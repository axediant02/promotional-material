<?php

namespace App\Http\Resources\Chat;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentChatMessageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'message_id' => $this->message_id,
            'thread_id' => $this->thread_id,
            'sender_user_id' => $this->sender_user_id,
            'sender_name' => $this->sender?->name ?? 'User',
            'sender_role' => $this->sender?->role,
            'body' => $this->body,
            'created_at' => $this->created_at?->toISOString(),
            'is_own_message' => $request->user()?->user_id === $this->sender_user_id,
        ];
    }
}
