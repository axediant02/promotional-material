<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUser(
            User::factory()->admin(),
            'admin@example.com',
            'Admin User'
        );

        $this->seedUser(
            User::factory()->production(),
            'production@example.com',
            'Production Team'
        );

        $this->seedUser(
            User::factory()->agent(),
            'agent@example.com',
            'Agent User'
        );

        $this->seedUser(
            User::factory()->client(),
            'client1@example.com',
            'Client One'
        );

        $this->seedUser(
            User::factory()->client(),
            'client2@example.com',
            'Client Two'
        );
    }

    protected function seedUser(\Illuminate\Database\Eloquent\Factories\Factory $factory, string $email, string $name): void
    {
        $attributes = $factory->raw([
            'email' => $email,
            'name' => $name,
            'password' => 'password123',
        ]);

        User::query()->updateOrCreate(
            ['email' => $email],
            $attributes
        );
    }
}
