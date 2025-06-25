<?php

namespace App\Http\Requests;

use App\Helpers\CustomFormRequest;
use Illuminate\Contracts\Validation\Validator;

class CategoryRequest extends CustomFormRequest
{

    protected $roles = [];

    public function rules()
    {
        $this->roles = [
            'name' => 'required|string|max:255|unique:categories,name,' . $this->id,
        ];
        return $this->roles;
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