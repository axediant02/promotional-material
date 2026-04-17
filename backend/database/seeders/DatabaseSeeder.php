<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'production@example.com'],
            [
                'name' => 'Production Team',
                'password' => 'password123',
                'role' => User::ROLE_PRODUCTION,
                'status' => User::STATUS_APPROVED,
            ]
        );
    }
}
