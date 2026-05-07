<?php

namespace Tests\Feature\Client;

use App\Events\ClientWorkspaceBroadcasted;
use App\Models\AssignedClient;
use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientWorkspaceBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_upload_broadcasts_an_upsert_to_the_assigned_client_only(): void
    {
        $this->fakeClientStorage();
        Event::fake([ClientWorkspaceBroadcasted::class]);

        [$production, $client, $folder] = $this->createAssignedClientWorkspace();

        Sanctum::actingAs($production);

        $response = $this->post('/api/files', [
            'folder_id' => $folder->folder_id,
            'file' => UploadedFile::fake()->create('campaign.pdf', 120, 'application/pdf'),
        ]);

        $response->assertCreated();

        Event::assertDispatched(ClientWorkspaceBroadcasted::class, function (ClientWorkspaceBroadcasted $event) use ($client, $production, $folder): bool {
            return $event->userId === $client->user_id
                && $event->payload['kind'] === 'client_workspace_sync'
                && $event->payload['action'] === 'upsert'
                && ! empty($event->payload['file_id'])
                && $event->payload['folder_id'] === $folder->folder_id
                && $event->payload['client_id'] === $client->user_id
                && $event->payload['production_id'] === $production->user_id
                && $event->payload['previous_folder_id'] === null;
        });
    }

    public function test_file_move_broadcasts_a_remove_to_the_previous_client_and_an_upsert_to_the_new_client(): void
    {
        $this->fakeClientStorage();
        Event::fake([ClientWorkspaceBroadcasted::class]);

        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $previousClient = $this->createUser('Previous Client', 'previous@example.com', User::ROLE_CLIENT);
        $nextClient = $this->createUser('Next Client', 'next@example.com', User::ROLE_CLIENT);

        $previousFolder = $this->createClientFolder($previousClient, $production);
        $nextFolder = $this->createClientFolder($nextClient, $production);
        $this->assignClientToProduction($previousClient, $production);
        $this->assignClientToProduction($nextClient, $production);

        $file = MediaFile::query()->create([
            'folder_id' => $previousFolder->folder_id,
            'uploaded_by' => $production->user_id,
            'file_name' => 'workspace.pdf',
            'storage_disk' => 'local',
            'storage_path' => 'clients/'.$previousFolder->folder_id.'/workspace.pdf',
            'category' => 'pdf',
        ]);

        Sanctum::actingAs($production);

        $response = $this->patch('/api/files/'.$file->file_id, [
            'folder_id' => $nextFolder->folder_id,
            'file' => UploadedFile::fake()->create('workspace-refresh.pdf', 160, 'application/pdf'),
        ]);

        $response->assertOk();

        Event::assertDispatched(ClientWorkspaceBroadcasted::class, function (ClientWorkspaceBroadcasted $event) use ($previousClient, $nextClient, $production, $previousFolder, $nextFolder, $file): bool {
            return $event->userId === $nextClient->user_id
                && $event->payload['kind'] === 'client_workspace_sync'
                && $event->payload['action'] === 'upsert'
                && $event->payload['file_id'] === $file->file_id
                && $event->payload['folder_id'] === $nextFolder->folder_id
                && $event->payload['client_id'] === $nextClient->user_id
                && $event->payload['production_id'] === $production->user_id
                && $event->payload['previous_folder_id'] === $previousFolder->folder_id;
        });

        Event::assertDispatched(ClientWorkspaceBroadcasted::class, function (ClientWorkspaceBroadcasted $event) use ($previousClient, $production, $previousFolder, $nextFolder, $file): bool {
            return $event->userId === $previousClient->user_id
                && $event->payload['kind'] === 'client_workspace_sync'
                && $event->payload['action'] === 'remove'
                && $event->payload['file_id'] === $file->file_id
                && $event->payload['folder_id'] === $previousFolder->folder_id
                && $event->payload['client_id'] === $previousClient->user_id
                && $event->payload['production_id'] === $production->user_id
                && $event->payload['previous_folder_id'] === $nextFolder->folder_id;
        });
    }

    public function test_file_delete_broadcasts_a_remove_to_the_assigned_client(): void
    {
        $this->fakeClientStorage();
        Event::fake([ClientWorkspaceBroadcasted::class]);

        [$production, $client, $folder, $file] = $this->createUploadedWorkspaceFile();

        Sanctum::actingAs($production);

        $response = $this->deleteJson('/api/files/'.$file->file_id);

        $response->assertOk();

        Event::assertDispatched(ClientWorkspaceBroadcasted::class, function (ClientWorkspaceBroadcasted $event) use ($client, $production, $folder, $file): bool {
            return $event->userId === $client->user_id
                && $event->payload['kind'] === 'client_workspace_sync'
                && $event->payload['action'] === 'remove'
                && $event->payload['file_id'] === $file->file_id
                && $event->payload['folder_id'] === $folder->folder_id
                && $event->payload['client_id'] === $client->user_id
                && $event->payload['production_id'] === $production->user_id
                && $event->payload['previous_folder_id'] === null;
        });
    }

    public function test_file_restore_broadcasts_an_upsert_to_the_assigned_client(): void
    {
        $this->fakeClientStorage();
        Event::fake([ClientWorkspaceBroadcasted::class]);

        [$production, $client, $folder, $file] = $this->createUploadedWorkspaceFile();

        Sanctum::actingAs($production);

        $this->deleteJson('/api/files/'.$file->file_id)->assertOk();

        $response = $this->postJson('/api/files/'.$file->file_id.'/restore');

        $response->assertOk();

        Event::assertDispatched(ClientWorkspaceBroadcasted::class, function (ClientWorkspaceBroadcasted $event) use ($client, $production, $folder, $file): bool {
            return $event->userId === $client->user_id
                && $event->payload['kind'] === 'client_workspace_sync'
                && $event->payload['action'] === 'upsert'
                && $event->payload['file_id'] === $file->file_id
                && $event->payload['folder_id'] === $folder->folder_id
                && $event->payload['client_id'] === $client->user_id
                && $event->payload['production_id'] === $production->user_id
                && $event->payload['previous_folder_id'] === null;
        });
    }

    public function test_unrelated_users_cannot_authorize_a_private_client_workspace_channel(): void
    {
        $authorizedUser = $this->createUser('Authorized Client', 'authorized@example.com', User::ROLE_CLIENT);
        $otherUser = $this->createUser('Other Client', 'other@example.com', User::ROLE_CLIENT);

        config(['broadcasting.default' => 'reverb']);

        Sanctum::actingAs($otherUser);

        $this->postJson('/api/broadcasting/auth', [
            'channel_name' => 'private-App.Models.User.'.$authorizedUser->user_id,
            'socket_id' => '1234.5678',
        ])->assertForbidden();
    }

    /**
     * @return array{0: User, 1: User, 2: Folder}
     */
    private function createAssignedClientWorkspace(): array
    {
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $folder = $this->createClientFolder($client, $production);
        $this->assignClientToProduction($client, $production);

        return [$production, $client, $folder];
    }

    /**
     * @return array{0: User, 1: User, 2: Folder, 3: MediaFile}
     */
    private function createUploadedWorkspaceFile(): array
    {
        [$production, $client, $folder] = $this->createAssignedClientWorkspace();

        $file = MediaFile::query()->create([
            'folder_id' => $folder->folder_id,
            'uploaded_by' => $production->user_id,
            'file_name' => 'workspace.pdf',
            'storage_disk' => 'local',
            'storage_path' => 'clients/'.$folder->folder_id.'/workspace.pdf',
            'category' => 'pdf',
        ]);

        return [$production, $client, $folder, $file];
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
        return AssignedClient::query()->firstOrCreate([
            'client_id' => $client->user_id,
        ], [
            'production_id' => $production->user_id,
            'status' => AssignedClient::STATUS_PENDING,
        ]);
    }

    private function fakeClientStorage(): void
    {
        Storage::fake('local');
        Storage::disk('local')->makeDirectory('clients');
    }
}
