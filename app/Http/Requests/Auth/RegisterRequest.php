<?php

namespace App\Http\Requests\Auth;

use App\Helpers\CustomFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rules;

class RegisterRequest extends CustomFormRequest
{
    protected $roles = [];

    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();
    }

    protected function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            //
        });
    }
}
