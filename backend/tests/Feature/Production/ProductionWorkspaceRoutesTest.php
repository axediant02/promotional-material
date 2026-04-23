<?php

namespace Tests\Feature\Production;

use App\Models\AssignedClient;
use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductionWorkspaceRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_production_dashboard_is_scoped_to_assigned_client_work(): void
    {
        [$production, $assignedFolder, $unassignedFolder, $assignedFile, $unassignedFile] = $this->createProductionWorkspaceFixtures();

        Sanctum::actingAs($production);

        $response = $this->getJson('/api/dashboard');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Dashboard fetched.')
            ->assertJsonPath('data.stats.folders', 1)
            ->assertJsonPath('data.stats.files', 1)
            ->assertJsonCount(1, 'data.folders')
            ->assertJsonCount(1, 'data.recentFiles')
            ->assertJsonPath('data.folders.0.folder_id', $assignedFolder->folder_id)
            ->assertJsonPath('data.recentFiles.0.file_id', $assignedFile->file_id);

        $response->assertJsonMissing([
            'folder_id' => $unassignedFolder->folder_id,
        ]);

        $response->assertJsonMissing([
            'file_id' => $unassignedFile->file_id,
        ]);
    }

    public function test_production_can_fetch_only_assigned_client_folders(): void
    {
        [$production, $assignedFolder, $unassignedFolder] = $this->createProductionWorkspaceFixtures();

        Sanctum::actingAs($production);

        $response = $this->getJson('/api/folders');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Folders fetched.')
            ->assertJsonCount(1, 'data.folders')
            ->assertJsonPath('data.folders.0.folder_id', $assignedFolder->folder_id);

        $response->assertJsonMissing([
            'folder_id' => $unassignedFolder->folder_id,
        ]);
    }

    public function test_production_can_fetch_only_assigned_client_files(): void
    {
        [$production, , , $assignedFile, $unassignedFile] = $this->createProductionWorkspaceFixtures();

        Sanctum::actingAs($production);

        $response = $this->getJson('/api/files');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Files fetched.')
            ->assertJsonCount(1, 'data.files')
            ->assertJsonPath('data.files.0.file_id', $assignedFile->file_id);

        $response->assertJsonMissing([
            'file_id' => $unassignedFile->file_id,
        ]);
    }

    public function test_production_can_fetch_only_assigned_client_recycle_bin_files(): void
    {
        [$production, , , $assignedFile, $unassignedFile] = $this->createProductionWorkspaceFixtures();

        $assignedFile->delete();
        $unassignedFile->delete();

        Sanctum::actingAs($production);

        $response = $this->getJson('/api/recycle-bin');

        $response
            ->assertOk()
            ->assertJsonPath('message', 'Recycle bin fetched.')
            ->assertJsonCount(1, 'data.files')
            ->assertJsonPath('data.files.0.file_id', $assignedFile->file_id);

        $response->assertJsonMissing([
            'file_id' => $unassignedFile->file_id,
        ]);
    }

    public function test_production_cannot_restore_an_unassigned_client_file(): void
    {
        [$production, , , $assignedFile, $unassignedFile] = $this->createProductionWorkspaceFixtures();

        $assignedFile->delete();
        $unassignedFile->delete();

        Sanctum::actingAs($production);

        $this->postJson("/api/files/{$unassignedFile->file_id}/restore")
            ->assertForbidden();

        $this->postJson("/api/files/{$assignedFile->file_id}/restore")
            ->assertOk()
            ->assertJsonPath('data.file.file_id', $assignedFile->file_id);
    }

    /**
     * @return array{0: User, 1: Folder, 2: Folder, 3: MediaFile, 4: MediaFile}
     */
    private function createProductionWorkspaceFixtures(): array
    {
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $assignedClient = $this->createUser('Assigned Client', 'assigned-client@example.com', User::ROLE_CLIENT);
        $unassignedClient = $this->createUser('Unassigned Client', 'unassigned-client@example.com', User::ROLE_CLIENT);

        $assignedFolder = $this->assignFolderToClient($assignedClient, $production);
        $unassignedFolder = $this->assignFolderToClient($unassignedClient, $production);

        AssignedClient::query()->create([
            'production_id' => $production->user_id,
            'client_id' => $assignedClient->user_id,
            'status' => AssignedClient::STATUS_IN_PROGRESS,
        ]);

        $assignedFile = MediaFile::query()->create([
            'folder_id' => $assignedFolder->folder_id,
            'uploaded_by' => $production->user_id,
            'file_name' => 'assigned.pdf',
            'storage_disk' => 'local',
            'storage_path' => 'clients/'.$assignedFolder->folder_id.'/assigned.pdf',
            'category' => 'pdf',
        ]);

        $unassignedFile = MediaFile::query()->create([
            'folder_id' => $unassignedFolder->folder_id,
            'uploaded_by' => $production->user_id,
            'file_name' => 'unassigned.pdf',
            'storage_disk' => 'local',
            'storage_path' => 'clients/'.$unassignedFolder->folder_id.'/unassigned.pdf',
            'category' => 'pdf',
        ]);

        return [$production->fresh(), $assignedFolder, $unassignedFolder, $assignedFile, $unassignedFile];
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
