<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_registration_creates_and_assigns_a_folder(): void
    {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'Client Example',
            'email' => 'client@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Registration completed. Your folder has been assigned.')
            ->assertJsonPath('data.user.email', 'client@example.com');

        $assignedFolderId = $response->json('data.user.assigned_folder_id');

        $this->assertNotEmpty($assignedFolderId);

        $this->assertDatabaseHas('users', [
            'email' => 'client@example.com',
            'role' => 'client',
            'assigned_folder_id' => $assignedFolderId,
        ]);

        $this->assertDatabaseHas('folders', [
            'folder_id' => $assignedFolderId,
            'client_id' => $response->json('data.user.user_id'),
            'folder_name' => 'Client Example',
        ]);
    }
}
