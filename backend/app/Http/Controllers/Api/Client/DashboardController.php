<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Services\FileService;
use App\Services\FolderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly FolderService $folderService,
        private readonly FileService $fileService,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $foldersQuery = $this->folderService->accessibleFoldersQuery($user)
            ->latest('updated_at');
        $filesQuery = $this->fileService->accessibleFilesQuery($user)
            ->latest('updated_at');

        $folders = (clone $foldersQuery)
            ->limit($user->isClient() ? 1 : 12)
            ->get();

        $recentFiles = (clone $filesQuery)
            ->with('folder:folder_id,folder_name')
            ->limit(8)
            ->get();

        $stats = [
            'folders' => (clone $foldersQuery)->count(),
            'files' => (clone $filesQuery)->count(),
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
