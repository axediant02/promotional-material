<?php

namespace App\Services;

use App\Models\AssignedClient;
use App\Models\AssignmentChatMessage;
use App\Models\AssignmentChatThread;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

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

    /**
     * @param  iterable<AssignmentChatThread>  $threads
     */
    public function hydrateUnreadCountsForThreads(User $user, iterable $threads): void
    {
        $threadCollection = $threads instanceof Collection
            ? $threads
            : collect($threads);

        if ($threadCollection->isEmpty()) {
            return;
        }

        $threadIds = $threadCollection->pluck('thread_id')->all();

        $countRows = AssignmentChatMessage::query()
            ->select('assignment_chat_messages.thread_id')
            ->selectRaw('count(*) as unread_count')
            ->join('assignment_chat_threads', 'assignment_chat_messages.thread_id', '=', 'assignment_chat_threads.thread_id')
            ->whereIn('assignment_chat_messages.thread_id', $threadIds)
            ->when($user->isClient(), function (Builder $query) {
                $query->whereColumn('assignment_chat_messages.sender_user_id', 'assignment_chat_threads.production_id')
                    ->where(function (Builder $query) {
                        $query->whereNull('assignment_chat_threads.client_last_read_at')
                            ->orWhereColumn('assignment_chat_messages.created_at', '>', 'assignment_chat_threads.client_last_read_at');
                    });
            }, function (Builder $query) {
                $query->whereColumn('assignment_chat_messages.sender_user_id', 'assignment_chat_threads.client_id')
                    ->where(function (Builder $query) {
                        $query->whereNull('assignment_chat_threads.production_last_read_at')
                            ->orWhereColumn('assignment_chat_messages.created_at', '>', 'assignment_chat_threads.production_last_read_at');
                    });
            })
            ->groupBy('assignment_chat_messages.thread_id')
            ->pluck('unread_count', 'thread_id');

        foreach ($threadCollection as $thread) {
            $thread->setAttribute('unread_count', (int) ($countRows[$thread->thread_id] ?? 0));
        }
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

        $thread->fill([
            $column => now(),
        ])->save();

        return $thread->fresh([
            'client:user_id,name,email,role',
            'production:user_id,name,email,role',
            'latestMessage.sender:user_id,name,role',
        ]);
    }
}
