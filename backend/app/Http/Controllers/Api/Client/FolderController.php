<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Folder\StoreFolderRequest;
use App\Http\Requests\Folder\UpdateFolderRequest;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class FolderController extends Controller
{
    public static function accessibleFoldersQuery(User $user): Builder
    {
        $query = Folder::query()->with('client:id,name,email', 'children:id,parent_id,name');

        if ($user->isClient()) {
            return $query->where('id', $user->assigned_folder_id);
        }

        return $query;
    }

    public function index(): JsonResponse
    {
        $folders = self::accessibleFoldersQuery(request()->user())
            ->when(request('q'), fn (Builder $query, string $search) => $query->where('name', 'like', "%{$search}%"))
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
            'name' => $request->string('name')->toString(),
            'slug' => Str::slug($request->string('name')->toString().'-'.Str::lower(Str::random(5))),
            'parent_id' => $request->input('parent_id'),
            'client_user_id' => $request->input('client_user_id'),
            'created_by' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Folder created.',
            'data' => ['folder' => $folder->load('client:id,name,email')],
        ], 201);
    }

    public function show(Folder $folder): JsonResponse
    {
        $this->authorizeFolder($folder, request()->user());

        return response()->json([
            'message' => 'Folder fetched.',
            'data' => [
                'folder' => $folder->load('files', 'children'),
            ],
        ]);
    }

    public function update(UpdateFolderRequest $request, Folder $folder): JsonResponse
    {
        abort_unless($request->user()->isProduction(), 403);

        $folder->fill($request->validated());
        if ($request->filled('name')) {
            $folder->slug = Str::slug($request->string('name')->toString().'-'.Str::lower(Str::random(5)));
        }
        $folder->save();

        return response()->json([
            'message' => 'Folder updated.',
            'data' => ['folder' => $folder],
        ]);
    }

    protected function authorizeFolder(Folder $folder, User $user): void
    {
        if ($user->isClient() && $folder->id !== $user->assigned_folder_id) {
            abort(403, 'You cannot access this folder.');
        }
    }
}
