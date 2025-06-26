<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use App\Http\Traits\Controller\DestroyTrait;
use App\Http\Traits\Controller\IndexTrait;
use App\Http\Traits\Controller\EditTrait;
use App\Http\Traits\Controller\ShowTrait;
use App\Http\Traits\Controller\ToggleActiveTrait;
use App\Models\Product;
use App\CacheNames;
use Illuminate\Support\Facades\Cache;

class ProductController extends BaseController
{
    use IndexTrait, ShowTrait, EditTrait, DestroyTrait, ToggleActiveTrait;

    /**
        * Cache duration in minutes
     */

    function __construct()
    {
        parent::__construct(Product::class, ['price']);
    }

    function index(Request $request)
    {
        // Generate cache key based on request parameters
        $cacheKey = CacheNames::PRODUCTS_LIST->paginatedKey([
            'category_name' => $request->category_name,
            'category_names' => $request->category_names,
            'min_price' => $request->min_price,
            'max_price' => $request->max_price,
            'name' => $request->name,
            'q' => $request->q,
            'date_from' => $request->date_from,
            'date_to' => $request->date_to,
            'sortColumn' => $request->sortColumn,
            'sortDirection' => $request->sortDirection,
            'page' => $request->page,
            'per_page' => $request->per_page,
            'deleted_at' => isListTrashed()
        ]);

        return Cache::remember($cacheKey, config('constants.products_cache_duration') * 60, function () use ($request) {
            return $this->indexInit($request, function ($items) use($request){

                if($request->category_name || $request->category_names){
                    $items = $items->whereHas('category', function ($query) use ($request) {
                        // filter by like category name
                        if($request->category_name){
                            $query->like('name', $request->category_name);
                        }

                        // filter by in category names
                        if($request->category_names){
                            $query->whereIn('name', $request->category_names);
                        }
                    });
                }

                // Price range filtering
                if($request->min_price){
                    $items = $items->where('price', '>=', $request->min_price);
                }

                if($request->max_price){
                    $items = $items->where('price', '<=', $request->max_price);
                }

                return [$items];
            }, [], isListTrashed(), function ($items) {
                return [$items];
            }, null, null, ['category']);
        });
    }

    function show($id)
    {
        $cacheKey = CacheNames::PRODUCT_DETAIL->key(['id' => $id, 'deleted_at' => isListTrashed()]);

        return Cache::remember($cacheKey, config('constants.products_cache_duration') * 60, function () use ($id) {
            return $this->showInit($id, function ($item) {
                // Load the category relation for the resource
                $item->load('category');
                return [$item];
            }, isListTrashed());
        });
    }

    public function create(?Request $request = null)
    {
        try {
            return $this->sendResponse(true, [], trans('msg.data-to-create'), null);
        } catch (\Throwable $th) {
            return $this->sendServerError(trans('msg.technicalError'), $request->all(), $th);
        }
    }

    public function store(ProductRequest $request)
    {
        try {
            $inputs = $request->validated();

            // Remove image from inputs as we'll handle it separately
            unset($inputs['image']);

            $item = $this->model::create($inputs);

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                $media = $item->addMediaFromRequest('image')
                    ->toMediaCollection('images');

                // Force conversion processing
                $media->refresh();
            }

            // Clear product-related caches
            $this->clearProductCaches();

            return $this->sendResponse(true, [
                'item' => new $this->resource($item->refresh()),
            ], trans('Created'), null, 201, $request);
        } catch (\Throwable $th) {
            return $this->sendServerError(trans('msg.technicalError'), $request->all(), $th);
        }
    }

    function edit($id)
    {
        return $this->editInit($id, function ($item) {
            // Load the category relation for the resource
            $item->load('category');
            return [$item];
        }, isListTrashed());
    }

    public function update(ProductRequest $request, $id)
    {
        try {
            $inputs = $request->validated();

            // Remove image from inputs as we'll handle it separately
            unset($inputs['image']);

            $item = $this->model::find($id);
            $item->update($inputs);

            // Handle image upload if provided
            if ($request->hasFile('image')) {
                // Clear existing images and add new one
                $item->clearMediaCollection('images');
                $media = $item->addMediaFromRequest('image')
                    ->toMediaCollection('images');

                // Force conversion processing
                $media->refresh();
            }

            // Clear product-related caches
            $this->clearProductCaches();

            return $this->sendResponse(true, [
                'item' => new $this->resource($item->refresh()),
            ], trans('msg.created'), null, 200, $request);
        } catch (\Throwable $th) {
            return $this->sendServerError(trans('msg.technicalError'), $request->all(), $th);
        }
    }

    function destroy($id)
    {
        $result = $this->destroyInit($id, null, isListTrashed());

        // Clear product-related caches after successful deletion
        if ($result->getStatusCode() === 200) {
            $this->clearProductCaches();
        }

        return $result;
    }

    function toggleActive($id, $state)
    {
        $result = $this->toggleActiveInit($id, $state, null, isListTrashed());

        // Clear product-related caches after successful toggle
        if ($result->getStatusCode() === 200) {
            $this->clearProductCaches();
        }

        return $result;
    }

    /**
     * Clear all product-related caches
     */
    private function clearProductCaches(): void
    {
        // Get all cache keys from the cache store
        $cacheStore = Cache::getStore();

        if (method_exists($cacheStore, 'flush')) {
            // For file cache driver, we need to implement pattern-based clearing
            $this->clearCacheByPattern([
                CacheNames::PRODUCTS_LIST->value,
                CacheNames::PRODUCT_DETAIL->value
            ]);
        } else {
             $this->clearAllCaches();
        }
    }


    private function clearCacheByPattern(array $patterns): void
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
             $this->clearAllCaches();
        }
    }


    private function clearAllCaches(): void
    {
         Cache::flush();
    }
}
