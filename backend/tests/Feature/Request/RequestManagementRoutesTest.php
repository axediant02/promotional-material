<?php

namespace Tests\Feature\Request;

use App\Models\AssignedClient;
use App\Models\ClientRequest;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RequestManagementRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_fetch_only_their_own_requests(): void
    {
        [$client, $otherClient, $clientRequest, $otherClientRequest] = $this->createClientsWithRequests();

        Sanctum::actingAs($client);

        $response = $this->getJson('/api/requests');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Requests fetched.')
            ->assertJsonCount(1, 'data.requests')
            ->assertJsonPath('data.requests.0.request_id', $clientRequest->request_id);

        $response->assertJsonMissing([
            'request_id' => $otherClientRequest->request_id,
        ]);
    }

    public function test_non_client_users_cannot_fetch_client_request_history(): void
    {
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $agent = $this->createUser('Agent User', 'agent@example.com', User::ROLE_AGENT);

        foreach ([$production, $admin, $agent] as $user) {
            Sanctum::actingAs($user);

            $this->getJson('/api/requests')->assertForbidden();
        }
    }

    public function test_production_can_fetch_only_assigned_client_requests(): void
    {
        [$production, $assignedRequest, $unassignedRequest] = $this->createProductionAssignmentsWithRequests();

        Sanctum::actingAs($production);

        $response = $this->getJson('/api/production/requests');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Requests fetched.')
            ->assertJsonCount(1, 'data.requests')
            ->assertJsonPath('data.requests.0.request_id', $assignedRequest->request_id);

        $response->assertJsonMissing([
            'request_id' => $unassignedRequest->request_id,
        ]);
    }

    public function test_non_production_users_cannot_fetch_production_request_listing(): void
    {
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $agent = $this->createUser('Agent User', 'agent@example.com', User::ROLE_AGENT);

        foreach ([$client, $admin, $agent] as $user) {
            Sanctum::actingAs($user);

            $this->getJson('/api/production/requests')->assertForbidden();
        }
    }

    public function test_production_can_update_status_for_an_assigned_client_request(): void
    {
        [$production, $assignedRequest] = $this->createProductionAssignmentsWithRequests();

        Sanctum::actingAs($production);

        $response = $this->patchJson("/api/production/requests/{$assignedRequest->request_id}", [
            'status' => ClientRequest::STATUS_IN_PROGRESS,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Request updated.')
            ->assertJsonPath('data.request.request_id', $assignedRequest->request_id)
            ->assertJsonPath('data.request.status', ClientRequest::STATUS_IN_PROGRESS);
    }

    public function test_production_cannot_update_due_date_on_the_production_request_route(): void
    {
        [$production, $assignedRequest] = $this->createProductionAssignmentsWithRequests();

        Sanctum::actingAs($production);

        $response = $this->patchJson("/api/production/requests/{$assignedRequest->request_id}", [
            'due_date' => '2026-05-15 09:00:00',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['due_date']);
    }

    public function test_production_cannot_update_status_for_an_unassigned_client_request(): void
    {
        [$production, , $unassignedRequest] = $this->createProductionAssignmentsWithRequests();

        Sanctum::actingAs($production);

        $this->patchJson("/api/production/requests/{$unassignedRequest->request_id}", [
            'status' => ClientRequest::STATUS_DONE,
        ])->assertForbidden();
    }

    public function test_admin_can_fetch_all_requests(): void
    {
        [, , $clientRequest, $otherClientRequest] = $this->createClientsWithRequests();
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/requests');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Requests fetched.')
            ->assertJsonCount(2, 'data.requests');

        $response->assertJsonFragment([
            'request_id' => $clientRequest->request_id,
        ]);

        $response->assertJsonFragment([
            'request_id' => $otherClientRequest->request_id,
        ]);
    }

    public function test_non_admin_users_cannot_fetch_admin_request_listing(): void
    {
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $agent = $this->createUser('Agent User', 'agent@example.com', User::ROLE_AGENT);

        foreach ([$client, $production, $agent] as $user) {
            Sanctum::actingAs($user);

            $this->getJson('/api/admin/requests')->assertForbidden();
        }
    }

    public function test_admin_can_update_due_date(): void
    {
        [$client, , $clientRequest] = $this->createClientsWithRequests();
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/admin/requests/{$clientRequest->request_id}", [
            'due_date' => '2026-05-15 09:00:00',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Request updated.')
            ->assertJsonPath('data.request.request_id', $clientRequest->request_id)
            ->assertJsonPath('data.request.client_id', $client->user_id)
            ->assertJsonPath('data.request.due_date', '2026-05-15T09:00:00.000000Z');
    }

    public function test_admin_cannot_update_status_on_the_admin_due_date_route(): void
    {
        [, , $clientRequest] = $this->createClientsWithRequests();
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/admin/requests/{$clientRequest->request_id}", [
            'status' => ClientRequest::STATUS_DONE,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_agent_is_blocked_from_all_request_management_routes(): void
    {
        [$client, , $clientRequest] = $this->createClientsWithRequests();
        $agent = $this->createUser('Agent User', 'agent@example.com', User::ROLE_AGENT);

        Sanctum::actingAs($agent);

        $this->getJson('/api/requests')->assertForbidden();
        $this->getJson('/api/production/requests')->assertForbidden();
        $this->getJson('/api/admin/requests')->assertForbidden();
        $this->patchJson("/api/production/requests/{$clientRequest->request_id}", [
            'status' => ClientRequest::STATUS_IN_PROGRESS,
        ])->assertForbidden();
        $this->patchJson("/api/admin/requests/{$clientRequest->request_id}", [
            'due_date' => '2026-05-15 09:00:00',
        ])->assertForbidden();
    }

    /**
     * @return array{0: User, 1: User, 2: ClientRequest, 3: ClientRequest}
     */
    private function createClientsWithRequests(): array
    {
        $production = $this->createUser('Production Team', 'seed-production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client One', 'client-one@example.com', User::ROLE_CLIENT);
        $otherClient = $this->createUser('Client Two', 'client-two@example.com', User::ROLE_CLIENT);

        $clientFolder = $this->assignFolderToClient($client, $production);
        $otherClientFolder = $this->assignFolderToClient($otherClient, $production);

        $clientRequest = ClientRequest::query()->create([
            'client_id' => $client->user_id,
            'folder_id' => $clientFolder->folder_id,
            'title' => 'Client One Request',
            'description' => 'Request for client one.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);

        $otherClientRequest = ClientRequest::query()->create([
            'client_id' => $otherClient->user_id,
            'folder_id' => $otherClientFolder->folder_id,
            'title' => 'Client Two Request',
            'description' => 'Request for client two.',
            'request_type' => ClientRequest::TYPE_UPDATE_ASSET,
            'status' => ClientRequest::STATUS_IN_PROGRESS,
            'due_date' => null,
        ]);

        return [$client->fresh(), $otherClient->fresh(), $clientRequest, $otherClientRequest];
    }

    /**
     * @return array{0: User, 1: ClientRequest, 2: ClientRequest}
     */
    private function createProductionAssignmentsWithRequests(): array
    {
        $production = $this->createUser('Production User', 'production-owner@example.com', User::ROLE_PRODUCTION);
        $assignedClient = $this->createUser('Assigned Client', 'assigned-client@example.com', User::ROLE_CLIENT);
        $unassignedClient = $this->createUser('Unassigned Client', 'unassigned-client@example.com', User::ROLE_CLIENT);

        $assignedFolder = $this->assignFolderToClient($assignedClient, $production);
        $unassignedFolder = $this->assignFolderToClient($unassignedClient, $production);

        AssignedClient::query()->create([
            'production_id' => $production->user_id,
            'client_id' => $assignedClient->user_id,
            'status' => AssignedClient::STATUS_IN_PROGRESS,
        ]);

        $assignedRequest = ClientRequest::query()->create([
            'client_id' => $assignedClient->user_id,
            'folder_id' => $assignedFolder->folder_id,
            'title' => 'Assigned Request',
            'description' => 'Assigned client request.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);

        $unassignedRequest = ClientRequest::query()->create([
            'client_id' => $unassignedClient->user_id,
            'folder_id' => $unassignedFolder->folder_id,
            'title' => 'Unassigned Request',
            'description' => 'Unassigned client request.',
            'request_type' => ClientRequest::TYPE_UPDATE_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);

        return [$production->fresh(), $assignedRequest, $unassignedRequest];
    }

    private function createUser(string $name, string $email, string $role): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => 'password123',
            'role' => $role,
        ]);
    }

    private function assignFolderToClient(User $client, User $creator): Folder
    {
        $folder = Folder::query()->create([
            'folder_name' => $client->name.' Folder',
            'client_id' => $client->user_id,
            'created_by' => $creator->user_id,
        ]);

        $client->forceFill([
            'assigned_folder_id' => $folder->folder_id,
        ])->save();

        return $folder;
    }
}
