<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\File\StoreFileRequest;
use App\Http\Requests\File\UpdateFileRequest;
use App\Models\AssignedClient;
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

    public static function accessibleFilesQuery(User $user, bool $onlyTrashed = false, bool $withTrashed = false): Builder
    {
        $query = MediaFile::query()
            ->when($onlyTrashed, fn (Builder $builder) => $builder->onlyTrashed())
            ->when($withTrashed && ! $onlyTrashed, fn (Builder $builder) => $builder->withTrashed())
            ->with('folder:folder_id,folder_name,client_id', 'uploader:user_id,name,email');

        if ($user->isClient()) {
            return $query->whereHas('folder', fn (Builder $folderQuery) => $folderQuery->where('folder_id', $user->assigned_folder_id));
        }

        if ($user->isProduction()) {
            return $query->whereHas('folder', fn (Builder $folderQuery) => $folderQuery->whereIn('client_id', AssignedClient::query()
                ->select('client_id')
                ->where('production_id', $user->user_id)));
        }

        return $query;
    }

    public function index(): JsonResponse
    {
        $files = self::accessibleFilesQuery(request()->user())
            ->when(request('folder_id'), fn (Builder $query, string $folderId) => $query->where('folder_id', $folderId))
            ->when(request('q'), fn (Builder $query, string $search) => $query->where('file_name', 'like', "%{$search}%"))
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
        abort_unless(FolderController::accessibleFoldersQuery($request->user())
            ->whereKey($folder->getKey())
            ->exists(), 403, 'You cannot access this folder.');
        $uploadedFile = $request->file('file');
        $disk = config('filesystems.default', 'local');
        $path = $uploadedFile->storeAs(
            trim('clients/'.$folder->folder_id, '/'),
            Str::uuid().'-'.$uploadedFile->getClientOriginalName(),
            $disk
        );

        $file = MediaFile::create([
            'folder_id' => $folder->folder_id,
            'uploaded_by' => $request->user()->user_id,
            'file_name' => $uploadedFile->getClientOriginalName(),
            'storage_disk' => $disk,
            'storage_path' => $path,
            'category' => $this->resolveCategory($uploadedFile->getMimeType()),
        ]);

        $this->activityLogService->log(
            $request->user(),
            'file_uploaded',
            $file,
            'Uploaded '.$file->file_name,
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
        $this->authorizeFile($file, $request->user());

        $validated = $request->validated();
        if (array_key_exists('folder_id', $validated)) {
            $targetFolder = Folder::query()->findOrFail($validated['folder_id']);
            abort_unless(FolderController::accessibleFoldersQuery($request->user())
                ->whereKey($targetFolder->getKey())
                ->exists(), 403, 'You cannot access this folder.');
        }

        $replacementFile = $request->file('file');
        $previousStorageDisk = $file->storage_disk;
        $previousStoragePath = $file->storage_path;

        if ($replacementFile) {
            $targetFolderId = $validated['folder_id'] ?? $file->folder_id;
            $storageDisk = $previousStorageDisk ?: config('filesystems.default', 'local');
            $storagePath = $replacementFile->storeAs(
                trim('clients/'.$targetFolderId, '/'),
                Str::uuid().'-'.$replacementFile->getClientOriginalName(),
                $storageDisk
            );

            $file->forceFill([
                'folder_id' => $targetFolderId,
                'uploaded_by' => $request->user()->user_id,
                'file_name' => $replacementFile->getClientOriginalName(),
                'storage_disk' => $storageDisk,
                'storage_path' => $storagePath,
                'category' => $this->resolveCategory($replacementFile->getMimeType()),
            ]);
        } else {
            $file->fill($validated);
        }

        $file->save();

        if ($replacementFile && $previousStoragePath !== $file->storage_path) {
            Storage::disk($previousStorageDisk)->delete($previousStoragePath);
        }

        $activityAction = $replacementFile ? 'file_replaced' : 'file_updated';
        $activityDescription = $replacementFile
            ? 'Replaced '.$file->file_name
            : 'Updated '.$file->file_name;

        $this->activityLogService->log(
            $request->user(),
            $activityAction,
            $file,
            $activityDescription,
        );

        return response()->json([
            'message' => 'File updated.',
            'data' => ['file' => $file->load('folder', 'uploader')],
        ]);
    }

    public function destroy(MediaFile $file): JsonResponse
    {
        abort_unless(request()->user()->isProduction(), 403);
        $this->authorizeFile($file, request()->user());

        $file->last_deleted_at = now();
        $file->save();
        $file->delete();

        $this->activityLogService->log(
            request()->user(),
            'file_deleted',
            $file,
            'Deleted '.$file->file_name,
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
        $disk = Storage::disk($file->storage_disk);

        abort_unless($disk->exists($file->storage_path), 404, 'The stored file is missing.');

        return $disk->download($file->storage_path, $file->file_name);
    }

    public function preview(string $id)
    {
        $file = MediaFile::withTrashed()->findOrFail($id);
        $this->authorizeFile($file, request()->user());
        $disk = Storage::disk($file->storage_disk);

        abort_unless($disk->exists($file->storage_path), 404, 'The stored file is missing.');

        return $disk->response($file->storage_path, $file->file_name, [
            'Content-Type' => $this->resolvePreviewMimeType($file->category),
        ]);
    }

    protected function authorizeFile(MediaFile $file, User $user): void
    {
        if ($user->isClient() && $file->folder?->folder_id !== $user->assigned_folder_id) {
            abort(403, 'You cannot access this file.');
        }

        if ($user->isProduction() && ! self::accessibleFilesQuery($user, withTrashed: true)
            ->whereKey($file->getKey())
            ->exists()) {
            abort(403, 'You cannot access this file.');
        }
    }

    protected function resolveCategory(?string $mimeType): string
    {
        if (is_string($mimeType) && str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (is_string($mimeType) && str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        return 'pdf';
    }

    protected function resolvePreviewMimeType(string $category): string
    {
        return match ($category) {
            'image' => 'image/*',
            'video' => 'video/*',
            default => 'application/pdf',
        };
    }
}
