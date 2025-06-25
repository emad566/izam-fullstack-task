<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends BaseController
{
 

    /**
     * Login user and create token.
     *
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $item = Admin::where('email', $validated['email'])->first();

        if (!$item || !Hash::check($validated['password'], $item->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided Credential are incorrect.'],
            ]);
        }


        $token = $item->createToken('auth_token')->plainTextToken;


        return $this->sendResponse(true, [
                'item' => new AdminResource($item),
            'token' => $token,
        ], 'Logged in successfully', null, 200, $request);
    }

    /**
     * Logout user (Revoke the token).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse(true, [
            'item' => null
        ], 'Successfully logged out', null, 200, $request);
    }


}
