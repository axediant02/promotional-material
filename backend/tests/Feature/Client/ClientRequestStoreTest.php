<?php

namespace Tests\Feature\Client;

use App\Models\ClientRequest;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientRequestStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_client_can_create_a_request_for_their_assigned_folder(): void
    {
        [$client, $folder] = $this->createApprovedClientWithAssignedFolder();

        Sanctum::actingAs($client);

        $response = $this->postJson('/api/requests', [
            'title' => 'New brochure',
            'description' => 'Create a tri-fold brochure for the April campaign.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.request.client_id', $client->user_id)
            ->assertJsonPath('data.request.folder_id', $folder->folder_id)
            ->assertJsonPath('data.request.title', 'New brochure')
            ->assertJsonPath('data.request.request_type', ClientRequest::TYPE_NEW_ASSET)
            ->assertJsonPath('data.request.status', ClientRequest::STATUS_PENDING)
            ->assertJsonPath('data.request.due_date', null);

        $this->assertDatabaseHas('client_requests', [
            'client_id' => $client->user_id,
            'folder_id' => $folder->folder_id,
            'title' => 'New brochure',
            'description' => 'Create a tri-fold brochure for the April campaign.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);
    }

    public function test_client_cannot_set_due_date_when_creating_a_request(): void
    {
        [$client] = $this->createApprovedClientWithAssignedFolder();

        Sanctum::actingAs($client);

        $response = $this->postJson('/api/requests', [
            'title' => 'Website banner refresh',
            'description' => 'Update the homepage hero banner copy and visuals.',
            'request_type' => ClientRequest::TYPE_UPDATE_ASSET,
            'due_date' => '2026-05-15 09:00:00',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['due_date']);

        $this->assertDatabaseMissing('client_requests', [
            'title' => 'Website banner refresh',
        ]);
    }

    /**
     * @return array{0: User, 1: Folder}
     */
    private function createApprovedClientWithAssignedFolder(): array
    {
        $production = User::query()->create([
            'name' => 'Production Team',
            'email' => 'production@example.com',
            'password' => 'password123',
            'role' => User::ROLE_PRODUCTION,
            'status' => User::STATUS_APPROVED,
        ]);

        $client = User::query()->create([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => 'password123',
            'role' => User::ROLE_CLIENT,
            'status' => User::STATUS_APPROVED,
        ]);

        $folder = Folder::query()->create([
            'folder_name' => 'Client User',
            'client_id' => $client->user_id,
            'created_by' => $production->user_id,
        ]);

        $client->forceFill([
            'assigned_folder_id' => $folder->folder_id,
        ])->save();

        return [$client->fresh(), $folder];
    }
}
