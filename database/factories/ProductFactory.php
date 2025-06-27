<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clothingProducts = [
            // T-Shirts
            'Classic Cotton T-Shirt',
            'Vintage Graphic Tee',
            'Basic V-Neck T-Shirt',
            'Oversized Cotton Tee',
            'Striped Long Sleeve Tee',

            // Polo & Dress Shirts
            'Classic Polo Shirt',
            'Oxford Button-Down Shirt',
            'Slim Fit Dress Shirt',
            'Casual Linen Shirt',
            'Checkered Flannel Shirt',

            // Jeans & Pants
            'Slim Fit Dark Jeans',
            'Classic Straight Jeans',
            'High-Waisted Mom Jeans',
            'Ripped Skinny Jeans',
            'Stretch Chino Pants',
            'Cargo Utility Pants',

            // Dresses & Skirts
            'Floral Summer Dress',
            'Little Black Dress',
            'Midi Wrap Dress',
            'Pleated Mini Skirt',
            'A-Line Maxi Skirt',

            // Outerwear
            'Denim Jacket',
            'Leather Bomber Jacket',
            'Wool Peacoat',
            'Puffer Winter Jacket',
            'Lightweight Blazer',
            'Casual Hoodie',
            'Cable Knit Sweater',

            // Activewear
            'Yoga Leggings',
            'Athletic Tank Top',
            'Running Shorts',
            'Sports Bra',
            'Track Pants',

            // Accessories
            'Leather Belt',
            'Silk Scarf',
            'Cotton Socks',
            'Baseball Cap',
            'Knit Beanie'
        ];

        return [
            'name' => $this->faker->randomElement($clothingProducts) . ' ' . $this->faker->numberBetween(1, 1000),
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'stock' => $this->faker->numberBetween(0, 20),
            'category_id' => Category::factory(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function ($product) {
            $this->addClothingImage($product);
        });
    }

    /**
     * Add a realistic clothing image to the product
     */
    private function addClothingImage($product): void
    {
        try {
            // Use Lorem Picsum which we know is working
            $imageUrl = 'https://picsum.photos/800/600';

            if (function_exists('logger')) {
                logger()->info("Attempting to download image for product {$product->id} from: {$imageUrl}");
            }

            // Download the image with longer timeout and retry logic
            $imageResponse = Http::timeout(60)
                ->retry(3, 1000) // Retry 3 times with 1 second delay
                ->get($imageUrl);

            if (function_exists('logger')) {
                logger()->info("Image download response for product {$product->id}: " . $imageResponse->status());
            }

            if ($imageResponse->successful() && !empty($imageResponse->body())) {
                // Create a temporary file with proper extension
                $tempPath = sys_get_temp_dir() . '/' . uniqid('product_') . '.jpg';
                file_put_contents($tempPath, $imageResponse->body());

                if (function_exists('logger')) {
                    logger()->info("Image saved to temp path for product {$product->id}: {$tempPath}");
                }

                // Verify it's a valid image
                $imageInfo = getimagesize($tempPath);
                if ($imageInfo !== false) {
                    if (function_exists('logger')) {
                        logger()->info("Image validation successful for product {$product->id}: {$imageInfo[0]}x{$imageInfo[1]}");
                    }

                    // Add to media library with proper disk specification
                    $media = $product->addMedia($tempPath)
                        ->usingName($product->name)
                        ->usingFileName('product-' . $product->id . '-' . time() . '.jpg')
                        ->toMediaCollection('images', 'public'); // Explicitly use public disk

                    if (function_exists('logger')) {
                        logger()->info("Media added to product {$product->id}, media ID: {$media->id}");
                    }

                    // Wait a moment for file operations to complete
                    usleep(100000); // 0.1 seconds
                } else {
                    if (function_exists('logger')) {
                        logger()->warning("Image validation failed for product {$product->id}");
                    }
                }

                // Clean up temp file
                if (file_exists($tempPath)) {
                    unlink($tempPath);
                }
            } else {
                if (function_exists('logger')) {
                    logger()->warning("Image download failed for product {$product->id}: " . $imageResponse->status());
                }
            }
        } catch (\Exception $e) {
            // Log error if logging is configured
            if (function_exists('logger')) {
                logger()->error('Failed to add image to product ID ' . $product->id . ': ' . $e->getMessage());
            }
        }
    }

    /**
     * Get appropriate search terms based on product name
     */
    private function getSearchTermsFromProductName(string $productName): string
    {
        $name = strtolower($productName);

        // Map product types to search terms
        $searchMap = [
            't-shirt' => 'tshirt clothing fashion',
            'tee' => 'tshirt clothing',
            'polo' => 'polo shirt clothing',
            'shirt' => 'dress shirt clothing',
            'jeans' => 'jeans denim clothing',
            'pants' => 'pants trousers clothing',
            'chino' => 'chino pants clothing',
            'dress' => 'dress fashion clothing',
            'skirt' => 'skirt fashion clothing',
            'jacket' => 'jacket clothing fashion',
            'coat' => 'coat clothing fashion',
            'blazer' => 'blazer suit clothing',
            'hoodie' => 'hoodie sweatshirt clothing',
            'sweater' => 'sweater clothing fashion',
            'leggings' => 'leggings activewear clothing',
            'shorts' => 'shorts clothing fashion',
            'tank' => 'tank top clothing',
            'belt' => 'leather belt accessory',
            'scarf' => 'scarf fashion accessory',
            'cap' => 'baseball cap hat',
            'beanie' => 'beanie hat winter',
            'socks' => 'socks clothing',
        ];

        foreach ($searchMap as $keyword => $searchTerm) {
            if (str_contains($name, $keyword)) {
                return $searchTerm;
            }
        }

        return 'clothing fashion'; // Default fallback
    }

    /**
     * Get image URL from multiple sources with fallbacks
     */
    private function getUnsplashImage(string $searchTerms): ?string
    {
        try {
            // Try multiple image sources as fallbacks
            $imageSources = [
                // Lorem Picsum with random images (most reliable)
                'https://picsum.photos/800/600',

                // Placeholder.com with clothing theme
                'https://via.placeholder.com/800x600/cccccc/666666?text=' . urlencode('Clothing Item'),

                // Unsplash Source (if it comes back online)
                "https://source.unsplash.com/800x600/?{$searchTerms}",
            ];

            foreach ($imageSources as $imageUrl) {
                try {
                    // Test if the URL is accessible
                    $testResponse = Http::timeout(10)->head($imageUrl);
                    if ($testResponse->successful()) {
                        return $imageUrl;
                    }
                } catch (\Exception $e) {
                    // Continue to next source
                    continue;
                }
            }

            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * State to disable image generation for testing
     */
    public function withoutImages(): static
    {
        return $this->state(function (array $attributes) {
            return $attributes;
        })->afterCreating(function ($product) {
            // Override the image creation - do nothing
        });
    }
}
