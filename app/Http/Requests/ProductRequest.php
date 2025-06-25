<?php

namespace App\Http\Requests;

use App\Helpers\CustomFormRequest;
use Illuminate\Contracts\Validation\Validator;

class ProductRequest extends CustomFormRequest
{
    protected $roles =  [ ];

    public function rules()
    {
        $this->roles = [
            'name' => 'required|string|max:255|unique:products,name,' . $this->id,
            'description' => 'nullable|string',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
        ];
        return $this->roles;
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        if ($this->isMethod('put') || ($this->isMethod('post') && $this->_method == 'PUT')) {
            $this->merge(['id' => $this->route(strtolower('Product'))]);
        }
    }

    protected function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            //
        });
    }
}