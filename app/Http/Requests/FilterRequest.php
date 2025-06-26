<?php

namespace App\Http\Requests;

use App\Helpers\CustomFormRequest;
use Illuminate\Contracts\Validation\Validator;

class FilterRequest extends CustomFormRequest
{
    /**
     * Get custom validation rules for filtering and query parameters
     */
    protected function getCustomRules(): array
    {
        return [
            // Pagination rules
            'page' => 'nullable|integer|min:1|max:1000',
            'per_page' => 'nullable|integer|min:1|max:100',

            // Sorting rules
            'sortColumn' => 'nullable|string|max:50|alpha_dash|no_sql_injection',
            'sortDirection' => 'nullable|string|in:ASC,DESC,asc,desc',

            // Date filtering
            'date_from' => 'nullable|date_format:Y-m-d\TH:i:s.v\Z|before:date_to',
            'date_to' => 'nullable|date_format:Y-m-d\TH:i:s.v\Z|after:date_from',

            // Text search rules
            'q' => 'nullable|string|max:255|no_xss|no_sql_injection|no_path_traversal',
            'name' => 'nullable|string|max:255|no_xss|no_sql_injection',

            // Category filtering
            'category_name' => 'nullable|string|max:255|no_xss|no_sql_injection',
            'category_names' => 'nullable|array|max:50',
            'category_names.*' => 'string|max:255|no_xss|no_sql_injection',
            'category_ids' => 'nullable|array|max:50',
            'category_ids.*' => 'integer|min:1',

            // Product filtering
            'product_name' => 'nullable|string|max:255|no_xss|no_sql_injection',
            'product_names' => 'nullable|array|max:50',
            'product_names.*' => 'string|max:255|no_xss|no_sql_injection',
            'product_ids' => 'nullable|array|max:50',
            'product_ids.*' => 'integer|min:1',

            // User filtering
            'user_name' => 'nullable|string|max:255|no_xss|no_sql_injection',
            'user_names' => 'nullable|array|max:50',
            'user_names.*' => 'string|max:255|no_xss|no_sql_injection',
            'user_ids' => 'nullable|array|max:50',
            'user_ids.*' => 'integer|min:1',

            // Price filtering
            'min_price' => 'nullable|numeric|min:0|max:999999.99',
            'max_price' => 'nullable|numeric|min:0|max:999999.99',

            // Status filtering
            'status' => 'nullable|string|max:50|alpha_dash|no_sql_injection',

            // Order filtering
            'order_number' => 'nullable|string|max:50|no_xss|no_sql_injection',
        ];
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        // Sanitize numeric parameters
        $numericFields = ['page', 'per_page', 'min_price', 'max_price'];
        foreach ($numericFields as $field) {
            if ($this->has($field) && $this->input($field) !== null) {
                $value = $this->input($field);
                if (is_numeric($value)) {
                    $this->merge([$field => $field === 'page' || $field === 'per_page' ? (int) $value : (float) $value]);
                }
            }
        }

        // Sanitize array parameters
        $arrayFields = ['category_names', 'category_ids', 'product_names', 'product_ids', 'user_names', 'user_ids'];
        foreach ($arrayFields as $field) {
            if ($this->has($field) && is_array($this->input($field))) {
                $values = $this->input($field);

                // Limit array size
                $values = array_slice($values, 0, 50);

                // Clean array values
                $cleanValues = [];
                foreach ($values as $value) {
                    if (str_ends_with($field, '_ids')) {
                        // For ID arrays, ensure they're positive integers
                        if (is_numeric($value) && $value > 0) {
                            $cleanValues[] = (int) $value;
                        }
                    } else {
                        // For name arrays, trim and validate strings
                        if (is_string($value) && strlen(trim($value)) > 0) {
                            $cleanValues[] = trim($value);
                        }
                    }
                }

                $this->merge([$field => $cleanValues]);
            }
        }

        // Normalize sort direction
        if ($this->has('sortDirection')) {
            $this->merge(['sortDirection' => strtoupper($this->input('sortDirection'))]);
        }
    }

    protected function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            // Validate pagination
            if ($this->has('per_page')) {
                $perPage = $this->input('per_page');
                if ($perPage > 100) {
                    $validator->errors()->add('per_page', 'Items per page cannot exceed 100.');
                }
            }

            // Validate search terms length
            $searchFields = ['q', 'name', 'category_name', 'product_name', 'user_name', 'order_number'];
            foreach ($searchFields as $field) {
                if ($this->has($field)) {
                    $value = $this->input($field);
                    if (is_string($value) && strlen($value) > 255) {
                        $validator->errors()->add($field, 'Search term is too long.');
                    }
                }
            }

            // Validate array sizes
            $arrayFields = ['category_names', 'category_ids', 'product_names', 'product_ids', 'user_names', 'user_ids'];
            foreach ($arrayFields as $field) {
                if ($this->has($field) && is_array($this->input($field))) {
                    $values = $this->input($field);
                    if (count($values) > 50) {
                        $validator->errors()->add($field, 'Too many filter values. Maximum 50 allowed.');
                    }
                }
            }

            // Validate price range - only when both min_price and max_price are provided
            if ($this->filled('min_price') && $this->filled('max_price')) {
                $minPrice = $this->input('min_price');
                $maxPrice = $this->input('max_price');
                if (is_numeric($minPrice) && is_numeric($maxPrice) && $minPrice > $maxPrice) {
                    $validator->errors()->add('max_price', 'Maximum price must be greater than or equal to minimum price.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'page.integer' => 'Page must be a valid integer.',
            'page.min' => 'Page must be at least 1.',
            'page.max' => 'Page cannot exceed 1000.',
            'per_page.integer' => 'Items per page must be a valid integer.',
            'per_page.min' => 'Items per page must be at least 1.',
            'per_page.max' => 'Items per page cannot exceed 100.',
            'sortColumn.alpha_dash' => 'Sort column contains invalid characters.',
            'sortColumn.no_sql_injection' => 'Sort column contains potentially dangerous patterns.',
            'sortDirection.in' => 'Sort direction must be ASC or DESC.',
            'date_from.date_format' => 'Date from must be in valid ISO 8601 format.',
            'date_to.date_format' => 'Date to must be in valid ISO 8601 format.',
            'date_from.before' => 'Date from must be before date to.',
            'date_to.after' => 'Date to must be after date from.',
            'min_price.numeric' => 'Minimum price must be a valid number.',
            'min_price.min' => 'Minimum price cannot be negative.',
            'max_price.numeric' => 'Maximum price must be a valid number.',
            'max_price.gte' => 'Maximum price must be greater than or equal to minimum price.',
            '*.no_xss' => 'This field contains potentially dangerous content.',
            '*.no_sql_injection' => 'This field contains potentially dangerous patterns.',
            '*.no_path_traversal' => 'This field contains invalid path characters.',
            '*.max' => 'This field is too long.',
            '*.integer' => 'This field must be a valid integer.',
            '*.min' => 'This field value is too small.',
        ];
    }
}
