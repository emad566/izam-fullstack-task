<?php

namespace App\Http\Requests\Auth;

use App\Helpers\CustomFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules;

class RegisterRequest extends CustomFormRequest
{
    protected $roles = [];

    protected function getCustomRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'min:2', 'no_xss', 'no_sql_injection', 'no_path_traversal'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'no_xss', 'no_sql_injection'],
            'password' => ['required', 'confirmed', 'string', 'min:8', 'max:255', Rules\Password::defaults()],
            'password_confirmation' => ['required', 'string', 'min:8', 'max:255'],
        ];
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        // Normalize email to lowercase
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }

        // Trim name
        if ($this->has('name')) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
    }

    protected function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('name')) {
                $name = $this->input('name');

                // Check for potentially malicious patterns in name
                if (preg_match('/[<>"\'\x00-\x1f\x7f]/', $name)) {
                    $validator->errors()->add('name', 'Name contains invalid characters.');
                }

                // Prevent names that could be confused with system terms
                $reservedNames = ['admin', 'administrator', 'root', 'system', 'api', 'null', 'undefined'];
                if (in_array(strtolower($name), $reservedNames)) {
                    $validator->errors()->add('name', 'This name is reserved and cannot be used.');
                }

                // Check for minimum meaningful length
                if (strlen(trim($name)) < 2) {
                    $validator->errors()->add('name', 'Name must be at least 2 characters long.');
                }
            }

            if ($this->has('email')) {
                $email = $this->input('email');

                // Additional email format validation
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $validator->errors()->add('email', 'Please provide a valid email address.');
                }

                // Check for potentially malicious patterns in email
                if (preg_match('/[<>"\'\x00-\x1f\x7f]/', $email)) {
                    $validator->errors()->add('email', 'Email contains invalid characters.');
                }
            }

            if ($this->has('password')) {
                $password = $this->input('password');

                // Check for null bytes in password
                if (strpos($password, "\0") !== false) {
                    $validator->errors()->add('password', 'Password contains invalid characters.');
                }

                // Additional password strength checks
                if (strlen($password) < 8) {
                    $validator->errors()->add('password', 'Password must be at least 8 characters long.');
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Full name is required.',
            'name.string' => 'Name must be a valid string.',
            'name.max' => 'Name cannot exceed 255 characters.',
            'name.min' => 'Name must be at least 2 characters long.',
            'name.no_xss' => 'Name contains potentially dangerous content.',
            'name.no_sql_injection' => 'Name contains potentially dangerous patterns.',
            'name.no_path_traversal' => 'Name contains invalid path characters.',
            'email.required' => 'Email address is required.',
            'email.string' => 'Email must be a valid string.',
            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Email address cannot exceed 255 characters.',
            'email.unique' => 'This email address is already registered.',
            'email.no_xss' => 'Email contains potentially dangerous content.',
            'email.no_sql_injection' => 'Email contains potentially dangerous patterns.',
            'password.required' => 'Password is required.',
            'password.confirmed' => 'Password confirmation does not match.',
            'password.string' => 'Password must be a valid string.',
            'password.min' => 'Password must be at least 8 characters long.',
            'password.max' => 'Password cannot exceed 255 characters.',
            'password_confirmation.required' => 'Password confirmation is required.',
            'password_confirmation.string' => 'Password confirmation must be a valid string.',
        ];
    }
}
