<?php

namespace App\Http\Requests;

use App\Helpers\CustomFormRequest;
use Illuminate\Contracts\Validation\Validator;

class ProductRequest extends CustomFormRequest
{
    protected $roles =  [ ];

    public function rules()
    {
        $imageRule = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120'; // 5MB max

        // For store operation, image is required
        if ($this->isMethod('post') && !$this->_method) {
            $imageRule = 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120';
        }

        $this->roles = [
            'name' => 'required|string|max:255|unique:products,name,' . $this->id,
            'description' => 'nullable|string',
            'image' => $imageRule,
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