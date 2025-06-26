<?php

namespace App\Http\Requests;

use App\Helpers\CustomFormRequest;
use Illuminate\Contracts\Validation\Validator;

class ProductRequest extends CustomFormRequest
{
    protected $roles = [];

    protected function getCustomRules(): array
    {
        // Image is always optional - user can create/update products without images
        $imageRule = 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120'; // 5MB max

        $this->roles = [
            'name' => 'required|string|max:255|min:2|no_xss|no_sql_injection|unique:products,name,' . $this->id,
            'description' => 'nullable|string|max:5000|no_xss|no_sql_injection',
            'image' => $imageRule,
            'price' => 'required|numeric|min:0|max:999999.99',
            'stock' => 'required|integer|min:0|max:1000000',
            'category_id' => 'required|integer|exists:categories,id',
        ];
        return $this->roles;
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        // Sanitize and validate numeric inputs
        if ($this->has('price')) {
            $price = $this->input('price');
            // Convert to float and limit precision
            $this->merge(['price' => round((float) $price, 2)]);
        }

        if ($this->has('stock')) {
            $stock = $this->input('stock');
            // Ensure it's a positive integer
            $this->merge(['stock' => max(0, (int) $stock)]);
        }

        if ($this->has('category_id')) {
            $categoryId = $this->input('category_id');
            // Ensure it's a positive integer
            $this->merge(['category_id' => (int) $categoryId]);
        }

        if ($this->isMethod('put') || ($this->isMethod('post') && $this->_method == 'PUT')) {
            $this->merge(['id' => $this->route(strtolower('Product'))]);
        }
    }

    protected function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            // Additional validation for product name to prevent duplicates with different cases
            if ($this->has('name')) {
                $name = trim($this->input('name'));
                if (strlen($name) < 2) {
                    $validator->errors()->add('name', 'Product name must be at least 2 characters long.');
                }

                // Check for potentially malicious patterns in product name
                if (preg_match('/[<>"\'\x00-\x1f\x7f]/', $name)) {
                    $validator->errors()->add('name', 'Product name contains invalid characters.');
                }
            }

            // Validate price format
            if ($this->has('price')) {
                $price = $this->input('price');
                if (!is_numeric($price) || $price < 0) {
                    $validator->errors()->add('price', 'Price must be a valid positive number.');
                }
            }

            // Validate stock is reasonable
            if ($this->has('stock')) {
                $stock = $this->input('stock');
                if (!is_int($stock) || $stock < 0) {
                    $validator->errors()->add('stock', 'Stock must be a valid non-negative integer.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required.',
            'name.string' => 'Product name must be a valid string.',
            'name.max' => 'Product name cannot exceed 255 characters.',
            'name.min' => 'Product name must be at least 2 characters long.',
            'name.unique' => 'A product with this name already exists.',
            'name.no_xss' => 'Product name contains potentially dangerous content.',
            'name.no_sql_injection' => 'Product name contains potentially dangerous patterns.',
            'description.string' => 'Description must be a valid string.',
            'description.max' => 'Description cannot exceed 5000 characters.',
            'description.no_xss' => 'Description contains potentially dangerous content.',
            'description.no_sql_injection' => 'Description contains potentially dangerous patterns.',
            'image.image' => 'The uploaded file must be an image.',
            'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif, webp.',
            'image.max' => 'The image size cannot exceed 5MB.',
            'price.required' => 'Price is required.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'price.max' => 'Price cannot exceed 999,999.99.',
            'stock.required' => 'Stock quantity is required.',
            'stock.integer' => 'Stock must be a valid integer.',
            'stock.min' => 'Stock cannot be negative.',
            'stock.max' => 'Stock cannot exceed 1,000,000.',
            'category_id.required' => 'Category is required.',
            'category_id.integer' => 'Category ID must be a valid integer.',
            'category_id.exists' => 'The selected category does not exist.',
        ];
    }
}
