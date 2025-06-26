<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Helpers\CustomFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Hash;

class LoginRequest extends CustomFormRequest
{
    protected $roles = [
        'email' => 'required|email|max:255|no_xss|no_sql_injection',
        'password' => 'required|string|min:6|max:255',
    ];

    protected function getCustomRules(): array
    {
        return $this->roles;
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();

        // Normalize email to lowercase
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }
    }

    protected function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
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

                // Check password length
                if (strlen($password) < 6) {
                    $validator->errors()->add('password', 'Password must be at least 6 characters long.');
                }

                // Check for null bytes in password
                if (strpos($password, "\0") !== false) {
                    $validator->errors()->add('password', 'Password contains invalid characters.');
                }
            }

            // Rate limiting check could be added here
            // For now, we rely on Laravel's built-in throttling
        });
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Email address is too long.',
            'email.no_xss' => 'Email contains potentially dangerous content.',
            'email.no_sql_injection' => 'Email contains potentially dangerous patterns.',
            'password.required' => 'Password is required.',
            'password.string' => 'Password must be a valid string.',
            'password.min' => 'Password must be at least 6 characters long.',
            'password.max' => 'Password is too long.',
        ];
    }
}
