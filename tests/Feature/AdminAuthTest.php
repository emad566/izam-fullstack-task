<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Tests\Feature\BaseTest;

class AdminAuthTest extends BaseTest
{
    public function test_admin_can_login()
    {
        $admin = Admin::first();

        $response = $this->withHeaders([
            'Accept' => 'application/json',
            'Accept-Language' => 'en'
        ])->postJson(route('auth.admin.login'), [
            'email' => config('seeder.admin.email'),
            'password' => config('seeder.admin.password'),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'item' => [
                        'id',
                        'name',
                        'email',
                    ],
                    'token'
                ]
            ]);
    }

    public function test_admin_can_logout()
    {
        $response = $this->withAuth($this->admin)->postJson(route('auth.admin.logout'));

        $response->assertStatus(200);
    }
}
