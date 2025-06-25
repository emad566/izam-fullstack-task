<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseController
{
    /**
     * Register a new user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $item = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $item->createToken('auth_token')->plainTextToken;

        return $this->sendResponse(true, [
                'item' => new UserResource($item),
                'token' => $token
        ], 'Registered successfully', null, 201, $request);
    }

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

        $item = User::where('email', $validated['email'])->first();

        if (!$item || !Hash::check($validated['password'], $item->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided Credential are incorrect.'],
            ]);
        }


        $token = $item->createToken('auth_token')->plainTextToken;


        return $this->sendResponse(true, [
            'item' => new UserResource($item),
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
