<?php

namespace Tests\Feature\Agent;

use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AgentWorkspaceRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_fetch_the_composed_workspace_payload(): void
    {
        Storage::fake('local');

        [$agent, $firstFolder, $secondFolder, $firstFile, $secondFile] = $this->createAgentWorkspaceFixtures();

        Sanctum::actingAs($agent);

        $response = $this->getJson('/api/agent/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Agent workspace fetched.')
            ->assertJsonPath('data.dashboard.stats.folders', 2)
            ->assertJsonPath('data.dashboard.stats.files', 2)
            ->assertJsonCount(2, 'data.dashboard.folders')
            ->assertJsonCount(2, 'data.dashboard.recentFiles')
            ->assertJsonCount(2, 'data.folders')
            ->assertJsonCount(2, 'data.files')
            ->assertJsonPath('data.folders.0.folder_id', $firstFolder->folder_id)
            ->assertJsonPath('data.files.0.file_id', $firstFile->file_id);

        $response->assertJsonFragment([
            'folder_id' => $secondFolder->folder_id,
        ]);

        $response->assertJsonFragment([
            'file_id' => $secondFile->file_id,
        ]);
    }

    public function test_non_agent_users_cannot_fetch_the_agent_workspace_payload(): void
    {
        $admin = $this->createUser('Admin User', 'admin@example.com', User::ROLE_ADMIN);
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);

        foreach ([$admin, $production, $client] as $user) {
            Sanctum::actingAs($user);

            $this->getJson('/api/agent/dashboard')->assertForbidden();
        }
    }

    /**
     * @return array{0: User, 1: Folder, 2: Folder, 3: MediaFile, 4: MediaFile}
     */
    private function createAgentWorkspaceFixtures(): array
    {
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $agent = $this->createUser('Agent User', 'agent@example.com', User::ROLE_AGENT);
        $firstClient = $this->createUser('Client One', 'client-one@example.com', User::ROLE_CLIENT);
        $secondClient = $this->createUser('Client Two', 'client-two@example.com', User::ROLE_CLIENT);

        $firstFolder = $this->assignFolderToClient($firstClient, $production);
        $secondFolder = $this->assignFolderToClient($secondClient, $production);

        $firstFile = $this->createStoredFile($firstFolder, $production, 'client-one.pdf');
        $secondFile = $this->createStoredFile($secondFolder, $production, 'client-two.pdf');

        return [$agent, $firstFolder, $secondFolder, $firstFile, $secondFile];
    }

    private function createStoredFile(Folder $folder, User $uploader, string $fileName): MediaFile
    {
        $path = 'clients/'.$folder->folder_id.'/'.$fileName;

        Storage::disk('local')->put($path, 'Test file content for '.$fileName);

        return MediaFile::query()->create([
            'folder_id' => $folder->folder_id,
            'uploaded_by' => $uploader->user_id,
            'file_name' => $fileName,
            'storage_disk' => 'local',
            'storage_path' => $path,
            'category' => 'pdf',
        ]);
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
