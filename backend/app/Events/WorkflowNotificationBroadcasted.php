<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WorkflowNotificationBroadcasted implements ShouldBroadcastNow
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
        return new PrivateChannel('users.'.$this->userId.'.notifications');
    }

    public function broadcastAs(): string
    {
        return 'workflow.notification';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return array_merge(
            [
                'kind' => 'workflow',
                'title' => 'Notification',
                'body' => '',
                'target' => null,
                'request_id' => null,
                'created_at' => now()->toISOString(),
            ],
            $this->payload
        );
    }
}
