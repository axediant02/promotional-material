<?php

namespace App\Http\Controllers\Api\Production;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ProductionWorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionWorkspaceController extends Controller
{
    public function __construct(private readonly ProductionWorkspaceService $productionWorkspaceService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $this->authorize('production', User::class);

        return response()->json([
            'message' => 'Production workspace fetched.',
            'data' => $this->productionWorkspaceService->getForUser($request->user()),
        ]);
    }
}
