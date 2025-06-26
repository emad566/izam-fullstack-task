<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use App\CacheNames;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'deleted_at'
    ];

    /**
     * Boot the model and register event listeners
     */
    protected static function boot()
    {
        parent::boot();

        // Clear product caches when category is created
        static::created(function ($category) {
            static::clearProductCaches();
        });

        // Clear product caches when category is updated
        static::updated(function ($category) {
            static::clearProductCaches();
        });

        // Clear product caches when category is deleted (soft delete)
        static::deleted(function ($category) {
            static::clearProductCaches();
        });

        // Clear product caches when category is restored
        static::restored(function ($category) {
            static::clearProductCaches();
        });

        // Clear product caches when category is force deleted
        static::forceDeleted(function ($category) {
            static::clearProductCaches();
        });
    }

    /**
     * Clear all product-related caches
     */
    public static function clearProductCaches(): void
    {
        // Get all cache keys from the cache store
        $cacheStore = Cache::getStore();

        if (method_exists($cacheStore, 'flush')) {
            // For file cache driver, we need to implement pattern-based clearing
            static::clearCacheByPattern([
                CacheNames::PRODUCTS_LIST->value,
                CacheNames::PRODUCT_DETAIL->value
            ]);
        } else {
            static::clearAllCaches();
        }
    }

    /**
     * Clear cache by pattern
     */
    private static function clearCacheByPattern(array $patterns): void
    {
        $cacheStore = Cache::getStore();

        if ($cacheStore instanceof \Illuminate\Cache\FileStore) {
            $cachePath = $cacheStore->getDirectory();
            $files = glob($cachePath . '/*');

            foreach ($files as $file) {
                if (is_file($file)) {
                    $filename = basename($file);

                    foreach ($patterns as $pattern) {
                        if (str_starts_with($filename, $pattern)) {
                            unlink($file);
                            break;
                        }
                    }
                }
            }
        } else {
            static::clearAllCaches();
        }
    }

    /**
     * Clear all caches
     */
    private static function clearAllCaches(): void
    {
        Cache::flush();
    }

    /**
     * Define relationship with products
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
