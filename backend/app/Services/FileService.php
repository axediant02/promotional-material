<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function accessibleFilesQuery(User $user, bool $onlyTrashed = false, bool $withTrashed = false): Builder
    {
        return MediaFile::query()
            ->accessibleTo($user, $onlyTrashed, $withTrashed)
            ->with('folder:folder_id,folder_name,client_id', 'uploader:user_id,name,email');
    }

    public function store(User $user, Folder $folder, UploadedFile $uploadedFile): MediaFile
    {
        $disk = config('filesystems.default', 'local');
        $path = $uploadedFile->storeAs(
            trim('clients/'.$folder->folder_id, '/'),
            Str::uuid().'-'.$uploadedFile->getClientOriginalName(),
            $disk
        );

        $file = MediaFile::create([
            'folder_id' => $folder->folder_id,
            'uploaded_by' => $user->user_id,
            'file_name' => $uploadedFile->getClientOriginalName(),
            'storage_disk' => $disk,
            'storage_path' => $path,
            'category' => $this->resolveCategory($uploadedFile->getMimeType()),
        ]);

        $this->activityLogService->log(
            $user,
            'file_uploaded',
            $file,
            'Uploaded '.$file->file_name,
        );

        return $file;
    }

    /**
     * @param array<string, mixed> $validated
     */
    public function update(User $user, MediaFile $file, array $validated, ?UploadedFile $replacementFile = null): MediaFile
    {
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
                'uploaded_by' => $user->user_id,
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
            Storage::disk($previousStorageDisk ?: config('filesystems.default', 'local'))->delete($previousStoragePath);
        }

        $activityAction = $replacementFile ? 'file_replaced' : 'file_updated';
        $activityDescription = $replacementFile
            ? 'Replaced '.$file->file_name
            : 'Updated '.$file->file_name;

        $this->activityLogService->log(
            $user,
            $activityAction,
            $file,
            $activityDescription,
        );

        return $file;
    }

    public function delete(User $user, MediaFile $file): void
    {
        $file->last_deleted_at = now();
        $file->save();
        $file->delete();

        $this->activityLogService->log(
            $user,
            'file_deleted',
            $file,
            'Deleted '.$file->file_name,
        );
    }

    public function download(MediaFile $file)
    {
        $disk = Storage::disk($file->storage_disk);
        abort_unless($disk->exists($file->storage_path), 404, 'The stored file is missing.');

        return $disk->download($file->storage_path, $file->file_name);
    }

    public function preview(MediaFile $file)
    {
        $disk = Storage::disk($file->storage_disk);
        abort_unless($disk->exists($file->storage_path), 404, 'The stored file is missing.');

        return $disk->response($file->storage_path, $file->file_name, [
            'Content-Type' => $this->resolvePreviewMimeType($file->category),
        ]);
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
