<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Http\Requests\CategoryRequest;
use Illuminate\Http\Request;
use App\Http\Traits\Controller\DestroyTrait;
use App\Http\Traits\Controller\IndexTrait;
use App\Http\Traits\Controller\EditTrait;
use App\Http\Traits\Controller\ShowTrait;
use App\Http\Traits\Controller\ToggleActiveTrait;
use App\Models\Category;

class CategoryController extends BaseController
{
    use IndexTrait, ShowTrait, EditTrait, DestroyTrait, ToggleActiveTrait;

    function __construct()
    {
        parent::__construct(Category::class);
    }

    function index(Request $request)
    {
        return $this->indexInit($request, function ($items) use($request){
            return [$items];
        }, [], isListTrashed(), function ($items) {
            return [$items];
        });
    }

    function show($id)
    {
        return $this->showInit($id, null, isListTrashed());
    }

    public function create(?Request $request = null)
    {
        try {
            return $this->sendResponse(true, [], trans('msg.data-to-create'), null);
        } catch (\Throwable $th) {
            return $this->sendServerError(trans('msg.technicalError'), $request->all(), $th);
        }
    }

    public function store(CategoryRequest $request)
    {
        try {
            $inputs = $request->validated();
            $item = $this->model::updateOrCreate(['name' => $inputs['name']], $inputs);
            return $this->sendResponse(true, [
                'item' => new $this->resource($item->refresh()),
            ], trans('Created'), null, 201, $request);
        } catch (\Throwable $th) {
            return $this->sendServerError(trans('msg.technicalError'), $request->all(), $th);
        }
    }

    function edit($id)
    {
        return $this->editInit($id, null, isListTrashed());
    }

    public function update(CategoryRequest $request, $id)
    {
        try {
            $inputs = $request->validated();
            $item = $this->model::find($id);
            $item->update($inputs);

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