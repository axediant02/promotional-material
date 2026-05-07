<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Resources\Chat\AssignmentChatThreadResource;
use App\Http\Controllers\Controller;
use App\Models\AssignmentChatThread;
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
            ->reorder()
            ->orderByDesc('last_message_at')
            ->orderByDesc('started_at')
            ->first();

        if ($thread) {
            $thread->setAttribute('unread_count', $this->assignmentChatService->unreadCountForThread($user, $thread));
        }

        return response()->json([
            'message' => 'Active chat thread fetched.',
            'data' => [
                'thread' => $thread ? new AssignmentChatThreadResource($thread) : null,
            ],
        ]);
    }

    public function index(): JsonResponse
    {
        $user = request()->user();
        $this->authorize('viewAny', AssignmentChatThread::class);

        $threads = $this->assignmentChatService->accessibleThreadsQuery($user)->get();
        $this->assignmentChatService->hydrateUnreadCountsForThreads($user, $threads);

        return response()->json([
            'message' => 'Chat threads fetched.',
            'data' => [
                'threads' => AssignmentChatThreadResource::collection($threads),
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
        $thread->setAttribute('unread_count', $this->assignmentChatService->unreadCountForThread($user, $thread));

        return response()->json([
            'message' => 'Chat thread fetched.',
            'data' => [
                'thread' => new AssignmentChatThreadResource($thread),
            ],
        ]);
    }

    public function markRead(AssignmentChatThread $thread): JsonResponse
    {
        $user = request()->user();
        $this->authorize('markRead', $thread);

        $thread = $this->assignmentChatService->markThreadRead($user, $thread);
        $thread->setAttribute('unread_count', $this->assignmentChatService->unreadCountForThread($user, $thread));

        return response()->json([
            'message' => 'Chat thread marked as read.',
            'data' => [
                'thread' => new AssignmentChatThreadResource($thread),
            ],
        ]);
    }
}
