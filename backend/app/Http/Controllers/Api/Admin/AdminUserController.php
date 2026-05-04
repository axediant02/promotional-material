<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminUserController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('admin', User::class);

        $users = User::query()
            ->orderBy('name')
            ->orderBy('email')
            ->get();

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

        $user->forceFill([
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
