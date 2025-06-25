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

class ProductController extends BaseController
{
    use IndexTrait, ShowTrait, EditTrait, DestroyTrait, ToggleActiveTrait;

    function __construct()
    {
        parent::__construct(Product::class);
    }

    function index(Request $request)
    {
        return $this->indexInit($request, function ($items) use($request){
            // Add image URLs to each product

            return [$items];
        }, [], isListTrashed(), function ($items) {
            return [$items];
        }, null, null, ['category']);
    }

    function show($id)
    {
        return $this->showInit($id, function ($item) {
            // Add image URLs to the product
             return [$item];
        }, isListTrashed());
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

            return $this->sendResponse(true, [
                'item' => new $this->resource($item->refresh()),
            ], trans('msg.created'), null, 200, $request);
        } catch (\Throwable $th) {
            return $this->sendServerError(trans('msg.technicalError'), $request->all(), $th);
        }
    }

    function destroy($id)
    {
        return $this->destroyInit($id, null, isListTrashed());
    }

    function toggleActive($id, $state)
    {
        return $this->toggleActiveInit($id, $state, null, isListTrashed());
    }
}
