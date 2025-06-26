<?php

namespace App\Http\Requests;

use App\Helpers\CustomFormRequest;
use Illuminate\Contracts\Validation\Validator;

class CategoryRequest extends CustomFormRequest
{
    protected $roles = [];

    protected function getCustomRules(): array
    {
        $this->roles = [
            'name' => 'required|string|max:255|min:2|no_xss|no_sql_injection|no_path_traversal|unique:categories,name,' . $this->id,
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
            if ($this->has('name')) {
                $name = trim($this->input('name'));

                // Additional security checks for category name
                if (strlen($name) < 2) {
                    $validator->errors()->add('name', 'Category name must be at least 2 characters long.');
                }

                // Check for potentially malicious patterns
                if (preg_match('/[<>"\'\x00-\x1f\x7f]/', $name)) {
                    $validator->errors()->add('name', 'Category name contains invalid characters.');
                }

                // Prevent categories that could be confused with system paths
                $reservedNames = ['admin', 'api', 'system', 'root', 'config', 'database', 'storage'];
                if (in_array(strtolower($name), $reservedNames)) {
                    $validator->errors()->add('name', 'This category name is reserved and cannot be used.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.string' => 'Category name must be a valid string.',
            'name.max' => 'Category name cannot exceed 255 characters.',
            'name.min' => 'Category name must be at least 2 characters long.',
            'name.unique' => 'A category with this name already exists.',
            'name.no_xss' => 'Category name contains potentially dangerous content.',
            'name.no_sql_injection' => 'Category name contains potentially dangerous patterns.',
            'name.no_path_traversal' => 'Category name contains invalid path characters.',
        ];
    }
}
