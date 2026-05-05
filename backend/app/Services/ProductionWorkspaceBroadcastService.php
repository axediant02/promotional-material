<?php

namespace App\Services;

use App\Events\ProductionWorkspaceBroadcasted;
use App\Models\AssignedClient;
use App\Models\ClientRequest;
use App\Models\Folder;
use App\Models\User;

class ProductionWorkspaceBroadcastService
{
    public function broadcastRequestCreated(ClientRequest $clientRequest): void
    {
        $assignment = AssignedClient::query()
            ->with('production:user_id,name,email')
            ->where('client_id', $clientRequest->client_id)
            ->first();

        $productionUser = $assignment?->production;

        if (! $productionUser) {
            return;
        }

        $this->broadcastToUser($productionUser->user_id, [
            'kind' => 'production_request_created',
            'action' => 'upsert',
            'request_id' => $clientRequest->request_id,
            'assignment_id' => $assignment?->id,
            'client_id' => $clientRequest->client_id,
            'production_id' => $productionUser->user_id,
            'folder_id' => $clientRequest->folder_id,
        ]);
    }

    public function broadcastAssignmentSaved(AssignedClient $assignment, ?string $previousProductionId = null): void
    {
        $folderId = $this->resolveFolderId($assignment->client_id);

        $currentProductionId = $assignment->production_id;

        if ($currentProductionId) {
            $this->broadcastToUser($currentProductionId, [
                'kind' => 'production_assignment_changed',
                'action' => 'upsert',
                'assignment_id' => $assignment->id,
                'client_id' => $assignment->client_id,
                'production_id' => $currentProductionId,
                'previous_production_id' => $previousProductionId,
                'folder_id' => $folderId,
            ]);
        }

        if ($previousProductionId && $previousProductionId !== $currentProductionId) {
            $this->broadcastToUser($previousProductionId, [
                'kind' => 'production_assignment_changed',
                'action' => 'remove',
                'assignment_id' => $assignment->id,
                'client_id' => $assignment->client_id,
                'production_id' => $previousProductionId,
                'previous_production_id' => $currentProductionId,
                'folder_id' => $folderId,
            ]);
        }
    }

    public function broadcastAssignmentDeleted(AssignedClient $assignment): void
    {
        if (! $assignment->production_id) {
            return;
        }

        $this->broadcastToUser($assignment->production_id, [
            'kind' => 'production_assignment_deleted',
            'action' => 'remove',
            'assignment_id' => $assignment->id,
            'client_id' => $assignment->client_id,
            'production_id' => $assignment->production_id,
            'folder_id' => $this->resolveFolderId($assignment->client_id),
        ]);
    }

    private function broadcastToUser(string $userId, array $payload): void
    {
        $user = User::query()->where('user_id', $userId)->first();

        if (! $user) {
            return;
        }

        event(new ProductionWorkspaceBroadcasted($user->user_id, $payload));
    }

    private function resolveFolderId(string $clientId): ?string
    {
        return Folder::query()
            ->where('client_id', $clientId)
            ->value('folder_id');
    }
}
