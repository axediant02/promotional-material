<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $notifications = $user
            ->notifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (DatabaseNotification $notification): array => $this->transformNotification($notification));

        return response()->json([
            'message' => 'Notifications fetched.',
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    public function update(Request $request, DatabaseNotification $notification): JsonResponse
    {
        $user = $request->user();
        $this->authorizeNotification($user, $notification);

        if ($notification->read_at === null) {
            $notification->markAsRead();
        }

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => [
                'notification' => $this->transformNotification($notification->fresh()),
                'unread_count' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'All notifications marked as read.',
            'data' => [
                'unread_count' => 0,
            ],
        ]);
    }

    private function authorizeNotification(User $user, DatabaseNotification $notification): void
    {
        abort_unless(
            $notification->notifiable_type === User::class
            && $notification->notifiable_id === $user->user_id,
            403
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function transformNotification(DatabaseNotification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type,
            'kind' => $notification->data['kind'] ?? 'workflow',
            'title' => $notification->data['title'] ?? 'Notification',
            'body' => $notification->data['body'] ?? '',
            'target' => $notification->data['target'] ?? null,
            'request_id' => $notification->data['request_id'] ?? null,
            'read_at' => $notification->read_at?->toISOString(),
            'created_at' => $notification->created_at?->toISOString(),
        ];
    }
}
