<?php

namespace App\Helpers;

use App\Enums\OfferType;
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

        $response = response()->json([
           'status' => false,
           'message' => $message,
           'data' => [],
           'errors' => $errorsArr,
           'input' => $this->all(),
        ], 422);

        throw new ValidationException($validator, $response);
    }

}
