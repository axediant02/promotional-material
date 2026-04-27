<?php

namespace Tests\Feature\Client;

use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClientFileDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_download_a_file_from_their_assigned_folder(): void
    {
        Storage::fake('local');

        [$client, $file] = $this->createClientFile('client-file.pdf');

        Sanctum::actingAs($client);

        $this->get("/api/files/{$file->file_id}/download")
            ->assertOk()
            ->assertDownload($file->file_name);
    }

    public function test_missing_stored_file_returns_not_found_instead_of_server_error(): void
    {
        Storage::fake('local');

        [$client, $file] = $this->createClientFile('missing-file.pdf', createPhysicalFile: false);

        Sanctum::actingAs($client);

        $this->get("/api/files/{$file->file_id}/download")
            ->assertNotFound();
    }

    /**
     * @return array{0: User, 1: MediaFile}
     */
    private function createClientFile(string $fileName, bool $createPhysicalFile = true): array
    {
        $production = $this->createUser('Production User', 'production@example.com', User::ROLE_PRODUCTION);
        $client = $this->createUser('Client User', 'client@example.com', User::ROLE_CLIENT);
        $folder = $this->assignFolderToClient($client, $production);
        $path = $folder->folder_id.'/'.$fileName;

        if ($createPhysicalFile) {
            Storage::disk('local')->put($path, 'Client file content.');
        }

        $file = MediaFile::query()->create([
            'folder_id' => $folder->folder_id,
            'uploaded_by' => $production->user_id,
            'file_name' => $fileName,
            'storage_disk' => 'local',
            'storage_path' => $path,
            'category' => 'pdf',
        ]);

        return [$client->fresh(), $file];
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
