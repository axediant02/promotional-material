<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Folder\StoreFolderRequest;
use App\Http\Requests\Folder\UpdateFolderRequest;
use App\Models\AssignedClient;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class FolderController extends Controller
{
    public static function accessibleFoldersQuery(User $user): Builder
    {
        $query = Folder::query()->with('client:user_id,name,email');

        if ($user->isClient()) {
            return $query->where('folder_id', $user->assigned_folder_id);
        }

        if ($user->isProduction()) {
            return $query->whereIn('client_id', AssignedClient::query()
                ->select('client_id')
                ->where('production_id', $user->user_id));
        }

        return $query;
    }

    public function index(): JsonResponse
    {
        $folders = self::accessibleFoldersQuery(request()->user())
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
        abort_unless($request->user()->isProduction(), 403);

        $folder = Folder::create([
            'folder_name' => $request->string('folder_name')->toString(),
            'client_id' => $request->string('client_id')->toString(),
            'created_by' => $request->user()->user_id,
        ]);

        return response()->json([
            'message' => 'Folder created.',
            'data' => ['folder' => $folder->load('client:user_id,name,email')],
        ], 201);
    }

    public function show(Folder $folder): JsonResponse
    {
        $this->authorizeFolder($folder, request()->user());

        return response()->json([
            'message' => 'Folder fetched.',
            'data' => [
                'folder' => $folder->load('files', 'clientRequests'),
            ],
        ]);
    }

    public function update(UpdateFolderRequest $request, Folder $folder): JsonResponse
    {
        abort_unless($request->user()->isProduction(), 403);

        $folder->fill($request->validated());
        $folder->save();

        return response()->json([
            'message' => 'Folder updated.',
            'data' => ['folder' => $folder],
        ]);
    }

    protected function authorizeFolder(Folder $folder, User $user): void
    {
        if ($user->isClient() && $folder->folder_id !== $user->assigned_folder_id) {
            abort(403, 'You cannot access this folder.');
        }

        if ($user->isProduction() && ! self::accessibleFoldersQuery($user)
            ->whereKey($folder->getKey())
            ->exists()) {
            abort(403, 'You cannot access this folder.');
        }
    }
}
