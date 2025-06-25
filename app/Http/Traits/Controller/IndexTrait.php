<?php


namespace App\Http\Traits\Controller;

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletes;
trait IndexTrait
{

    function indexInit(Request $request, $callBack=null, $validations = [], $deleted_at = true, $afterGet = null, $helpers = null, $with = null, $load = null, $q=true, $created_at = 'created_at')
    {
        try {
            $validator = Validator::make($request->all(), [
                ...config('constants.list_validations'),
                'sort_column' => 'nullable|in:id,' . implode(',', $this->columns),
                ...$validations,
            ]);

            $check = $this->checkValidator($validator);
            if ($check) return $check;


            if ($deleted_at && in_array(SoftDeletes::class, class_uses($this->model))) {
                $items = $this->model::withTrashed()->orderBy($request->sortColumn ?? $this->primaryKey, $request->sortDirection ?? 'DESC');
            }else{
                $items = $this->model::orderBy($request->sortColumn ?? $this->primaryKey, $request->sortDirection ?? 'DESC');
            }


            if ($request->date_from) {
                $items =  $items->where($created_at, '>=', Carbon::parse($request->date_from));
            }

            if ($request->date_to) {
                $items =  $items->where($created_at, '<=', Carbon::parse($request->date_to));
            }

            if($callBack){
                $response = $callBack($items);
                if($response[0] === false) return $response[1];
                $items = $response[0];
            }

            foreach ($this->columns as $column) {
                if ($request->$column) {
                    $where = (Str::contains($column, '_id') || $column == "id")? 'where' : 'likeStart';
                    $items = $items->$where($column, $request->$column);
                }
            }

            if($request->q && $q){
                $searchable = $this->columns;
                $items = $items->where(function ($q) use($request, $searchable) {
                    foreach ($searchable as $column) {
                        $q = $q->orLike($column, $request->q);
                    }
                    return $q;
                });
            }

            if($with){
                $items = $items->with($with);
            }

         

            $items = $items->paginate($request->per_page ?? config('constants.per_page'));
            if($load){
                $loads = $items->load($load);
                $items->data = $loads;
            }

            if($afterGet){
                $response = $afterGet($items);
                if($response[0] === false) return $response[1];
                $items = $response[0];
            }
            return $this->sendResponse(true, data: ['helpers' => $helpers, 'items' => $this->resource::collection($items)->response()->getData(true)], message: trans('Listed'), request: $request);
        } catch (\Throwable $th) {
            return $this->sendResponse(false, null, trans('msg.technicalError'), null, 500, $request);
        }
    }

}
