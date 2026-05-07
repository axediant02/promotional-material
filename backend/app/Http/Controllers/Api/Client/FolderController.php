<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Folder\StoreFolderRequest;
use App\Http\Requests\Folder\UpdateFolderRequest;
use App\Models\Folder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use App\Services\FolderService;

class FolderController extends Controller
{
    public function __construct(private readonly FolderService $folderService)
    {
    }

    public function index(): JsonResponse
    {
        $user = request()->user();
        $this->authorize('viewAny', Folder::class);

        $folders = $this->folderService->accessibleFoldersQuery($user)
            ->when(request('q'), fn (Builder $query, string $search) => $query->where('folder_name', 'like', "%{$search}%"))
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Folders fetched.',
            'data' => ['folders' => $folders],
        ]);
    }

    public function store(StoreFolderRequest $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('create', Folder::class);

        $folder = Folder::create([
            'folder_name' => $request->string('folder_name')->toString(),
            'client_id' => $request->string('client_id')->toString(),
            'created_by' => $user->user_id,
        ]);

        return response()->json([
            'message' => 'Folder created.',
            'data' => ['folder' => $folder->load('client:user_id,name,email')],
        ], 201);
    }

    public function show(Folder $folder): JsonResponse
    {
        $this->authorize('view', $folder);

        return response()->json([
            'message' => 'Folder fetched.',
            'data' => [
                'folder' => $folder->load('files', 'clientRequests'),
            ],
        ]);
    }

    public function update(UpdateFolderRequest $request, Folder $folder): JsonResponse
    {
        $this->authorize('update', $folder);

        $folder->fill($request->validated());
        $folder->save();

        return response()->json([
            'message' => 'Folder updated.',
            'data' => ['folder' => $folder],
        ]);
    }
}
