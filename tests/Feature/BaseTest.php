<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set default locale
        app()->setLocale('en');
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');

        // Create test users with different roles
        $this->user = User::first();
    }


    function test_base_api_test()
    {
        $this->assertTrue(true);
    }
}
