<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_user_can_log_in_without_account_status(): void
    {
        User::query()->create([
            'name' => 'Production Team',
            'email' => 'production@example.com',
            'password' => 'password123',
            'role' => User::ROLE_PRODUCTION,
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
