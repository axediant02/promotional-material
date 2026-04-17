<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_seeded_user_can_log_in(): void
    {
        User::query()->create([
            'name' => 'Production Team',
            'email' => 'production@example.com',
            'password' => 'password123',
            'role' => User::ROLE_PRODUCTION,
            'status' => User::STATUS_APPROVED,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'production@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonPath('data.user.email', 'production@example.com');

        $this->assertIsString($response->json('data.token'));
        $this->assertNotEmpty($response->json('data.token'));
    }
}
