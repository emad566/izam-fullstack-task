<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class RegenerateMediaConversions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:regenerate-conversions {--model=Product}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate media conversions for all products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting media conversion regeneration...');

        $products = Product::has('media')->get();

        if ($products->isEmpty()) {
            $this->warn('No products with media found.');
            return;
        }

        $this->info("Found {$products->count()} products with media.");

        foreach ($products as $product) {
            foreach ($product->getMedia('images') as $media) {
                try {
                    $this->info("Processing media ID: {$media->id} for product: {$product->name}");

                    // Check if original file exists
                    if (!file_exists($media->getPath())) {
                        $this->error("Original file not found for media ID: {$media->id}");
                        continue;
                    }

                    // Check if conversions exist
                    $conversions = ['thumb', 'medium', 'large'];
                    foreach ($conversions as $conversion) {
                        $conversionPath = $media->getPath($conversion);
                        if (file_exists($conversionPath)) {
                            $this->info("Conversion '$conversion' already exists: " . basename($conversionPath));
                        } else {
                            $this->warn("Conversion '$conversion' missing for media ID: {$media->id}");
                        }
                    }

                } catch (\Exception $e) {
                    $this->error("Error processing media ID: {$media->id}. Error: " . $e->getMessage());
                }
            }
        }

        $this->newLine();
        $this->info('Media conversion check completed!');
        $this->info('Try uploading a new image to test if conversions are now working.');
    }
}
