<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminWorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminWorkspaceController extends Controller
{
    public function __construct(private readonly AdminWorkspaceService $adminWorkspaceService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $this->authorize('admin', User::class);

        return response()->json([
            'message' => 'Admin workspace fetched.',
            'data' => $this->adminWorkspaceService->getForUser($request->user()),
        ]);
    }
}
