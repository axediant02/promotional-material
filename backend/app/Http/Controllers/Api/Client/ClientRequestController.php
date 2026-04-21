<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRequest\StoreClientRequestRequest;
use App\Models\ClientRequest;
use Illuminate\Http\JsonResponse;

class ClientRequestController extends Controller
{
    public function store(StoreClientRequestRequest $request): JsonResponse
    {
        $user = $request->user();

        abort_unless($user->isClient(), 403);

        if (! $user->assigned_folder_id) {
            return response()->json([
                'message' => 'Assigned folder is required before creating requests.',
                'errors' => [
                    'folder_id' => ['Assigned folder is required before creating requests.'],
                ],
            ], 422);
        }

        $clientRequest = ClientRequest::query()->create([
            'client_id' => $user->user_id,
            'folder_id' => $user->assigned_folder_id,
            'title' => $request->string('title')->toString(),
            'description' => $request->string('description')->toString(),
            'request_type' => $request->string('request_type')->toString(),
            'status' => ClientRequest::STATUS_PENDING,
            'due_date' => null,
        ]);

        return response()->json([
            'message' => 'Request created.',
            'data' => [
                'request' => $clientRequest,
            ],
        ], 201);
    }
}
