<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\MediaFile;
use App\Services\ActivityLogService;
use App\Services\FileService;
use Illuminate\Http\JsonResponse;

class RecycleBinController extends Controller
{
    public function __construct(
        private readonly ActivityLogService $activityLogService,
        private readonly FileService $fileService,
    )
    {
    }

    public function index(): JsonResponse
    {
        $user = request()->user();
        $this->authorize('viewRecycleBin', MediaFile::class);

        $files = $this->fileService->accessibleFilesQuery($user, onlyTrashed: true)
            ->with('folder:folder_id,folder_name', 'uploader:user_id,name')
            ->latest('deleted_at')
            ->get();

        return response()->json([
            'message' => 'Recycle bin fetched.',
            'data' => ['files' => $files],
        ]);
    }

    public function restore(string $id): JsonResponse
    {
        $user = request()->user();
        $file = MediaFile::withTrashed()->findOrFail($id);
        $this->authorize('restore', $file);
        $file->restore();

        $this->activityLogService->log(
            $user,
            'file_restored',
            $file,
            'Restored '.$file->file_name,
        );

        return response()->json([
            'message' => 'File restored.',
            'data' => ['file' => $file->load('folder', 'uploader')],
        ]);
    }
}
