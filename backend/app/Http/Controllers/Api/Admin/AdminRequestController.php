<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminRequestDueDateRequest;
use App\Models\ClientRequest;
use Illuminate\Http\JsonResponse;

class AdminRequestController extends Controller
{
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

        return response()->json([
            'message' => 'Request updated.',
            'data' => [
                'request' => $clientRequest->fresh(),
            ],
        ]);
    }
}
