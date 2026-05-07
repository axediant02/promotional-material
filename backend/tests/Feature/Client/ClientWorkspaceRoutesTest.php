<?php

namespace Tests\Feature\Client;

use App\Models\ClientRequest;
use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientWorkspaceRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_fetch_the_composed_workspace_payload(): void
    {
        Storage::fake('local');

        [$client, $folder, $file, $request] = $this->createClientWorkspaceFixtures();

        Sanctum::actingAs($client);

        $response = $this->getJson('/api/client/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Client workspace fetched.')
            ->assertJsonPath('data.dashboard.stats.folders', 1)
            ->assertJsonPath('data.dashboard.stats.files', 1)
            ->assertJsonCount(1, 'data.dashboard.folders')
            ->assertJsonCount(1, 'data.dashboard.recentFiles')
            ->assertJsonCount(1, 'data.files')
            ->assertJsonCount(1, 'data.requests')
            ->assertJsonPath('data.dashboard.folders.0.folder_id', $folder->folder_id)
            ->assertJsonPath('data.dashboard.recentFiles.0.file_id', $file->file_id)
            ->assertJsonPath('data.files.0.file_id', $file->file_id)
            ->assertJsonPath('data.requests.0.request_id', $request->request_id);
    }

    public function test_non_client_users_cannot_fetch_the_client_workspace_payload(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $agent = $this->createUser('Agent User', 'agent@example.com', User::ROLE_AGENT);

        foreach ([$admin, $production, $agent] as $user) {
            Sanctum::actingAs($user);

            $this->getJson('/api/client/dashboard')->assertForbidden();
        }
    }

    /**
     * @return array{0: User, 1: Folder, 2: MediaFile, 3: ClientRequest}
     */
    private function createClientWorkspaceFixtures(): array
    {
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);

        $folder = Folder::query()->create([
            'folder_name' => 'Client User Folder',
            'client_id' => $client->user_id,
            'created_by' => $production->user_id,
        ]);

        $client->forceFill([
            'assigned_folder_id' => $folder->folder_id,
        ])->save();

        $file = MediaFile::query()->create([
            'folder_id' => $folder->folder_id,
            'uploaded_by' => $production->user_id,
            'file_name' => 'workspace.pdf',
            'storage_disk' => 'local',
            'storage_path' => 'clients/'.$folder->folder_id.'/workspace.pdf',
            'category' => 'pdf',
        ]);

        $request = ClientRequest::query()->create([
            'client_id' => $client->user_id,
            'folder_id' => $folder->folder_id,
            'title' => 'Workspace request',
            'description' => 'Request included in the workspace payload.',
            'request_type' => ClientRequest::TYPE_NEW_ASSET,
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);

        return [$client->fresh(), $folder, $file, $request];
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
