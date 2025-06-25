<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->createAdmin();
        $this->createUser();
    }

    private function createAdmin()
    {
        $admin = config('seeder.admin');
        Admin::create($admin);
    }

    private function createUser()
    {
        $user = config('seeder.user');
        User::create($user);
    }
}
