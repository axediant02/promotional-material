<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminAssignmentRequest;
use App\Models\AssignedClient;
use Illuminate\Http\JsonResponse;

class AdminAssignmentController extends Controller
{
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
}
