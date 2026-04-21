<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function show(): JsonResponse
    {
        /** @var User $user */
        $user = request()->user();

        $folders = FolderController::accessibleFoldersQuery($user)
            ->latest('updated_at')
            ->limit($user->isClient() ? 1 : 12)
            ->get();

        $recentFiles = FileController::accessibleFilesQuery($user)
            ->with('folder:folder_id,folder_name')
            ->latest('updated_at')
            ->limit(8)
            ->get();

        $stats = [
            'folders' => FolderController::accessibleFoldersQuery($user)->count(),
            'files' => FileController::accessibleFilesQuery($user)->count(),
        ];

        return response()->json([
            'message' => 'Dashboard fetched.',
            'data' => [
                'user' => $user->load('assignedFolder'),
                'stats' => $stats,
                'folders' => $folders,
                'recentFiles' => $recentFiles,
            ],
        ]);
    }
}
