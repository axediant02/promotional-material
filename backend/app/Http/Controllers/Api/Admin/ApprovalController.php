<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index(): JsonResponse
    {
        $pendingClients = User::query()
            ->pending()
            ->where('role', User::ROLE_CLIENT)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Pending clients fetched.',
            'data' => ['clients' => $pendingClients],
        ]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        abort_unless($user->isClient(), 404);

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
        ]);

        $user->status = $validated['status'];

        if ($validated['status'] === User::STATUS_APPROVED && ! $user->assigned_folder_id) {
            $folder = Folder::create([
                'folder_name' => $user->name,
                'client_id' => $user->user_id,
                'created_by' => $request->user()->user_id,
            ]);

            $user->assigned_folder_id = $folder->folder_id;
        }

        $user->save();

        $this->activityLogService->log(
            $request->user(),
            'client_'.$validated['status'],
            $user,
            ucfirst($validated['status']).' client account for '.$user->email,
        );

        return response()->json([
            'message' => 'Client status updated.',
            'data' => ['user' => $user->load('assignedFolder')],
        ]);
    }
}
