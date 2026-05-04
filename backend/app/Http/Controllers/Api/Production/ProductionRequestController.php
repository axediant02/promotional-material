<?php

namespace App\Http\Controllers\Api\Production;

use App\Http\Controllers\Controller;
use App\Http\Requests\Production\UpdateProductionRequestStatusRequest;
use App\Models\ClientRequest;
use App\Services\ProductionRequestService;
use App\Services\WorkflowNotificationService;
use Illuminate\Http\JsonResponse;

class ProductionRequestController extends Controller
{
    public function __construct(
        private readonly WorkflowNotificationService $workflowNotificationService,
        private readonly ProductionRequestService $productionRequestService,
    )
    {
    }

    public function index(): JsonResponse
    {
        $user = request()->user();
        $this->authorize('viewAnyProduction', ClientRequest::class);

        $requests = $this->productionRequestService->accessibleRequestsQuery($user)->get();

        return response()->json([
            'message' => 'Requests fetched.',
            'data' => [
                'requests' => $requests,
            ],
        ]);
    }

    public function update(UpdateProductionRequestStatusRequest $request, ClientRequest $clientRequest): JsonResponse
    {
        $user = $request->user();
        $this->authorize('updateProduction', $clientRequest);
        $newStatus = $request->string('status')->toString();
        $previousStatus = $clientRequest->status;

        $clientRequest->forceFill([
            'status' => $newStatus,
        ])->save();

        if (
            $previousStatus !== $newStatus
            && in_array($newStatus, [ClientRequest::STATUS_IN_PROGRESS, ClientRequest::STATUS_DONE], true)
            && $clientRequest->client
        ) {
            $this->workflowNotificationService->sendToUser($clientRequest->client, [
                'kind' => 'request_status_updated',
                'title' => 'Request status updated',
                'body' => sprintf(
                    'Your request "%s" is now %s.',
                    $clientRequest->title,
                    str_replace('_', ' ', $newStatus)
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
