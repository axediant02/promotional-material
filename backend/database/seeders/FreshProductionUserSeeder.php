<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class FreshProductionUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'fresh-production@example.com'],
            [
                'name' => 'Fresh Production User',
                'password' => 'password123',
                'role' => User::ROLE_PRODUCTION,
            ]
        );
    }
}
