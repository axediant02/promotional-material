<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\MediaFile;
use App\Services\ActivityLogService;
use Illuminate\Http\JsonResponse;

class RecycleBinController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function index(): JsonResponse
    {
        abort_unless(request()->user()->isProduction(), 403);

        $files = FileController::accessibleFilesQuery(request()->user(), onlyTrashed: true)
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
        abort_unless(request()->user()->isProduction(), 403);

        $file = FileController::accessibleFilesQuery(request()->user(), onlyTrashed: true)
            ->whereKey($id)
            ->first();

        abort_if(! $file, 403, 'You cannot access this file.');
        $file->restore();

        $this->activityLogService->log(
            request()->user(),
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
