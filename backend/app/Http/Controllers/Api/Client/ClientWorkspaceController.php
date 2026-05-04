<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ClientWorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientWorkspaceController extends Controller
{
    public function __construct(private readonly ClientWorkspaceService $clientWorkspaceService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $this->authorize('client', User::class);

        return response()->json([
            'message' => 'Client workspace fetched.',
            'data' => [
                ...$this->clientWorkspaceService->getForUser($request->user()),
            ],
        ]);
    }
}
