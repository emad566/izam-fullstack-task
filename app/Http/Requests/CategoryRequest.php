<?php

namespace App\Http\Requests;

use App\Helpers\CustomFormRequest;
use Illuminate\Contracts\Validation\Validator;

class CategoryRequest extends CustomFormRequest
{


    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255|unique:categories,name,' . $this->id,
        ];
        return $rules;
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        if ($this->isMethod('put')) {
            $this->merge(['id' => $this->route(strtolower('Category'))]);
        }
    }

    protected function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            //
        });
    }
}