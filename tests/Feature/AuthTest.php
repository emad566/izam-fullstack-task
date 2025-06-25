<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\Feature\BaseTest;

class AuthTest extends BaseTest
{
    protected string $testName = 'Tested user';
    protected string $testEmail = 'test_user@gmail.com';
    protected string $testPassword = '12345678';

    public function test_user_can_register()
    {
        $userData = [
            'name' => $this->testName,
            'email' => $this->testEmail,
            'password' => $this->testPassword,
            'password_confirmation' => $this->testPassword,
        ];

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Accept-Language' => 'en'
        ])->postJson(route('auth.register'), $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'item' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                    'token'
                ]
            ]);

        $user = User::where('email', $this->testEmail)->first();

        $this->assertDatabaseHas('users', [
            'email' => $this->testEmail,
            'name' => $this->testName,
        ]);
    }

    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'email' => $this->testEmail,
            'password' => $this->testPassword,
        ]);

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Accept-Language' => 'en'
        ])->postJson(route('auth.login'), [
            'email' => $this->testEmail,
            'password' => $this->testPassword,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'item' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                        'updated_at',
                    ],
                    'token'
                ]
            ]);
    }

    public function test_user_can_logout()
    {
        $response = $this->withAuth($this->user)->postJson(route('auth.logout'));

        $response->assertStatus(200);
    }
}
