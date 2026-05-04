<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Models\AssignmentChatThread;
use App\Models\User;
use App\Services\AssignmentChatService;
use Illuminate\Http\JsonResponse;

class AssignmentChatController extends Controller
{
    public function __construct(private readonly AssignmentChatService $assignmentChatService)
    {
    }

    public function active(): JsonResponse
    {
        $user = request()->user();
        $this->authorize('viewAny', AssignmentChatThread::class);

        $thread = $this->assignmentChatService->accessibleThreadsQuery($user)
            ->where('status', AssignmentChatThread::STATUS_ACTIVE)
            ->first();

        return response()->json([
            'message' => 'Active chat thread fetched.',
            'data' => [
                'thread' => $thread ? $this->transformThread($thread, $user) : null,
            ],
        ]);
    }

    public function index(): JsonResponse
    {
        $user = request()->user();
        $this->authorize('viewAny', AssignmentChatThread::class);

        $threads = $this->assignmentChatService->accessibleThreadsQuery($user)->get();

        return response()->json([
            'message' => 'Chat threads fetched.',
            'data' => [
                'threads' => $threads->map(fn (AssignmentChatThread $thread) => $this->transformThread($thread, $user)),
            ],
        ]);
    }

    public function show(AssignmentChatThread $thread): JsonResponse
    {
        $user = request()->user();
        $this->authorize('view', $thread);

        $thread->load([
            'client:user_id,name,email,role',
            'production:user_id,name,email,role',
            'messages.sender:user_id,name,role',
            'latestMessage.sender:user_id,name,role',
        ]);

        return response()->json([
            'message' => 'Chat thread fetched.',
            'data' => [
                'thread' => $this->transformThread($thread, $user, includeMessages: true),
            ],
        ]);
    }

    public function markRead(AssignmentChatThread $thread): JsonResponse
    {
        $user = request()->user();
        $this->authorize('markRead', $thread);

        $thread = $this->assignmentChatService->markThreadRead($user, $thread);

        return response()->json([
            'message' => 'Chat thread marked as read.',
            'data' => [
                'thread' => $this->transformThread($thread, $user),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function transformThread(AssignmentChatThread $thread, User $user, bool $includeMessages = false): array
    {
        $counterpart = $user->isClient() ? $thread->production : $thread->client;

        $payload = [
            'thread_id' => $thread->thread_id,
            'client_id' => $thread->client_id,
            'production_id' => $thread->production_id,
            'status' => $thread->status,
            'started_at' => $thread->started_at?->toISOString(),
            'closed_at' => $thread->closed_at?->toISOString(),
            'last_message_at' => $thread->last_message_at?->toISOString(),
            'unread_count' => $this->assignmentChatService->unreadCountForThread($user, $thread),
            'last_message_preview' => $thread->latestMessage?->body,
            'counterpart' => $counterpart ? [
                'user_id' => $counterpart->user_id,
                'name' => $counterpart->name,
                'email' => $counterpart->email,
                'role' => $counterpart->role,
            ] : null,
        ];

        if (! $includeMessages) {
            return $payload;
        }

        $payload['messages'] = $thread->messages
            ->sortBy('created_at')
            ->values()
            ->map(fn ($message) => [
                'message_id' => $message->message_id,
                'thread_id' => $message->thread_id,
                'sender_user_id' => $message->sender_user_id,
                'sender_name' => $message->sender?->name ?? 'User',
                'sender_role' => $message->sender?->role ?? null,
                'body' => $message->body,
                'created_at' => $message->created_at?->toISOString(),
                'is_own_message' => $message->sender_user_id === $user->user_id,
            ]);

        return $payload;
    }
}
