<?php

namespace Tests\Feature\Request;

use App\Models\AssignedClient;
use App\Models\ClientRequest;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RequestLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_client_request_creates_folder_automatically(): void
    {
        $client = $this->createUser('New Client', 'new-client@example.com', User::ROLE_CLIENT);

        // Ensure client has no assigned folder initially
        $this->assertNull($client->assigned_folder_id);

        Sanctum::actingAs($client);

        $response = $this->postJson('/api/requests', [
            'title' => 'First Request',
            'description' => 'This should create my folder.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Request created.')
            ->assertJsonStructure([
                'message',
                'data' => [
                    'request' => [
                        'request_id',
                        'client_id',
                        'folder_id',
                        'title',
                        'description',
                        'request_type',
                        'status',
                        'due_date',
                    ],
                ],
            ]);

        // Verify folder was created
        $client->refresh();
        $this->assertNotNull($client->assigned_folder_id);

        $folder = Folder::query()->find($client->assigned_folder_id);
        $this->assertNotNull($folder);
        $this->assertEquals($client->user_id, $folder->client_id);
        $this->assertEquals($client->name, $folder->folder_name);

        // Verify request references the created folder
        $responseData = $response->json('data.request');
        $this->assertEquals($client->assigned_folder_id, $responseData['folder_id']);
    }

    public function test_subsequent_requests_reuse_existing_folder(): void
    {
        $client = $this->createUser('Returning Client', 'returning-client@example.com', User::ROLE_CLIENT);

        // Create folder manually (simulating first request)
        $folder = Folder::query()->create([
            'folder_name' => $client->name,
            'client_id' => $client->user_id,
            'created_by' => null,
        ]);

        $client->forceFill([
            'assigned_folder_id' => $folder->folder_id,
        ])->save();

        Sanctum::actingAs($client);

        // First request
        $this->postJson('/api/requests', [
            'title' => 'First Request',
            'description' => 'First request for existing folder.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
        ])->assertCreated();

        // Second request
        $this->postJson('/api/requests', [
            'title' => 'Second Request',
            'description' => 'Second request should use same folder.',
            'request_type' => ClientRequest::TYPE_UPDATE_ASSET,
        ])->assertCreated();

        // Verify only one folder exists for this client
        $this->assertEquals(1, Folder::query()->where('client_id', $client->user_id)->count());

        // Verify both requests reference the same folder
        $requests = ClientRequest::query()
            ->where('client_id', $client->user_id)
            ->get();

        $this->assertEquals(2, $requests->count());
        $this->assertTrue($requests->pluck('folder_id')->unique()->count() === 1);
        $this->assertEquals($folder->folder_id, $requests->first()->folder_id);
    }

    public function test_client_request_notifications_admin(): void
    {
        $client = $this->createUser('New Client', 'new-client@example.com', User::ROLE_CLIENT);
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);

        Sanctum::actingAs($client);

        $response = $this->postJson('/api/requests', [
            'title' => 'Request Needing Attention',
            'description' => 'Admin should be notified.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
        ]);

        $response->assertCreated();

        // Verify admin received notification
        $this->assertDatabaseHas('notifications', [
            'type' => 'App\\Notifications\\WorkflowNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $admin->user_id,
        ]);
    }

    public function test_admin_can_assign_client_to_production(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);

        // Create folder for client
        $folder = Folder::query()->create([
            'folder_name' => $client->name,
            'client_id' => $client->user_id,
            'created_by' => null,
        ]);

        $client->forceFill([
            'assigned_folder_id' => $folder->folder_id,
        ])->save();

        // Create at least one request (required for assignment)
        ClientRequest::query()->create([
            'client_id' => $client->user_id,
            'folder_id' => $folder->folder_id,
            'title' => 'Initial Request',
            'description' => 'Request before assignment.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/assignments', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message', 'Client assignment saved.')
            ->assertJsonPath('data.assignment.production_id', $production->user_id)
            ->assertJsonPath('data.assignment.client_id', $client->user_id);

        // Verify assignment exists
        $this->assertDatabaseHas('assigned_clients', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ]);
    }

    public function test_production_can_see_assigned_client_requests(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);

        // Create folder for client
        $folder = Folder::query()->create([
            'folder_name' => $client->name,
            'client_id' => $client->user_id,
            'created_by' => null,
        ]);

        $client->forceFill([
            'assigned_folder_id' => $folder->folder_id,
        ])->save();

        // Create request
        $clientRequest = ClientRequest::query()->create([
            'client_id' => $client->user_id,
            'folder_id' => $folder->folder_id,
            'title' => 'Client Request',
            'description' => 'Request for production.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);

        // Create assignment
        AssignedClient::query()->create([
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ]);

        Sanctum::actingAs($production);

        $response = $this->getJson('/api/production/requests');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Requests fetched.')
            ->assertJsonCount(1, 'data.requests')
            ->assertJsonPath('data.requests.0.request_id', $clientRequest->request_id);
    }

    public function test_production_can_update_assigned_request_status(): void
    {
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);

        // Create folder for client
        $folder = Folder::query()->create([
            'folder_name' => $client->name,
            'client_id' => $client->user_id,
            'created_by' => null,
        ]);

        // Create assignment
        AssignedClient::query()->create([
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_IN_PROGRESS,
        ]);

        // Create request
        $clientRequest = ClientRequest::query()->create([
            'client_id' => $client->user_id,
            'folder_id' => $folder->folder_id,
            'title' => 'Client Request',
            'description' => 'Request for production.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);

        Sanctum::actingAs($production);

        $response = $this->patchJson("/api/production/requests/{$clientRequest->request_id}", [
            'status' => ClientRequest::STATUS_IN_PROGRESS,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Request updated.')
            ->assertJsonPath('data.request.status', ClientRequest::STATUS_IN_PROGRESS);
    }

    public function test_production_status_update_notifies_client(): void
    {
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);

        // Create folder for client
        $folder = Folder::query()->create([
            'folder_name' => $client->name,
            'client_id' => $client->user_id,
            'created_by' => null,
        ]);

        // Create assignment
        AssignedClient::query()->create([
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_IN_PROGRESS,
        ]);

        // Create request
        $clientRequest = ClientRequest::query()->create([
            'client_id' => $client->user_id,
            'folder_id' => $folder->folder_id,
            'title' => 'Client Request',
            'description' => 'Request for production.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);

        Sanctum::actingAs($production);

        // Update to in_progress
        $this->patchJson("/api/production/requests/{$clientRequest->request_id}", [
            'status' => ClientRequest::STATUS_IN_PROGRESS,
        ])->assertOk();

        // Verify client received notification
        $this->assertDatabaseHas('notifications', [
            'type' => 'App\\Notifications\\WorkflowNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $client->user_id,
        ]);
    }

    public function test_admin_can_set_due_date_on_request(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);

        // Create folder for client
        $folder = Folder::query()->create([
            'folder_name' => $client->name,
            'client_id' => $client->user_id,
            'created_by' => null,
        ]);

        // Create request
        $clientRequest = ClientRequest::query()->create([
            'client_id' => $client->user_id,
            'folder_id' => $folder->folder_id,
            'title' => 'Client Request',
            'description' => 'Request needing due date.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/admin/requests/{$clientRequest->request_id}", [
            'due_date' => '2026-05-15 09:00:00',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Request updated.')
            ->assertJsonPath('data.request.due_date', '2026-05-15T09:00:00.000000Z');
    }

    public function test_end_to_end_request_lifecycle(): void
    {
        // 1. Client registers and creates first request
        $client = $this->createUser('New Client', 'new-client@example.com', User::ROLE_CLIENT);
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);

        Sanctum::actingAs($client);

        $createResponse = $this->postJson('/api/requests', [
            'title' => 'New Marketing Materials',
            'description' => 'Need new brochures for Q2.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
        ]);

        $createResponse->assertCreated();
        $client->refresh();
        $this->assertNotNull($client->assigned_folder_id);

        $requestId = $createResponse->json('data.request.request_id');

        // 2. Admin sets due date
        Sanctum::actingAs($admin);

        $this->patchJson("/api/admin/requests/{$requestId}", [
            'due_date' => '2026-05-20 09:00:00',
        ])->assertOk()
            ->assertJsonPath('data.request.due_date', '2026-05-20T09:00:00.000000Z');

        // 3. Admin assigns client to production
        $this->postJson('/api/admin/assignments', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ])->assertCreated();

        // 4. Production sees the request
        Sanctum::actingAs($production);

        $listResponse = $this->getJson('/api/production/requests');
        $listResponse->assertOk()
            ->assertJsonCount(1, 'data.requests')
            ->assertJsonPath('data.requests.0.request_id', $requestId);

        // 5. Production updates status to in progress
        $this->patchJson("/api/production/requests/{$requestId}", [
            'status' => ClientRequest::STATUS_IN_PROGRESS,
        ])->assertOk()
            ->assertJsonPath('data.request.status', ClientRequest::STATUS_IN_PROGRESS);

        // 6. Production marks as done
        $this->patchJson("/api/production/requests/{$requestId}", [
            'status' => ClientRequest::STATUS_DONE,
        ])->assertOk()
            ->assertJsonPath('data.request.status', ClientRequest::STATUS_DONE);
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
}
