<?php


namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\FailedValidation;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */

     protected string $table = '';
     protected string $primaryKey = '';
     protected ?string $model = null;
     protected string $resource;
     protected ?string $modelRequest = null;
     protected $data = [];

     protected array $excludedColumns = [];

     protected array $columns = [];
     protected array $heading = [];

     // Allowing Default Argument
     public function __construct(?string $model = null, array $excludedColumns = [])
     {
         if ($model) {
             $this->model = $model;
             $this->excludedColumns = $excludedColumns;
             $modelInstance = new $this->model();
             $this->columns = $modelInstance->getFillable();

              if (!empty($this->excludedColumns)) {
                 $this->columns = array_filter($this->columns, function($column) {
                     return !in_array($column, $this->excludedColumns);
                 });
                 $this->columns = array_values($this->columns);
             }

             if($this->columns['date_from']?? false) unset($this->columns['date_from']);
             if($this->columns['date_to']?? false) unset($this->columns['date_to']);
             $this->primaryKey = $modelInstance->getKeyName();
             $this->table = $modelInstance->getTable();
             $modelResource = class_basename($modelInstance) . 'Resource';
             $modelRequest = class_basename($modelInstance) . 'Request';
             $this->resource = "App\Http\Resources\\$modelResource";
             $this->modelRequest = "App\Http\Requests\\$modelRequest";
         }
     }

    public function sendResponse($status = true, $data = null, $message = '', $errors = null, $code = 200, $request = null)
    {
        // Filter out file uploads from request data to prevent serialization errors
        $requestData = null;
        if ($request) {
            $requestData = $request->all();
            foreach ($requestData as $key => $value) {
                if ($request->hasFile($key)) {
                    $requestData[$key] = '[FILE]';
                }
            }
        }

        $response = [
            'status' =>  $status,
            'message' => $message,
            'data'    => $data,
            'errors' => $status === true ? $errors : (count($errors ?? [], COUNT_RECURSIVE) > 1 ? $errors : ['message' => [$message]]),

            // TODO:: comment this lines
            'response_code' => $code,
            'request_data' => $requestData,
        ];
        return response()->json($response, $code);
    }

    public function sendServerError($msg = '', $data = null, $th = false)
    {
        $thStr = $th ? $th->getMessage() : '';
        return $this->sendResponse(false, $data, 'Server Technical Error: ' . $msg . " $thStr", null, 500);
    }

    public function checkValidator($validator)
    {
        $failedValidation = new FailedValidation($validator);
        if ($failedValidation->status) {
            return $failedValidation->response;
        } else return false;
    }
}
