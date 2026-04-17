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

        $files = MediaFile::onlyTrashed()->with('folder:id,name', 'uploader:id,name')->latest('deleted_at')->get();

        return response()->json([
            'message' => 'Recycle bin fetched.',
            'data' => ['files' => $files],
        ]);
    }

    public function restore(string $id): JsonResponse
    {
        abort_unless(request()->user()->isProduction(), 403);

        $file = MediaFile::onlyTrashed()->findOrFail($id);
        $file->restore();

        $this->activityLogService->log(
            request()->user(),
            'file_restored',
            $file,
            'Restored '.$file->original_name,
        );

        return response()->json([
            'message' => 'File restored.',
            'data' => ['file' => $file->load('folder', 'uploader')],
        ]);
    }
}
