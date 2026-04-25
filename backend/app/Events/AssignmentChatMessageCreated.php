<?php

namespace App\Events;

use App\Models\AssignmentChatMessage;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssignmentChatMessageCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly AssignmentChatMessage $message,
        public readonly User $viewer,
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('assignment-chat.'.$this->message->thread_id);
    }

    public function broadcastAs(): string
    {
        return 'assignment-chat.message.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $message = $this->message->loadMissing('sender:user_id,name,role');

        return [
            'thread_id' => $message->thread_id,
            'message' => [
                'message_id' => $message->message_id,
                'thread_id' => $message->thread_id,
                'sender_user_id' => $message->sender_user_id,
                'sender_name' => $message->sender?->name ?? 'User',
                'sender_role' => $message->sender?->role ?? null,
                'body' => $message->body,
                'created_at' => $message->created_at?->toISOString(),
                'is_own_message' => $message->sender_user_id === $this->viewer->user_id,
            ],
        ];
    }
}
