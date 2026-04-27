<?php

namespace Tests\Feature\Agent;

use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AgentFileAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_agent_can_fetch_all_client_folders_and_files(): void
    {
        [$agent, $firstFolder, $secondFolder, $firstFile, $secondFile] = $this->createAgentFileFixtures();

        Sanctum::actingAs($agent);

        $foldersResponse = $this->getJson('/api/folders');

        $foldersResponse
            ->assertOk()
            ->assertJsonPath('message', 'Folders fetched.')
            ->assertJsonCount(2, 'data.folders');

        $foldersResponse->assertJsonFragment([
            'folder_id' => $firstFolder->folder_id,
        ]);

        $foldersResponse->assertJsonFragment([
            'folder_id' => $secondFolder->folder_id,
        ]);

        $filesResponse = $this->getJson('/api/files');

        $filesResponse
            ->assertOk()
            ->assertJsonPath('message', 'Files fetched.')
            ->assertJsonCount(2, 'data.files');

        $filesResponse->assertJsonFragment([
            'file_id' => $firstFile->file_id,
        ]);

        $filesResponse->assertJsonFragment([
            'file_id' => $secondFile->file_id,
        ]);
    }

    public function test_agent_can_download_any_client_file(): void
    {
        [$agent, , , $firstFile, $secondFile] = $this->createAgentFileFixtures();

        Sanctum::actingAs($agent);

        $this->get("/api/files/{$firstFile->file_id}/download")
            ->assertOk()
            ->assertDownload($firstFile->file_name);

        $this->get("/api/files/{$secondFile->file_id}/download")
            ->assertOk()
            ->assertDownload($secondFile->file_name);
    }

    public function test_agent_cannot_upload_delete_or_restore_files(): void
    {
        [$agent, $folder, , $file] = $this->createAgentFileFixtures();

        Sanctum::actingAs($agent);

        $this->postJson('/api/files', [
            'folder_id' => $folder->folder_id,
            'file' => UploadedFile::fake()->create('agent-upload.pdf', 24, 'application/pdf'),
        ])->assertForbidden();

        $this->deleteJson("/api/files/{$file->file_id}")
            ->assertForbidden();

        $file->delete();

        $this->postJson("/api/files/{$file->file_id}/restore")
            ->assertForbidden();
    }

    /**
     * @return array{0: User, 1: Folder, 2: Folder, 3: MediaFile, 4: MediaFile}
     */
    private function createAgentFileFixtures(): array
    {
        Storage::fake('local');

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
