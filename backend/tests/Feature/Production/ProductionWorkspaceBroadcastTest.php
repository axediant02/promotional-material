<?php

namespace Tests\Feature\Production;

use App\Events\ProductionWorkspaceBroadcasted;
use App\Models\AssignedClient;
use App\Models\ClientRequest;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductionWorkspaceBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_request_creation_broadcasts_a_production_workspace_refresh_for_the_assigned_production_user(): void
    {
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $this->assignClientToProduction($client, $production);
        Event::fake([ProductionWorkspaceBroadcasted::class]);

        Sanctum::actingAs($client);

        $response = $this->postJson('/api/requests', [
            'title' => 'New brochure',
            'description' => 'Create a tri-fold brochure for the April campaign.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
        ]);

        $response->assertCreated();

        Event::assertDispatched(ProductionWorkspaceBroadcasted::class, function (ProductionWorkspaceBroadcasted $event) use ($client, $production): bool {
            return $event->userId === $production->user_id
                && $event->payload['kind'] === 'production_request_created'
                && $event->payload['action'] === 'upsert'
                && $event->payload['client_id'] === $client->user_id
                && $event->payload['production_id'] === $production->user_id
                && ! empty($event->payload['request_id'])
                && ! empty($event->payload['assignment_id'])
                && ! empty($event->payload['folder_id']);
        });
    }

    public function test_assignment_creation_broadcasts_a_production_workspace_refresh_for_the_assigned_production_user(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $this->createClientFolder($client, $production);
        $this->createClientRequest($client);
        Event::fake([ProductionWorkspaceBroadcasted::class]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/assignments', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ]);

        $response->assertCreated();

        Event::assertDispatched(ProductionWorkspaceBroadcasted::class, function (ProductionWorkspaceBroadcasted $event) use ($client, $production): bool {
            return $event->userId === $production->user_id
                && $event->payload['kind'] === 'production_assignment_changed'
                && $event->payload['action'] === 'upsert'
                && $event->payload['client_id'] === $client->user_id
                && $event->payload['production_id'] === $production->user_id
                && $event->payload['previous_production_id'] === null
                && ! empty($event->payload['folder_id'])
                && ! empty($event->payload['assignment_id']);
        });
    }

    public function test_assignment_change_broadcasts_a_refresh_to_the_old_and_new_production_users(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $previousProduction = $this->createUser('Previous Production', 'previous@example.com', User::ROLE_PRODUCTION);
        $newProduction = $this->createUser('New Production', 'new@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $this->assignClientToProduction($client, $previousProduction);
        $this->createClientRequest($client);
        Event::fake([ProductionWorkspaceBroadcasted::class]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/assignments', [
            'production_id' => $newProduction->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_IN_PROGRESS,
        ]);

        $response->assertOk();

        Event::assertDispatched(ProductionWorkspaceBroadcasted::class, function (ProductionWorkspaceBroadcasted $event) use ($previousProduction, $newProduction, $client): bool {
            return $event->userId === $newProduction->user_id
                && $event->payload['kind'] === 'production_assignment_changed'
                && $event->payload['action'] === 'upsert'
                && $event->payload['client_id'] === $client->user_id
                && $event->payload['production_id'] === $newProduction->user_id
                && $event->payload['previous_production_id'] === $previousProduction->user_id;
        });

        Event::assertDispatched(ProductionWorkspaceBroadcasted::class, function (ProductionWorkspaceBroadcasted $event) use ($previousProduction, $newProduction, $client): bool {
            return $event->userId === $previousProduction->user_id
                && $event->payload['kind'] === 'production_assignment_changed'
                && $event->payload['action'] === 'remove'
                && $event->payload['client_id'] === $client->user_id
                && $event->payload['production_id'] === $previousProduction->user_id
                && $event->payload['previous_production_id'] === $newProduction->user_id;
        });
    }

    public function test_assignment_deletion_broadcasts_a_removal_to_the_current_production_user(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $assignment = $this->assignClientToProduction($client, $production);
        $this->createClientRequest($client);
        Event::fake([ProductionWorkspaceBroadcasted::class]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson('/api/admin/assignments/'.$assignment->id);

        $response->assertOk();

        Event::assertDispatched(ProductionWorkspaceBroadcasted::class, function (ProductionWorkspaceBroadcasted $event) use ($client, $production): bool {
            return $event->userId === $production->user_id
                && $event->payload['kind'] === 'production_assignment_deleted'
                && $event->payload['action'] === 'remove'
                && $event->payload['client_id'] === $client->user_id
                && $event->payload['production_id'] === $production->user_id
                && ! empty($event->payload['folder_id']);
        });
    }

    public function test_unrelated_users_cannot_authorize_a_private_production_workspace_channel(): void
    {
        $authorizedUser = $this->createUser('Authorized Production', 'authorized@example.com', User::ROLE_PRODUCTION);
        $otherUser = $this->createUser('Other Production', 'other@example.com', User::ROLE_PRODUCTION);

        config(['broadcasting.default' => 'reverb']);

        Sanctum::actingAs($otherUser);

        $this->postJson('/api/broadcasting/auth', [
            'channel_name' => 'private-App.Models.User.'.$authorizedUser->user_id,
            'socket_id' => '1234.5678',
        ])->assertForbidden();
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

    private function assignClientToProduction(User $client, User $production): AssignedClient
    {
        $this->createClientFolder($client, $production);

        return AssignedClient::query()->create([
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ]);
    }

    private function createClientFolder(User $client, User $creator): Folder
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

    private function createClientRequest(User $client): ClientRequest
    {
        $folder = Folder::query()->where('client_id', $client->user_id)->firstOrFail();

        return ClientRequest::query()->create([
            'client_id' => $client->user_id,
            'folder_id' => $folder->folder_id,
            'title' => 'Client request',
            'description' => 'Request ready for production.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);
    }
}
