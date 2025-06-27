<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class TestImageGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:image-generation {--count=1 : Number of products to create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test product image generation and validate storage location';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');

        $this->info("Creating {$count} product(s) with images...");
        $this->info("Testing image sources first...");

        // Test image sources
        $this->testImageSources();

        for ($i = 1; $i <= $count; $i++) {
            $this->info("Creating product {$i}/{$count}...");

            // Create product with images
            $product = Product::factory()->create();

            $this->info("Product created: {$product->name} (ID: {$product->id})");

            // Wait a moment for any async operations
            sleep(2);

            // Refresh the product to get latest media
            $product->refresh();

            // Check if media was added
            if ($product->hasMedia('images')) {
                $media = $product->getFirstMedia('images');
                $this->info("✅ Image attached successfully!");
                $this->info("   - Media ID: {$media->id}");
                $this->info("   - File name: {$media->file_name}");
                $this->info("   - Size: " . number_format($media->size / 1024, 2) . " KB");

                // Check file paths
                $originalPath = $media->getPath();
                $this->info("   - Original path: {$originalPath}");
                $this->info("   - File exists: " . (file_exists($originalPath) ? 'Yes' : 'No'));

                // Check if in public storage
                $publicPath = storage_path('app/public');
                if (str_contains($originalPath, $publicPath)) {
                    $this->info("   - ✅ Stored in public storage");
                } else {
                    $this->error("   - ❌ NOT in public storage!");
                }

                // Check conversions
                $this->checkConversion($media, 'thumb');
                $this->checkConversion($media, 'medium');
                $this->checkConversion($media, 'large');

                // Check URLs
                $imageUrls = $product->getImageUrls();
                if ($imageUrls) {
                    $this->info("   - URLs generated successfully:");
                    $this->info("     - Original: {$imageUrls['original']}");
                    $this->info("     - Thumb: {$imageUrls['thumb']}");
                    $this->info("     - Medium: {$imageUrls['medium']}");
                    $this->info("     - Large: {$imageUrls['large']}");
                } else {
                    $this->error("   - ❌ No URLs generated");
                }

            } else {
                $this->error("❌ No image was attached to product {$product->id}");
                $this->warn("This could be due to:");
                $this->warn("  - Network connectivity issues");
                $this->warn("  - Image service unavailability");
                $this->warn("  - Storage permission problems");
                $this->warn("  - Media library configuration issues");
            }

            $this->newLine();
        }

        $this->info("Test completed!");
        $this->info("Check storage/app/public for uploaded files");
        $this->info("Public URL should be accessible at: " . config('app.url') . "/storage/...");

        // Check storage permissions
        $this->checkStoragePermissions();
    }

    private function testImageSources()
    {
        $imageSources = [
            'Lorem Picsum' => 'https://picsum.photos/800/600',
            'Placeholder.com' => 'https://via.placeholder.com/800x600/cccccc/666666?text=Test',
            'Unsplash Source' => 'https://source.unsplash.com/800x600/?clothing',
        ];

        foreach ($imageSources as $name => $url) {
            try {
                $this->info("Testing {$name}...");
                $response = Http::timeout(10)->get($url);
                if ($response->successful() && !empty($response->body())) {
                    $this->info("  ✅ {$name} is accessible");
                } else {
                    $this->warn("  ⚠️  {$name} returned status: " . $response->status());
                }
            } catch (\Exception $e) {
                $this->error("  ❌ {$name} failed: " . $e->getMessage());
            }
        }
        $this->newLine();
    }

    private function checkStoragePermissions()
    {
        $this->info("Checking storage permissions...");

        $storagePath = storage_path('app/public');

        if (!is_dir($storagePath)) {
            $this->error("❌ Storage directory does not exist: {$storagePath}");
            return;
        }

        if (!is_writable($storagePath)) {
            $this->error("❌ Storage directory is not writable: {$storagePath}");
            $this->info("Try running: chmod -R 775 storage/");
            return;
        }

        $this->info("✅ Storage directory exists and is writable");

        // Check if storage link exists
        $linkPath = public_path('storage');
        if (is_link($linkPath)) {
            $this->info("✅ Storage link exists");
        } else {
            $this->warn("⚠️  Storage link missing - run: php artisan storage:link");
        }
    }

    private function checkConversion($media, $conversionName)
    {
        $conversionPath = $media->getPath($conversionName);
        $exists = file_exists($conversionPath);

        if ($exists) {
            $imageInfo = getimagesize($conversionPath);
            $this->info("   - ✅ {$conversionName}: {$imageInfo[0]}x{$imageInfo[1]}");
        } else {
            $this->warn("   - ⚠️  {$conversionName}: Not generated yet");
        }
    }
}
