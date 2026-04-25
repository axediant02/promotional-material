<?php

namespace App\Services;

use App\Models\AssignedClient;
use App\Models\AssignmentChatThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AssignmentChatService
{
    public function syncForAssignment(
        AssignedClient $assignment,
        ?string $previousProductionId = null,
        ?string $previousStatus = null,
    ): ?AssignmentChatThread {
        if ($previousProductionId && $previousProductionId !== $assignment->production_id) {
            $this->archiveActiveThreadsForAssignment($assignment);
        }

        if ($assignment->status === AssignedClient::STATUS_DONE) {
            $this->archiveActiveThreadsForAssignment($assignment);

            return null;
        }

        return $this->ensureActiveThread($assignment);
    }

    public function archiveForAssignmentDeletion(AssignedClient $assignment): void
    {
        $this->archiveActiveThreadsForAssignment($assignment);
    }

    public function ensureActiveThread(AssignedClient $assignment): AssignmentChatThread
    {
        return AssignmentChatThread::query()->firstOrCreate(
            [
                'assignment_id' => $assignment->id,
                'client_id' => $assignment->client_id,
                'production_id' => $assignment->production_id,
                'status' => AssignmentChatThread::STATUS_ACTIVE,
            ],
            [
                'started_at' => now(),
            ]
        );
    }

    public function archiveActiveThreadsForAssignment(AssignedClient $assignment): void
    {
        AssignmentChatThread::query()
            ->where('assignment_id', $assignment->id)
            ->where('status', AssignmentChatThread::STATUS_ACTIVE)
            ->update([
                'status' => AssignmentChatThread::STATUS_ARCHIVED,
                'closed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function accessibleThreadsQuery(User $user): Builder
    {
        $query = AssignmentChatThread::query()
            ->with([
                'client:user_id,name,email,role',
                'production:user_id,name,email,role',
                'latestMessage.sender:user_id,name,role',
            ])
            ->orderByRaw('case when status = ? then 0 else 1 end', [AssignmentChatThread::STATUS_ACTIVE])
            ->orderByDesc('last_message_at')
            ->orderByDesc('started_at');

        if ($user->isClient()) {
            return $query->where('client_id', $user->user_id);
        }

        if ($user->isProduction()) {
            return $query->where('production_id', $user->user_id);
        }

        return $query->whereRaw('1 = 0');
    }

    public function authorizeThread(User $user, AssignmentChatThread $thread): void
    {
        $isAuthorized =
            ($user->isClient() && $thread->client_id === $user->user_id)
            || ($user->isProduction() && $thread->production_id === $user->user_id);

        abort_unless($isAuthorized, 403, 'You cannot access this chat thread.');
    }

    public function unreadCountForThread(User $user, AssignmentChatThread $thread): int
    {
        $readAt = $user->isClient() ? $thread->client_last_read_at : $thread->production_last_read_at;
        $otherUserId = $user->isClient() ? $thread->production_id : $thread->client_id;

        return $thread->messages()
            ->where('sender_user_id', $otherUserId)
            ->when($readAt, fn (Builder $query) => $query->where('created_at', '>', $readAt))
            ->count();
    }

    public function markThreadRead(User $user, AssignmentChatThread $thread): AssignmentChatThread
    {
        $column = $user->isClient() ? 'client_last_read_at' : 'production_last_read_at';

        $thread->forceFill([
            $column => now(),
        ])->save();

        return $thread->fresh([
            'client:user_id,name,email,role',
            'production:user_id,name,email,role',
            'latestMessage.sender:user_id,name,role',
        ]);
    }
}
