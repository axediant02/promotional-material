<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\File\StoreFileRequest;
use App\Http\Requests\File\UpdateFileRequest;
use App\Models\Folder;
use App\Models\MediaFile;
use App\Services\FileService;
use App\Services\FolderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class FileController extends Controller
{
    public function __construct(
        private readonly FileService $fileService,
        private readonly FolderService $folderService,
    ) {
    }

    public function index(): JsonResponse
    {
        $user = request()->user();
        $this->authorize('viewAny', MediaFile::class);

        $files = $this->fileService->accessibleFilesQuery($user)
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
        $user = $request->user();
        $this->authorize('create', MediaFile::class);

        $folder = Folder::query()->findOrFail($request->input('folder_id'));
        $this->folderService->authorizeFolderAccess($folder, $user);
        $file = $this->fileService->store($user, $folder, $request->file('file'));

        return response()->json([
            'message' => 'File uploaded.',
            'data' => ['file' => $file->load('folder', 'uploader')],
        ], 201);
    }

    public function show(MediaFile $file): JsonResponse
    {
        $this->authorize('view', $file);

        return response()->json([
            'message' => 'File fetched.',
            'data' => ['file' => $file->load('folder', 'uploader')],
        ]);
    }

    public function update(UpdateFileRequest $request, MediaFile $file): JsonResponse
    {
        $user = $request->user();
        $this->authorize('update', $file);

        $validated = $request->validated();
        if (array_key_exists('folder_id', $validated)) {
            $targetFolder = Folder::query()->findOrFail($validated['folder_id']);
            $this->folderService->authorizeFolderAccess($targetFolder, $user);
        }

        $file = $this->fileService->update($user, $file, $validated, $request->file('file'));

        return response()->json([
            'message' => 'File updated.',
            'data' => ['file' => $file->load('folder', 'uploader')],
        ]);
    }

    public function destroy(MediaFile $file): JsonResponse
    {
        $user = request()->user();
        $this->authorize('delete', $file);
        $this->fileService->delete($user, $file);

        return response()->json([
            'message' => 'File moved to recycle bin.',
            'data' => null,
        ]);
    }

    public function download(string $id)
    {
        $file = MediaFile::withTrashed()->findOrFail($id);
        $this->authorize('download', $file);

        return $this->fileService->download($file);
    }

    public function preview(string $id)
    {
        $file = MediaFile::withTrashed()->findOrFail($id);
        $this->authorize('preview', $file);

        return $this->fileService->preview($file);
    }
}
