<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'production@example.com'],
            [
                'name' => 'Production Team',
                'password' => 'password123',
                'role' => User::ROLE_PRODUCTION,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'agent@example.com'],
            [
                'name' => 'Agent User',
                'password' => 'password123',
                'role' => User::ROLE_AGENT,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'client1@example.com'],
            [
                'name' => 'Client One',
                'password' => 'password123',
                'role' => User::ROLE_CLIENT,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'client2@example.com'],
            [
                'name' => 'Client Two',
                'password' => 'password123',
                'role' => User::ROLE_CLIENT,
            ]
        );
    }
}
