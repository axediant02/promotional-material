<?php

namespace App\Http\Controllers\Api\Chat;

use App\Events\AssignmentChatMessageCreated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Chat\StoreAssignmentChatMessageRequest;
use App\Models\AssignmentChatMessage;
use App\Models\AssignmentChatThread;
use App\Services\AssignmentChatService;
use Illuminate\Http\JsonResponse;

class AssignmentChatMessageController extends Controller
{
    public function __construct(private readonly AssignmentChatService $assignmentChatService)
    {
    }

    public function store(StoreAssignmentChatMessageRequest $request, AssignmentChatThread $thread): JsonResponse
    {
        $user = $request->user();
        $this->authorize('createMessage', $thread);
        abort_if($thread->status === AssignmentChatThread::STATUS_ARCHIVED, 422, 'This conversation is archived.');
        $body = trim($request->string('body')->toString());
        abort_if($body === '', 422, 'A message is required.');

        $message = AssignmentChatMessage::query()->create([
            'thread_id' => $thread->thread_id,
            'sender_user_id' => $user->user_id,
            'body' => $body,
        ]);

        $thread->forceFill([
            'last_message_at' => $message->created_at,
            'last_message_by' => $user->user_id,
            $user->isClient() ? 'client_last_read_at' : 'production_last_read_at' => now(),
        ])->save();

        $message->load('sender:user_id,name,role');
        event(new AssignmentChatMessageCreated($message, $user));

        return response()->json([
            'message' => 'Chat message sent.',
            'data' => [
                'message' => [
                    'message_id' => $message->message_id,
                    'thread_id' => $message->thread_id,
                    'sender_user_id' => $message->sender_user_id,
                    'sender_name' => $message->sender?->name ?? 'User',
                    'sender_role' => $message->sender?->role ?? null,
                    'body' => $message->body,
                    'created_at' => $message->created_at?->toISOString(),
                    'is_own_message' => true,
                ],
            ],
        ], 201);
    }
}
