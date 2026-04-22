<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_registration_creates_an_unassigned_client_account(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Client Example',
            'email' => 'client@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Registration completed. Your folder will be created when you submit your first request.')
            ->assertJsonPath('data.user.email', 'client@example.com')
            ->assertJsonPath('data.user.assigned_folder_id', null);

        $this->assertDatabaseHas('users', [
            'email' => 'client@example.com',
            'role' => 'client',
            'assigned_folder_id' => null,
        ]);

        $this->assertDatabaseCount('folders', 0);
    }
}
