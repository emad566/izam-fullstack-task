<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $table = 'products';
    protected $fillable = [
        'name',
        'description',
        'price',
        'stock',
        'category_id',
        'deleted_at'
    ];

        // Define media conversions for different image sizes
    public function registerMediaConversions(?Media $media = null): void
    {
        // Temporarily disabled conversions to resolve null path issues in tests
        // Can be re-enabled once the Spatie Image library issue is resolved
        // /*
        $this->addMediaConversion('thumb')
            ->width(150)
            ->height(150)
            ->sharpen(10)
            ->nonQueued()
            ->performOnCollections('images');

        $this->addMediaConversion('medium')
            ->width(400)
            ->height(400)
            ->sharpen(10)
            ->nonQueued()
            ->performOnCollections('images');

        $this->addMediaConversion('large')
            ->width(800)
            ->height(600)
            ->sharpen(10)
            ->nonQueued()
            ->performOnCollections('images');
        // */
    }

    // Define media collections
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_products')
                    ->withPivot('quantity', 'unit_price', 'total_price')
                    ->withTimestamps();
    }

    public function orderProducts()
    {
        return $this->hasMany(OrderProduct::class);
    }

    // Helper method to get image URLs
    public function getImageUrls()
    {
        $media = $this->getFirstMedia('images');
        if (!$media) {
            return null;
        }

        $urls = [
            'original' => $media->getUrl(),
        ];

        // Add conversion URLs only if they exist
        try {
            $urls['thumb'] = $media->getUrl('thumb');
        } catch (\Exception $e) {
            // Conversion doesn't exist, skip it
        }

        try {
            $urls['medium'] = $media->getUrl('medium');
        } catch (\Exception $e) {
            // Conversion doesn't exist, skip it
        }

        try {
            $urls['large'] = $media->getUrl('large');
        } catch (\Exception $e) {
            // Conversion doesn't exist, skip it
        }

        return $urls;
    }
}
