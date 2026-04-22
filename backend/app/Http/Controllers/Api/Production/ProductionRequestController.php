<?php

namespace App\Http\Controllers\Api\Production;

use App\Http\Controllers\Controller;
use App\Http\Requests\Production\UpdateProductionRequestStatusRequest;
use App\Models\AssignedClient;
use App\Models\ClientRequest;
use Illuminate\Http\JsonResponse;

class ProductionRequestController extends Controller
{
    public function index(): JsonResponse
    {
        $user = request()->user();

        abort_unless($user?->isProduction(), 403);

        $requests = ClientRequest::query()
            ->whereIn('client_id', function ($query) use ($user): void {
                $query->select('client_id')
                    ->from((new AssignedClient())->getTable())
                    ->where('production_id', $user->user_id);
            })
            ->latest('created_at')
            ->get();

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

        abort_unless($user?->isProduction(), 403);
        abort_unless($this->isAssignedToProduction($clientRequest, $user->user_id), 403);

        $clientRequest->forceFill([
            'status' => $request->string('status')->toString(),
        ])->save();

        return response()->json([
            'message' => 'Request updated.',
            'data' => [
                'request' => $clientRequest->fresh(),
            ],
        ]);
    }

    private function isAssignedToProduction(ClientRequest $clientRequest, string $productionId): bool
    {
        return AssignedClient::query()
            ->where('production_id', $productionId)
            ->where('client_id', $clientRequest->client_id)
            ->exists();
    }
}
