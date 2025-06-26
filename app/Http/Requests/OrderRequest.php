<?php

namespace App\Http\Requests;

use App\Helpers\CustomFormRequest;
use App\Models\Product;
use App\OrderStatus;
use Illuminate\Contracts\Validation\Validator;

class OrderRequest extends CustomFormRequest
{
    protected $roles = [
        'products' => 'required|array|min:1|max:50', // Limit to 50 products max
        'products.*.product_id' => 'required|integer|exists:products,id',
        'products.*.quantity' => 'required|integer|min:1|max:10000', // Reasonable quantity limits
        'notes' => 'nullable|string|max:1000|no_xss|no_sql_injection',
    ];

    /**
     * Get custom validation rules that apply to the request.
     */
    protected function getCustomRules(): array
    {
        $rules = $this->roles;

        // Different rules for update vs create
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // For updates, products are not required (only status/notes updates)
            $rules['products'] = 'sometimes|array|min:1|max:50';
            $rules['products.*.product_id'] = 'required_with:products|integer|exists:products,id';
            $rules['products.*.quantity'] = 'required_with:products|integer|min:1|max:10000';

            // Allow status updates (only admins can access update routes now)
            $rules['status'] = 'sometimes|in:' . implode(',', OrderStatus::values());
        }

        return $rules;
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        // Additional sanitization for order-specific fields
        if ($this->has('products') && is_array($this->input('products'))) {
            $sanitizedProducts = [];
            foreach ($this->input('products', []) as $product) {
                if (is_array($product)) {
                    $sanitizedProducts[] = [
                        'product_id' => isset($product['product_id']) ? (int) $product['product_id'] : null,
                        'quantity' => isset($product['quantity']) ? (int) $product['quantity'] : null,
                    ];
                }
            }

            // Limit the number of products to prevent abuse
            $sanitizedProducts = array_slice($sanitizedProducts, 0, 50);
            $this->merge(['products' => $sanitizedProducts]);
        }
    }

    protected function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('products') && is_array($this->input('products'))) {
                foreach ($this->input('products', []) as $index => $productData) {
                    if (isset($productData['product_id']) && isset($productData['quantity'])) {
                        // Validate product_id is actually an integer
                        if (!is_int($productData['product_id']) || $productData['product_id'] <= 0) {
                            $validator->errors()->add(
                                "products.{$index}.product_id",
                                "Invalid product ID format."
                            );
                            continue;
                        }

                        // Validate quantity is actually an integer
                        if (!is_int($productData['quantity']) || $productData['quantity'] <= 0) {
                            $validator->errors()->add(
                                "products.{$index}.quantity",
                                "Invalid quantity format."
                            );
                            continue;
                        }

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
            'products.max' => 'Too many products. Maximum 50 products allowed per order.',
            'products.*.product_id.required' => 'Product ID is required for each product.',
            'products.*.product_id.integer' => 'Product ID must be a valid integer.',
            'products.*.product_id.exists' => 'The selected product does not exist.',
            'products.*.quantity.required' => 'Quantity is required for each product.',
            'products.*.quantity.integer' => 'Quantity must be a valid integer.',
            'products.*.quantity.min' => 'Quantity must be at least 1.',
            'products.*.quantity.max' => 'Quantity cannot exceed 10,000 per product.',
            'status.in' => "Invalid status value. Allowed values are: {$statusValues}.",
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            'notes.no_xss' => 'Notes contain potentially dangerous content.',
            'notes.no_sql_injection' => 'Notes contain potentially dangerous patterns.',
        ];
    }
}
