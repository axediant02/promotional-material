<?php

namespace App\Http\Resources\Chat;

use App\Models\AssignmentChatThread;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssignmentChatThreadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $counterpart = $this->counterpartFor($user);

        $payload = [
            'thread_id' => $this->thread_id,
            'client_id' => $this->client_id,
            'production_id' => $this->production_id,
            'status' => $this->status,
            'started_at' => $this->started_at?->toISOString(),
            'closed_at' => $this->closed_at?->toISOString(),
            'last_message_at' => $this->last_message_at?->toISOString(),
            'unread_count' => (int) ($this->unread_count ?? 0),
            'last_message_preview' => $this->latestMessage?->body,
            'counterpart' => $counterpart ? [
                'user_id' => $counterpart->user_id,
                'name' => $counterpart->name,
                'email' => $counterpart->email,
                'role' => $counterpart->role,
            ] : null,
        ];

        if ($this->resource->relationLoaded('messages')) {
            $payload['messages'] = AssignmentChatMessageResource::collection(
                $this->messages->sortBy('created_at')->values()
            );
        }

        return $payload;
    }

    private function counterpartFor(?User $user): ?User
    {
        if (! $user) {
            return null;
        }

        return $user->isClient() ? $this->production : $this->client;
    }
}
