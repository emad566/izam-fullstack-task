<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class BaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set default locale
        app()->setLocale('en');
        Cache::flush();
        Artisan::call('migrate:fresh');
        Artisan::call('db:seed');

        // Create test users with different roles
        $this->user = User::first();
        $this->admin = Admin::first();
    }


    function test_base_api_test()
    {
        $this->assertTrue(true);
    }
}
