<?php


namespace App\Services;


use Illuminate\Contracts\Validation\Validator;

class FailedValidation
{

    public $validator = null;
    public $response = null;
    public $status = true;
    function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    protected function failedValidation()
    {
        $validator = $this->validator;
        $errors = $validator->errors();
        $errorsArr = json_decode(json_encode($errors), true);
        $this->status = $validator->fails()? true : false;
        $message = '';
        $errorsArrValues = array_values($errorsArr);

        if (!empty($errorsArrValues) && isset($errorsArrValues[0][0])) {
            $message = $errorsArrValues[0][0];
            if (count($errorsArrValues ?? []) > 1) {
                $message .= trans('and') . (count($errorsArrValues ?? []) - 1) . trans('moreValidation');
            }
        }
        $code = 422;
        $response = response()->json([
           'status' => false,
           'message' => $message,
           'data' => null,
           'guard' => auth()->user()->role,
           'errors' => $errorsArr,
           'response_code' => $code,
           'request_data' => $this->all(),
        ], $code);

        $this->response = $response;
    }
}
