<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminAssignmentRequest;
use App\Models\AssignedClient;
use Illuminate\Http\JsonResponse;

class AdminAssignmentController extends Controller
{
    public function index(): JsonResponse
    {
        abort_unless(request()->user()?->isAdmin(), 403);

        $assignments = AssignedClient::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'message' => 'Assignments fetched.',
            'data' => [
                'assignments' => $assignments,
            ],
        ]);
    }

    public function store(StoreAdminAssignmentRequest $request): JsonResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $assignment = AssignedClient::query()->firstOrNew([
            'client_id' => $request->string('client_id')->toString(),
        ]);

        $isNew = ! $assignment->exists;

        $assignment->fill([
            'production_id' => $request->string('production_id')->toString(),
            'status' => $request->string('status')->toString(),
        ]);
        $assignment->save();

        return response()->json([
            'message' => 'Client assignment saved.',
            'data' => [
                'assignment' => $assignment->fresh(),
            ],
        ], $isNew ? 201 : 200);
    }

    public function destroy(AssignedClient $assignment): JsonResponse
    {
        abort_unless(request()->user()?->isAdmin(), 403);

        $assignment->delete();

        return response()->json([
            'message' => 'Client assignment removed.',
            'data' => [],
        ]);
    }
}
