<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\File\StoreFileRequest;
use App\Http\Requests\File\UpdateFileRequest;
use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public static function accessibleFilesQuery(User $user): Builder
    {
        $query = MediaFile::query()->with('folder:id,name,client_user_id', 'uploader:id,name,email');

        if ($user->isClient()) {
            return $query->whereHas('folder', fn (Builder $folderQuery) => $folderQuery->where('id', $user->assigned_folder_id));
        }

        return $query;
    }

    public function index(): JsonResponse
    {
        $files = self::accessibleFilesQuery(request()->user())
            ->when(request('folder_id'), fn (Builder $query, string $folderId) => $query->where('folder_id', $folderId))
            ->when(request('q'), fn (Builder $query, string $search) => $query->where('original_name', 'like', "%{$search}%"))
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Files fetched.',
            'data' => ['files' => $files],
        ]);
    }

    public function store(StoreFileRequest $request): JsonResponse
    {
        abort_unless($request->user()->isProduction(), 403);

        $folder = Folder::query()->findOrFail($request->input('folder_id'));
        $uploadedFile = $request->file('file');
        $disk = config('filesystems.default', 'local');
        $path = $uploadedFile->storeAs(
            trim('clients/'.$folder->id, '/'),
            Str::uuid().'-'.$uploadedFile->getClientOriginalName(),
            $disk
        );

        $file = MediaFile::create([
            'folder_id' => $folder->id,
            'uploaded_by' => $request->user()->id,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'storage_disk' => $disk,
            'storage_path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
        ]);

        $this->activityLogService->log(
            $request->user(),
            'file_uploaded',
            $file,
            'Uploaded '.$file->original_name,
        );

        return response()->json([
            'message' => 'File uploaded.',
            'data' => ['file' => $file->load('folder', 'uploader')],
        ], 201);
    }

    public function show(MediaFile $file): JsonResponse
    {
        $this->authorizeFile($file, request()->user());

        return response()->json([
            'message' => 'File fetched.',
            'data' => ['file' => $file->load('folder', 'uploader')],
        ]);
    }

    public function update(UpdateFileRequest $request, MediaFile $file): JsonResponse
    {
        abort_unless($request->user()->isProduction(), 403);

        $file->fill($request->validated());
        $file->save();

        return response()->json([
            'message' => 'File updated.',
            'data' => ['file' => $file->load('folder', 'uploader')],
        ]);
    }

    public function destroy(MediaFile $file): JsonResponse
    {
        abort_unless(request()->user()->isProduction(), 403);

        $file->last_deleted_at = now();
        $file->save();
        $file->delete();

        $this->activityLogService->log(
            request()->user(),
            'file_deleted',
            $file,
            'Deleted '.$file->original_name,
        );

        return response()->json([
            'message' => 'File moved to recycle bin.',
            'data' => null,
        ]);
    }

    public function download(string $id)
    {
        $file = MediaFile::withTrashed()->findOrFail($id);
        $this->authorizeFile($file, request()->user());

        return Storage::disk($file->storage_disk)->download($file->storage_path, $file->original_name);
    }

    public function preview(string $id)
    {
        $file = MediaFile::withTrashed()->findOrFail($id);
        $this->authorizeFile($file, request()->user());

        return Storage::disk($file->storage_disk)->response($file->storage_path, $file->original_name, [
            'Content-Type' => $file->mime_type,
        ]);
    }

    protected function authorizeFile(MediaFile $file, User $user): void
    {
        if ($user->isClient() && $file->folder?->id !== $user->assigned_folder_id) {
            abort(403, 'You cannot access this file.');
        }
    }
}
