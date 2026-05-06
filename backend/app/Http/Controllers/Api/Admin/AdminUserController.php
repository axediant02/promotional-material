<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\User;
use App\Services\AdminUserService;
use Illuminate\Http\JsonResponse;

class AdminUserController extends Controller
{
    public function __construct(private readonly AdminUserService $adminUserService)
    {
    }

    public function index(): JsonResponse
    {
        $this->authorize('admin', User::class);

        $users = $this->adminUserService->usersQuery()->get();

        return response()->json([
            'message' => 'Users fetched.',
            'data' => [
                'users' => $users,
            ],
        ]);
    }

    public function update(UpdateUserRoleRequest $request, User $user): JsonResponse
    {
        $this->authorize('admin', User::class);

        $user->fill([
            'role' => $request->string('role')->toString(),
        ])->save();

        return response()->json([
            'message' => 'User role updated.',
            'data' => [
                'user' => $user->fresh(),
            ],
        ]);
    }
}
