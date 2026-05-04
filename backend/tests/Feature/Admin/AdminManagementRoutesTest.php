<?php

namespace Tests\Feature\Admin;

use App\Models\AssignedClient;
use App\Models\ClientRequest;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminManagementRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_assign_a_client_to_a_production_user(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $folder = $this->assignFolderToClient($client, $production);

        ClientRequest::query()->create([
            'client_id' => $client->user_id,
            'folder_id' => $folder->folder_id,
            'title' => 'Client User Request',
            'description' => 'Initial request to make the client eligible for assignment.',
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
            ->assertJsonPath('data.assignment.client_id', $client->user_id)
            ->assertJsonPath('data.assignment.status', AssignedClient::STATUS_PENDING);

        $this->assertDatabaseHas('assigned_clients', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ]);
    }

    public function test_admin_cannot_assign_a_client_without_any_request(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/assignments', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['client_id']);

        $this->assertDatabaseMissing('assigned_clients', [
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ]);
    }

    public function test_admin_updates_an_existing_client_assignment_instead_of_creating_a_duplicate(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $firstProduction = $this->createUser('Production One', 'production-one@example.com', User::ROLE_PRODUCTION);
        $secondProduction = $this->createUser('Production Two', 'production-two@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $folder = $this->assignFolderToClient($client, $firstProduction);

        ClientRequest::query()->create([
            'client_id' => $client->user_id,
            'folder_id' => $folder->folder_id,
            'title' => 'Client User Request',
            'description' => 'Initial request to make the client eligible for reassignment.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);

        $assignment = AssignedClient::query()->create([
            'production_id' => $firstProduction->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/assignments', [
            'production_id' => $secondProduction->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_IN_PROGRESS,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Client assignment saved.')
            ->assertJsonPath('data.assignment.id', $assignment->id)
            ->assertJsonPath('data.assignment.production_id', $secondProduction->user_id)
            ->assertJsonPath('data.assignment.client_id', $client->user_id)
            ->assertJsonPath('data.assignment.status', AssignedClient::STATUS_IN_PROGRESS);

        $this->assertDatabaseCount('assigned_clients', 1);

        $this->assertDatabaseHas('assigned_clients', [
            'id' => $assignment->id,
            'production_id' => $secondProduction->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_IN_PROGRESS,
        ]);
    }

    public function test_admin_can_list_all_current_assignments(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $firstProduction = $this->createUser('Production One', 'production-one@example.com', User::ROLE_PRODUCTION);
        $secondProduction = $this->createUser('Production Two', 'production-two@example.com', User::ROLE_PRODUCTION);
        $firstClient = $this->createUser('Client One', 'client-one@example.com', User::ROLE_CLIENT);
        $secondClient = $this->createUser('Client Two', 'client-two@example.com', User::ROLE_CLIENT);

        $firstAssignment = AssignedClient::query()->create([
            'production_id' => $firstProduction->user_id,
            'client_id' => $firstClient->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ]);

        $secondAssignment = AssignedClient::query()->create([
            'production_id' => $secondProduction->user_id,
            'client_id' => $secondClient->user_id,
            'status' => AssignedClient::STATUS_DONE,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/assignments');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Assignments fetched.')
            ->assertJsonCount(2, 'data.assignments')
            ->assertJsonPath('data.assignments.0.id', $secondAssignment->id)
            ->assertJsonPath('data.assignments.1.id', $firstAssignment->id);

        $response->assertJsonFragment([
            'id' => $firstAssignment->id,
            'production_id' => $firstProduction->user_id,
            'client_id' => $firstClient->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ]);

        $response->assertJsonFragment([
            'id' => $secondAssignment->id,
            'production_id' => $secondProduction->user_id,
            'client_id' => $secondClient->user_id,
            'status' => AssignedClient::STATUS_DONE,
        ]);

        $response->assertJsonFragment([
            'user_id' => $firstProduction->user_id,
            'name' => $firstProduction->name,
            'email' => $firstProduction->email,
        ]);

        $response->assertJsonFragment([
            'user_id' => $secondProduction->user_id,
            'name' => $secondProduction->name,
            'email' => $secondProduction->email,
        ]);
    }

    public function test_admin_assignment_listing_includes_all_production_users_even_without_activity_or_assignments(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $productionOne = $this->createUser('Production One', 'production-one@example.com', User::ROLE_PRODUCTION);
        $productionTwo = $this->createUser('Production Two', 'production-two@example.com', User::ROLE_PRODUCTION);
        $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/assignments');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Assignments fetched.')
            ->assertJsonCount(0, 'data.assignments')
            ->assertJsonCount(2, 'data.production_users');

        $response->assertJsonFragment([
            'user_id' => $productionOne->user_id,
            'name' => $productionOne->name,
            'email' => $productionOne->email,
        ]);

        $response->assertJsonFragment([
            'user_id' => $productionTwo->user_id,
            'name' => $productionTwo->name,
            'email' => $productionTwo->email,
        ]);
    }

    public function test_admin_can_unassign_a_client(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);

        $assignment = AssignedClient::query()->create([
            'production_id' => $production->user_id,
            'client_id' => $client->user_id,
            'status' => AssignedClient::STATUS_IN_PROGRESS,
        ]);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/admin/assignments/{$assignment->id}");

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Client assignment removed.');

        $this->assertDatabaseMissing('assigned_clients', [
            'id' => $assignment->id,
        ]);
    }

    public function test_non_admin_users_cannot_manage_client_assignments(): void
    {
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $agent = $this->createUser('Agent User', 'agent@example.com', User::ROLE_AGENT);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);

        foreach ([$production, $agent, $client] as $user) {
            Sanctum::actingAs($user);

            $this->postJson('/api/admin/assignments', [
                'production_id' => $production->user_id,
                'client_id' => $client->user_id,
                'status' => AssignedClient::STATUS_PENDING,
            ])->assertForbidden();

            $this->getJson('/api/admin/assignments')->assertForbidden();

            $assignment = AssignedClient::query()->create([
                'production_id' => $production->user_id,
                'client_id' => $client->user_id,
                'status' => AssignedClient::STATUS_PENDING,
            ]);

            $this->deleteJson("/api/admin/assignments/{$assignment->id}")->assertForbidden();

            $assignment->delete();
        }
    }

    public function test_admin_can_fetch_all_requests(): void
    {
        [$clientRequest, $otherClientRequest] = $this->createClientRequests();
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

    public function test_admin_activity_logs_are_paginated_without_breaking_the_log_list_shape(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $subject = $this->createUser('Subject User', 'subject@example.com', User::ROLE_CLIENT);

        for ($index = 1; $index <= 16; $index++) {
            DB::table('activity_logs')->insert([
                'id' => (string) Str::uuid(),
                'user_id' => $admin->user_id,
                'action' => 'activity_'.$index,
                'subject_type' => User::class,
                'subject_id' => $subject->user_id,
                'description' => 'Activity log '.$index,
                'metadata' => json_encode(['index' => $index], JSON_THROW_ON_ERROR),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Sanctum::actingAs($admin);

        $firstPage = $this->getJson('/api/admin/activity-logs');

        $firstPage
            ->assertOk()
            ->assertJsonPath('message', 'Activity logs fetched.')
            ->assertJsonCount(15, 'data.logs')
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonPath('pagination.per_page', 15)
            ->assertJsonPath('pagination.last_page', 2)
            ->assertJsonPath('pagination.total', 16);

        $secondPage = $this->getJson('/api/admin/activity-logs?page=2&per_page=15');

        $secondPage
            ->assertOk()
            ->assertJsonCount(1, 'data.logs')
            ->assertJsonPath('pagination.current_page', 2)
            ->assertJsonPath('pagination.per_page', 15)
            ->assertJsonPath('pagination.last_page', 2)
            ->assertJsonPath('pagination.total', 16);
    }

    public function test_non_admin_users_cannot_fetch_admin_request_listing(): void
    {
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $agent = $this->createUser('Agent User', 'agent@example.com', User::ROLE_AGENT);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);

        foreach ([$production, $agent, $client] as $user) {
            Sanctum::actingAs($user);

            $this->getJson('/api/admin/requests')->assertForbidden();
        }
    }

    public function test_admin_can_fetch_a_complete_backend_driven_user_list(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $agent = $this->createUser('Agent User', 'agent@example.com', User::ROLE_AGENT);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $hiddenClient = $this->createUser('Hidden Client', 'hidden-client@example.com', User::ROLE_CLIENT);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/users');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Users fetched.')
            ->assertJsonCount(5, 'data.users');

        $response->assertJsonFragment([
            'user_id' => $admin->user_id,
            'name' => $admin->name,
            'email' => $admin->email,
            'role' => User::ROLE_ADMIN,
        ]);

        $response->assertJsonFragment([
            'user_id' => $production->user_id,
            'name' => $production->name,
            'email' => $production->email,
            'role' => User::ROLE_PRODUCTION,
        ]);

        $response->assertJsonFragment([
            'user_id' => $agent->user_id,
            'name' => $agent->name,
            'email' => $agent->email,
            'role' => User::ROLE_AGENT,
        ]);

        $response->assertJsonFragment([
            'user_id' => $client->user_id,
            'name' => $client->name,
            'email' => $client->email,
            'role' => User::ROLE_CLIENT,
        ]);

        $response->assertJsonFragment([
            'user_id' => $hiddenClient->user_id,
            'name' => $hiddenClient->name,
            'email' => $hiddenClient->email,
            'role' => User::ROLE_CLIENT,
        ]);
    }

    public function test_admin_can_update_another_users_role(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $user = $this->createUser('Agent User', 'agent@example.com', User::ROLE_AGENT);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/admin/users/{$user->user_id}", [
            'role' => User::ROLE_PRODUCTION,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('message', 'User role updated.')
            ->assertJsonPath('data.user.user_id', $user->user_id)
            ->assertJsonPath('data.user.role', User::ROLE_PRODUCTION);

        $this->assertDatabaseHas('users', [
            'user_id' => $user->user_id,
            'role' => User::ROLE_PRODUCTION,
        ]);
    }

    public function test_admin_cannot_update_their_own_role(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/admin/users/{$admin->user_id}", [
            'role' => User::ROLE_PRODUCTION,
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_invalid_role_update_is_rejected(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $user = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);

        Sanctum::actingAs($admin);

        $response = $this->patchJson("/api/admin/users/{$user->user_id}", [
            'role' => 'super-admin',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['role']);
    }

    public function test_non_admin_users_cannot_update_user_roles(): void
    {
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $agent = $this->createUser('Agent User', 'agent@example.com', User::ROLE_AGENT);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $targetUser = $this->createUser('Target User', 'target@example.com', User::ROLE_CLIENT);

        foreach ([$production, $agent, $client] as $user) {
            Sanctum::actingAs($user);

            $this->patchJson("/api/admin/users/{$targetUser->user_id}", [
                'role' => User::ROLE_AGENT,
            ])->assertForbidden();
        }
    }

    /**
     * @return array{0: ClientRequest, 1: ClientRequest}
     */
    private function createClientRequests(): array
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

        return [$clientRequest, $otherClientRequest];
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
