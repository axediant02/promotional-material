<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\FileService;
use App\Services\FolderService;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private readonly FolderService $folderService,
        private readonly FileService $fileService,
    ) {
    }

    public function show(): JsonResponse
    {
        $user = request()->user();

        $folders = $this->folderService->accessibleFoldersQuery($user)
            ->latest('updated_at')
            ->limit($user->isClient() ? 1 : 12)
            ->get();

        $recentFiles = $this->fileService->accessibleFilesQuery($user)
            ->with('folder:folder_id,folder_name')
            ->latest('updated_at')
            ->limit(8)
            ->get();

        $stats = [
            'folders' => $this->folderService->accessibleFoldersQuery($user)->count(),
            'files' => $this->fileService->accessibleFilesQuery($user)->count(),
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
