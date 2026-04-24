<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminRequestDueDateRequest;
use App\Models\ClientRequest;
use App\Services\WorkflowNotificationService;
use Illuminate\Http\JsonResponse;

class AdminRequestController extends Controller
{
    public function __construct(private readonly WorkflowNotificationService $workflowNotificationService)
    {
    }

    public function index(): JsonResponse
    {
        abort_unless(request()->user()?->isAdmin(), 403);

        $requests = ClientRequest::query()
            ->latest('created_at')
            ->get();

        return response()->json([
            'message' => 'Requests fetched.',
            'data' => [
                'requests' => $requests,
            ],
        ]);
    }

    public function update(UpdateAdminRequestDueDateRequest $request, ClientRequest $clientRequest): JsonResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $clientRequest->forceFill([
            'due_date' => $request->date('due_date'),
        ])->save();

        if ($clientRequest->client) {
            $this->workflowNotificationService->sendToUser($clientRequest->client, [
                'kind' => 'due_date_updated',
                'title' => 'Request due date updated',
                'body' => sprintf(
                    'Your request "%s" is now due on %s.',
                    $clientRequest->title,
                    optional($clientRequest->due_date)->format('M j, Y') ?? 'a new schedule'
                ),
                'target' => 'request-history',
                'request_id' => $clientRequest->request_id,
            ]);
        }

        return response()->json([
            'message' => 'Request updated.',
            'data' => [
                'request' => $clientRequest->fresh(),
            ],
        ]);
    }
}
