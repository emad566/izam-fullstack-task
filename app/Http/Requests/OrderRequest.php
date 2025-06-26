<?php

namespace App\Http\Requests;

use App\Helpers\CustomFormRequest;
use App\Models\Product;
use App\OrderStatus;
use Illuminate\Contracts\Validation\Validator;

class OrderRequest extends CustomFormRequest
{
    protected $roles = [
        'products' => 'required|array|min:1',
        'products.*.product_id' => 'required|exists:products,id',
        'products.*.quantity' => 'required|integer|min:1',
        'notes' => 'nullable|string|max:1000',
    ];

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = $this->roles;

        // Different rules for update vs create
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // For updates, products are not required (only status/notes updates)
            $rules['products'] = 'sometimes|array|min:1';
            $rules['products.*.product_id'] = 'required_with:products|exists:products,id';
            $rules['products.*.quantity'] = 'required_with:products|integer|min:1';

            // Allow status updates (only admins can access update routes now)
            $rules['status'] = 'sometimes|in:' . implode(',', OrderStatus::values());
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();
    }

    protected function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('products') && is_array($this->input('products'))) {
                foreach ($this->input('products', []) as $index => $productData) {
                    if (isset($productData['product_id']) && isset($productData['quantity'])) {
                        $product = Product::find($productData['product_id']);

                        if ($product && $product->stock < $productData['quantity']) {
                            $validator->errors()->add(
                                "products.{$index}.quantity",
                                "Insufficient stock for {$product->name}. Available: {$product->stock}, Requested: {$productData['quantity']}"
                            );
                        }
                    }
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        $statusValues = implode(', ', OrderStatus::values());

        return [
            'products.required' => 'At least one product is required for the order.',
            'products.array' => 'Products must be provided as an array.',
            'products.min' => 'At least one product must be included in the order.',
            'products.*.product_id.required' => 'Product ID is required for each product.',
            'products.*.product_id.exists' => 'The selected product does not exist.',
            'products.*.quantity.required' => 'Quantity is required for each product.',
            'products.*.quantity.integer' => 'Quantity must be a valid integer.',
            'products.*.quantity.min' => 'Quantity must be at least 1.',
            'status.in' => "Invalid status value. Allowed values are: {$statusValues}.",
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }
}
