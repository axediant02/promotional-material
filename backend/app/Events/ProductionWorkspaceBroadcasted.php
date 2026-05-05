<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductionWorkspaceBroadcasted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly string $userId,
        public readonly array $payload,
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('App.Models.User.'.$this->userId);
    }

    public function broadcastAs(): string
    {
        return 'production.workspace.sync';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return array_merge(
            [
                'kind' => 'production_workspace_sync',
                'action' => 'refresh',
                'request_id' => null,
                'assignment_id' => null,
                'client_id' => null,
                'production_id' => null,
                'previous_production_id' => null,
                'folder_id' => null,
                'created_at' => now()->toISOString(),
            ],
            $this->payload
        );
    }
}
