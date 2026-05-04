<?php

namespace App\Http\Controllers\Api\Agent;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AgentWorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentWorkspaceController extends Controller
{
    public function __construct(private readonly AgentWorkspaceService $agentWorkspaceService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $this->authorize('agent', User::class);

        return response()->json([
            'message' => 'Agent workspace fetched.',
            'data' => [
                ...$this->agentWorkspaceService->getForUser($request->user()),
            ],
        ]);
    }
}
