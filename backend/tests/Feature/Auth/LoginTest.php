<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
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

    public function test_admin_user_can_log_in(): void
    {
        User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Login successful.')
            ->assertJsonPath('data.user.email', 'admin@example.com')
            ->assertJsonPath('data.user.role', User::ROLE_ADMIN);

        $this->assertIsString($response->json('data.token'));
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_authenticated_user_can_fetch_the_current_user_profile(): void
    {
        $user = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'role' => User::ROLE_ADMIN,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/auth/currentUser')
            ->assertOk()
            ->assertJsonPath('message', 'Current user fetched.')
            ->assertJsonPath('data.user.user_id', $user->user_id)
            ->assertJsonPath('data.user.email', $user->email)
            ->assertJsonPath('data.user.role', User::ROLE_ADMIN);
    }
}
