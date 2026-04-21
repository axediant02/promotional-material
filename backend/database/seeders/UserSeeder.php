<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => 'password123',
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_APPROVED,
            ]
        );

        // Production user
        User::query()->firstOrCreate(
            ['email' => 'production@example.com'],
            [
                'name' => 'Production Team',
                'password' => 'password123',
                'role' => User::ROLE_PRODUCTION,
                'status' => User::STATUS_APPROVED,
            ]
        );

        // Agent user
        User::query()->firstOrCreate(
            ['email' => 'agent@example.com'],
            [
                'name' => 'Agent User',
                'password' => 'password123',
                'role' => User::ROLE_AGENT,
                'status' => User::STATUS_APPROVED,
            ]
        );

        // Approved Client 1
        User::query()->firstOrCreate(
            ['email' => 'client1@example.com'],
            [
                'name' => 'Client One',
                'password' => 'password123',
                'role' => User::ROLE_CLIENT,
                'status' => User::STATUS_APPROVED,
            ]
        );

        // Approved Client 2
        User::query()->firstOrCreate(
            ['email' => 'client2@example.com'],
            [
                'name' => 'Client Two',
                'password' => 'password123',
                'role' => User::ROLE_CLIENT,
                'status' => User::STATUS_APPROVED,
            ]
        );

        // Pending Client (for testing approval flow)
        User::query()->firstOrCreate(
            ['email' => 'pending@example.com'],
            [
                'name' => 'Pending Client',
                'password' => 'password123',
                'role' => User::ROLE_CLIENT,
                'status' => User::STATUS_PENDING,
            ]
        );
    }
}
