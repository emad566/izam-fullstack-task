<?php

namespace App\Helpers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;

class CustomFormRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }


    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        $errorsArr = json_decode(json_encode($errors), true);

        $message = '';
        $errorsArrValues = array_values($errorsArr);

        if (!empty($errorsArrValues) && isset($errorsArrValues[0][0])) {
            $message = $errorsArrValues[0][0];
            if (count($errorsArrValues ?? []) > 1) {
                $message .= trans('and') . (count($errorsArrValues ?? []) - 1) . trans('moreValidation');
            }
        }

        // Filter out file uploads to prevent serialization errors
        $input = $this->all();
        foreach ($input as $key => $value) {
            if ($this->hasFile($key)) {
                $input[$key] = '[FILE]';
            }
        }

        $response = response()->json([
           'status' => false,
           'message' => $message,
           'data' => [],
           'errors' => $errorsArr,
           'input' => $input,
        ], 422);

        throw new ValidationException($validator, $response);
    }

}
