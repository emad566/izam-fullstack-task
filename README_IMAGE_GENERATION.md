# Product Image Generation Testing

This document explains how to test and validate the automatic image generation for products.

## Overview

The ProductFactory automatically generates realistic clothing images for products by:
1. Analyzing the product name to determine clothing type
2. Fetching relevant images from Unsplash Source API
3. Downloading and storing images in Laravel's public storage
4. Generating multiple size conversions (thumb, medium, large)

## Testing Image Generation

### Method 1: Using the Test Command

Run the custom test command to create products and validate image generation:

```bash
# Create 1 product with image
php artisan test:image-generation

# Create multiple products
php artisan test:image-generation --count=3
```

This command will:
- Create products with images
- Show detailed information about each generated image
- Validate that files are stored in the correct location
- Check that all image conversions are generated
- Display accessible URLs

### Method 2: Using PHPUnit Tests

Run the comprehensive test suite:

```bash
# Run specific image generation tests (requires network access)
php artisan test --filter=test_product_factory_generates_images_automatically

# Run without images test
php artisan test --filter=test_product_factory_without_images_works

# Run image conversion tests  
php artisan test --filter=test_image_conversions_are_generated_correctly
```

**Note:** Some tests are marked as skipped by default because they require network access. To enable them, comment out the `markTestSkipped()` line in the test methods.

### Method 3: Manual Factory Usage

```php
// In tinker or your code
use App\Models\Product;

// Create products with images (default behavior)
$product = Product::factory()->create();

// Create products without images (for testing)
$product = Product::factory()->withoutImages()->create();

// Create multiple products
Product::factory()->count(5)->create();
```

## File Storage Locations

Images are stored in the following structure:

```
storage/app/public/
├── 1/              # Product ID folder
│   ├── product-1-timestamp.jpg     # Original image
│   ├── conversions/
│   │   ├── product-1-timestamp-thumb.jpg   # 150x150
│   │   ├── product-1-timestamp-medium.jpg  # 400x400
│   │   └── product-1-timestamp-large.jpg   # 800x600
├── 2/
│   └── ...
```

## Accessible URLs

After running `php artisan storage:link`, images are accessible via:

- Original: `http://yourapp.test/storage/1/product-1-timestamp.jpg`
- Thumb: `http://yourapp.test/storage/1/conversions/product-1-timestamp-thumb.jpg`
- Medium: `http://yourapp.test/storage/1/conversions/product-1-timestamp-medium.jpg`  
- Large: `http://yourapp.test/storage/1/conversions/product-1-timestamp-large.jpg`

## Troubleshooting

### Images Not Generated

1. **Check network connectivity:**
   ```bash
   curl -I https://source.unsplash.com/800x600/?clothing
   ```

2. **Check storage permissions:**
   ```bash
   chmod -R 775 storage/app/public
   ```

3. **Ensure storage link exists:**
   ```bash
   php artisan storage:link
   ```

### Conversions Not Generated

1. **Check if GD/Imagick is installed:**
   ```bash
   php -m | grep -E "(gd|imagick)"
   ```

2. **Force regenerate conversions:**
   ```bash
   php artisan media-library:regenerate
   ```

3. **Check queue processing (if using queues):**
   ```bash
   php artisan queue:work
   ```

### Disk Configuration Issues

Check `config/media-library.php`:
```php
'disk_name' => env('MEDIA_DISK', 'public'),
```

Check `config/filesystems.php`:
```php
'public' => [
    'driver' => 'local',
    'root' => storage_path('app/public'),
    'url' => env('APP_URL').'/storage',
    'visibility' => 'public',
],
```

## Image Search Terms Mapping

The factory maps product names to search terms:

- **T-Shirt** → "tshirt clothing fashion"
- **Jeans** → "jeans denim clothing"  
- **Dress** → "dress fashion clothing"
- **Jacket** → "jacket clothing fashion"
- **Default** → "clothing fashion"

## Production Considerations

1. **Network Dependency:** Image generation requires internet access
2. **Performance:** Each product creation makes an HTTP request
3. **Rate Limits:** Unsplash Source has usage limits
4. **Storage Space:** High-resolution images require significant storage

For production, consider:
- Pre-generating a library of clothing images
- Using a local image service
- Implementing image caching strategies
- Using queue jobs for image processing

## Testing in CI/CD

For continuous integration, use the `withoutImages()` factory method:

```php
// In tests
Product::factory()->withoutImages()->create();
```

This ensures tests run without network dependencies. 
