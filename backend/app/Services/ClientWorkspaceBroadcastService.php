<?php

namespace App\Services;

use App\Events\ClientWorkspaceBroadcasted;
use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\User;

class ClientWorkspaceBroadcastService
{
    public function broadcastFileUpserted(MediaFile $file, User $productionUser, ?string $previousFolderId = null): void
    {
        $currentFolderId = $file->folder_id;
        $currentClientId = $this->resolveClientIdForFolder($currentFolderId);

        if ($currentClientId) {
            $this->broadcastToUser($currentClientId, [
                'kind' => 'client_workspace_sync',
                'action' => 'upsert',
                'file_id' => $file->file_id,
                'folder_id' => $currentFolderId,
                'client_id' => $currentClientId,
                'production_id' => $productionUser->user_id,
                'previous_folder_id' => $previousFolderId,
            ]);
        }

        if (! $previousFolderId || $previousFolderId === $currentFolderId) {
            return;
        }

        $previousClientId = $this->resolveClientIdForFolder($previousFolderId);

        if (! $previousClientId) {
            return;
        }

        $this->broadcastToUser($previousClientId, [
            'kind' => 'client_workspace_sync',
            'action' => 'remove',
            'file_id' => $file->file_id,
            'folder_id' => $previousFolderId,
            'client_id' => $previousClientId,
            'production_id' => $productionUser->user_id,
            'previous_folder_id' => $currentFolderId,
        ]);
    }

    public function broadcastFileRemoved(MediaFile $file, User $productionUser): void
    {
        $clientId = $this->resolveClientIdForFolder($file->folder_id);

        if (! $clientId) {
            return;
        }

        $this->broadcastToUser($clientId, [
            'kind' => 'client_workspace_sync',
            'action' => 'remove',
            'file_id' => $file->file_id,
            'folder_id' => $file->folder_id,
            'client_id' => $clientId,
            'production_id' => $productionUser->user_id,
            'previous_folder_id' => null,
        ]);
    }

    private function broadcastToUser(string $userId, array $payload): void
    {
        $user = User::query()->where('user_id', $userId)->first();

        if (! $user) {
            return;
        }

        event(new ClientWorkspaceBroadcasted($user->user_id, $payload));
    }

    private function resolveClientIdForFolder(?string $folderId): ?string
    {
        if (! $folderId) {
            return null;
        }

        return Folder::query()
            ->where('folder_id', $folderId)
            ->value('client_id');
    }
}
