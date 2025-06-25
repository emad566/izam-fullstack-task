<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Helpers\CustomFormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Hash;

class LoginRequest extends CustomFormRequest
{
    protected $roles =  [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function rules()
    {
        return $this->roles;
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();
    }

    protected function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $user = User::where('email', $this->email)->first();

            // if (!$user || !Hash::check($this->password, $user->password)) {
            //     $validator->errors()->add('email', 'The provided Credential are incorrect.');
            // }
        });
    }
}
