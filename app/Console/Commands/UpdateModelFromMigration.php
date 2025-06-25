<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class UpdateModelFromMigration extends Command
{
    protected $signature = 'model:ufm {name}';
    protected $description = 'Update model fillable and resource from migration table (ufm)';

    public function handle()
    {
        try {
            $name = $this->argument('name');

            // Convert name to various formats
            $modelName = Str::studly(Str::singular($name));
            $tableName = Str::plural(Str::snake($name));

            // Find migration file
            $migrationFile = $this->findMigrationFile($tableName);
            if (!$migrationFile) {
                $this->error("Migration file for table {$tableName} not found!");
                return 1;
            }

            // Get columns from migration
            $columns = $this->getColumnsFromMigration($migrationFile);
            if (empty($columns)) {
                $this->error("No columns found in migration file!");
                return 1;
            }

            // Update Model
            $this->updateModel($modelName, $columns);

            // Update Resource
            $this->updateResource($modelName, $columns);

            $this->info('Model and Resource updated successfully!');
        } catch (\Throwable $th) {
            $this->error("Failed to update model and resource: " . $th->getMessage());
            return 1;
        }
    }

    protected function findMigrationFile($tableName)
    {
        $files = File::glob(database_path('migrations/*_create_'.$tableName.'_table.php'));
        return !empty($files) ? $files[0] : null;
    }

    protected function getColumnsFromMigration($migrationFile)
    {
        $content = File::get($migrationFile);
        preg_match('/Schema::create\([^,]+,\s*function\s*\(Blueprint\s+\$table\)\s*{([^}]+)}\);/s', $content, $matches);

        if (empty($matches[1])) {
            return [];
        }

        $columns = [];
        $lines = explode("\n", $matches[1]);

        foreach ($lines as $line) {
            // Skip timestamps, id, and softDeletes
            if (strpos($line, '->timestamps()') !== false ||
                strpos($line, '->id()') !== false ||
                strpos($line, '->softDeletes()') !== false) {
                continue;
            }

            // Extract column definitions
            if (preg_match('/\$table->([^(]+)\(\'([^\']+)\'/', $line, $columnMatch)) {
                $columns[] = $columnMatch[2];
            }
        }

        return $columns;
    }

    protected function updateModel($name, $columns)
    {
        $modelPath = app_path("Models/{$name}.php");

        if (!File::exists($modelPath)) {
            $this->error("Model {$name}.php does not exist!");
            return;
        }

        // Add default fields
        $defaultFields = ['id', 'created_at', 'updated_at', 'deleted_at'];
        $columns = array_merge($defaultFields, $columns);

        $modelContent = File::get($modelPath);

        // Create new fillable array
        $fillable = implode("',\n        '", $columns);
        $fillableContent = "    protected \$fillable = [\n        '{$fillable}'\n    ];";

        // Replace existing fillable
        $pattern = '/protected\s+\$fillable\s+=\s+\[(.*?)\];/s';
        $modelContent = preg_replace($pattern, $fillableContent, $modelContent);

        File::put($modelPath, $modelContent);
        $this->info("Updated fillable in {$name} model");
    }

    protected function updateResource($name, $columns)
    {
        $resourcePath = app_path("Http/Resources/{$name}Resource.php");

        if (!File::exists($resourcePath)) {
            $this->error("Resource {$name}Resource.php does not exist!");
            return;
        }

        // Build resource array content
        $resourceArray = [];
        foreach ($columns as $column) {
            $resourceArray[] = "            '{$column}' => \$this->{$column}";
        }

        // Add default fields
        array_unshift($resourceArray, "            'id' => \$this->id");
        array_push($resourceArray,
            "            'created_at' => \$this->created_at",
            "            'updated_at' => \$this->updated_at",
            "            'deleted_at' => \$this->deleted_at"
        );

        $resourceArrayContent = implode(",\n", $resourceArray);

        // Create new resource content
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
{$resourceArrayContent}
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

        File::put($resourcePath, $resourceContent);
        $this->info("Updated {$name}Resource");
    }
}
