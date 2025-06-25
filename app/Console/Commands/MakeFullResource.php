<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;

class MakeFullResource extends Command
{
    protected $signature = 'make:fr {name} {--fields=} {--api} {--version-folder=} {--route-file=api}';
    protected $description = 'Create a full resource including migration, model, controller, API resource, and routes';

    public function handle()
    {
        try {
            $name = $this->argument('name');
            $fields = $this->option('fields');
            $isApi = $this->option('api');
            $versionFolder = $this->option('version-folder');
            $routeFile = $this->option('route-file');

            // Convert name to various formats
            $modelName = Str::studly(Str::singular($name));
            $tableName = Str::plural(Str::snake($name));
            $migrationName = "create_{$tableName}_table";
            $routeName = Str::plural(Str::kebab($name));

            // Ensure directories exist
            $this->ensureDirectoriesExist($versionFolder);

            $this->info('Creating full resource for: ' . $modelName);

            // 1. Create Migration
            $this->createMigration($migrationName, $tableName, $fields);

            // 2. Create Model
            $this->createModel($modelName, $fields);

            // 3. Create Controller
            $this->createController($modelName, $versionFolder);

            // 4. Create Resource
            $this->createResource($modelName);

            // 5. Create Request
            $this->createRequest($modelName);

            // 6. Add Routes
            $this->addRoutes($modelName, $routeName, $routeFile);

            $this->info('Full resource created successfully!');
        } catch (\Throwable $th) {
            $this->error("Failed to create resource: " . $th->getMessage());
            return 1;
        }
    }

    protected function ensureDirectoriesExist($versionFolder)
    {
        $directories = [
            app_path("Http/Controllers/{$versionFolder}"),
            app_path('Http/Resources'),
            app_path('Http/Requests'),
            app_path('Models'),
            database_path('migrations'),
            app_path('Http/Traits/Controller'),
        ];

        foreach ($directories as $directory) {
            if (!File::exists($directory)) {
                $this->info("Creating directory: {$directory}");
                File::makeDirectory($directory, 0755, true);
            }
        }
    }

    protected function createFile($path, $content, $type)
    {
        try {
            if (File::exists($path)) {
                $this->warn("{$type} already exists: {$path}");
                return;
            }
            File::put($path, $content);
            $this->info("Created {$type}: {$path}");
        } catch (\Throwable $th) {
            $this->error("Failed to create {$type}: " . $th->getMessage());
            throw $th;
        }
    }

    protected function createMigration($name, $tableName, $fields)
    {
        $this->info('Creating migration...');

        $fieldsArray = $fields ? explode(',', $fields) : [];
        $migrationFields = '';

        foreach ($fieldsArray as $field) {
            $parts = explode(':', $field);
            $fieldName = $parts[0];
            $fieldType = $parts[1] ?? 'string';
            $migrationFields .= "\n            \$table->{$fieldType}('{$fieldName}');";
        }

        $migrationContent = <<<EOT
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$tableName}', function (Blueprint \$table) {
            \$table->id();{$migrationFields}
            \$table->timestamps();
            \$table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$tableName}');
    }
};
EOT;

        $filename = date('Y_m_d_His') . "_{$name}.php";
        $this->createFile(
            database_path("migrations/{$filename}"),
            $migrationContent,
            'Migration'
        );
    }

    protected function createModel($name, $fields)
    {
        $this->info('Creating model...');

        $fieldsArray = $fields ? explode(',', $fields) : [];

        // Add default fields
        $defaultFields = ['id', 'created_at', 'updated_at', 'deleted_at'];
        $fieldsArray = array_merge($defaultFields, $fieldsArray);

        $fillable = implode("',\n        '", $fieldsArray);
        $fillableContent = "    protected \$fillable = [\n        '{$fillable}'\n    ];";

        $modelContent = <<<EOT
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class {$name} extends Model
{
    use HasFactory, SoftDeletes;

{$fillableContent}
}
EOT;

        $this->createFile(
            app_path("Models/{$name}.php"),
            $modelContent,
            'Model'
        );
    }

    protected function createController($name, $versionFolder)
    {
        $this->info('Creating controller...');

        $namespace = "App\\Http\\Controllers\\{$versionFolder}";
        $controllerName = "{$name}Controller";

        $controllerContent = <<<EOT
<?php

namespace {$namespace};

use App\\Http\\Controllers\\{$versionFolder}\\BaseController;
use App\\Http\\Requests\\{$name}Request;
use Illuminate\\Http\\Request;
use App\\Http\\Traits\\Controller\\DestroyTrait;
use App\\Http\\Traits\\Controller\\IndexTrait;
use App\\Http\\Traits\\Controller\\EditTrait;
use App\\Http\\Traits\\Controller\\ShowTrait;
use App\\Http\\Traits\\Controller\\ToggleActiveTrait;
use App\\Models\\{$name};

class {$controllerName} extends BaseController
{
    use IndexTrait, ShowTrait, EditTrait, DestroyTrait, ToggleActiveTrait;

    function __construct()
    {
        parent::__construct({$name}::class);
    }

    function index(Request \$request)
    {
        return \$this->indexInit(\$request, function (\$items) use(\$request){
            return [\$items];
        }, [], isListTrashed(), function (\$items) {
            return [\$items];
        });
    }

    function show(\$id)
    {
        return \$this->showInit(\$id, null, isListTrashed());
    }

    public function create(?Request \$request = null)
    {
        try {
            return \$this->sendResponse(true, [], trans('msg.data-to-create'), null);
        } catch (\\Throwable \$th) {
            return \$this->sendServerError(trans('msg.technicalError'), \$request->all(), \$th);
        }
    }

    public function store({$name}Request \$request)
    {
        try {
            \$inputs = \$request->validated();
            \$item = \$this->model::updateOrCreate(['id' => \$inputs['id']], \$inputs);
            return \$this->sendResponse(true, [
                'item' => new \$this->resource(\$item->refresh()),
            ], trans('Created'), null, 201, \$request);
        } catch (\\Throwable \$th) {
            return \$this->sendServerError(trans('msg.technicalError'), \$request->all(), \$th);
        }
    }

    function edit(\$id)
    {
        return \$this->editInit(\$id, null, isListTrashed());
    }

    public function update({$name}Request \$request, \$id)
    {
        try {
            \$inputs = \$request->validated();
            \$item = \$this->model::find(\$id);
            \$item->updateOrCreate(['id' => \$inputs['id']], \$inputs);
            return \$this->sendResponse(true, [
                'item' => new \$this->resource(\$item->refresh()),
            ], trans('msg.created'), null, 200, \$request);
        } catch (\\Throwable \$th) {
            return \$this->sendServerError(trans('msg.technicalError'), \$request->all(), \$th);
        }
    }

    function destroy(\$id)
    {
        return \$this->destroyInit(\$id, null, isListTrashed());
    }

    function toggleActive(\$id, \$state)
    {
        return \$this->toggleActiveInit(\$id, \$state, null, isListTrashed());
    }
}
EOT;

        $directory = app_path("Http/Controllers/{$versionFolder}");
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $this->createFile(
            "{$directory}/{$controllerName}.php",
            $controllerContent,
            'Controller'
        );
    }

    protected function createResource($name)
    {
        $this->info('Creating resource...');

        $resourceContent = <<<EOT
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {$name}Resource extends JsonResource
{
    public function toArray(Request \$request): array
    {
        \$data = [
            'id' => \$this->id,
            //
            'created_at' => \$this->created_at,
            'updated_at' => \$this->updated_at,
            'deleted_at' => \$this->deleted_at,
        ];
        \$data = toString(\$data);
        return \$data;
    }

    public function heading(): array
    {
        return array_keys(\$this->toArray(request()));
    }
}
EOT;

        $directory = app_path('Http/Resources');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $this->createFile(
            "{$directory}/{$name}Resource.php",
            $resourceContent,
            'Resource'
        );
    }

    protected function createRequest($name)
    {
        $this->info('Creating form request...');

        $requestContent = <<<EOT
<?php

namespace App\Http\Requests;

use App\Helpers\CustomFormRequest;
use Illuminate\Contracts\Validation\Validator;

class {$name}Request extends CustomFormRequest
{
    protected \$roles =  [
        //
    ];

    public function rules()
    {
        return \$this->roles;
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        if (\$this->isMethod('put')) {
            \$this->merge(['id' => \$this->route(strtolower('{$name}'))]);
        }
    }

    protected function withValidator(Validator \$validator)
    {
        \$validator->after(function (\$validator) {
            //
        });
    }
}
EOT;

        $directory = app_path('Http/Requests');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $this->createFile(
            "{$directory}/{$name}Request.php",
            $requestContent,
            'Request'
        );
    }

    protected function addRoutes($name, $routeName, $routeFile)
    {
        $this->info('Adding routes...');
        $routePath = base_path("routes/{$routeFile}.php");

        $routeContent = "\n// Start::{$name} ===================================================== //\n";
        $routeContent .= "Route::resource('{$routeName}', {$name}Controller::class)->names('{$routeName}');\n";
        $routeContent .= "Route::put('{$routeName}/{" . Str::camel($name) . "}/toggleActive/{state}', [{$name}Controller::class, 'toggleActive'])\n";
        $routeContent .= "    ->where(['id' => '[0-9]+', 'state' => 'true|false'])->name('{$routeName}.toggleActive');\n";
        $routeContent .= "// End::{$name} ===================================================== //\n";

        if (!File::exists($routePath)) {
            $this->warn("Route file {$routeFile}.php does not exist. Creating it...");
            File::put($routePath, "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n");
        }

        File::append($routePath, $routeContent);
    }
}
