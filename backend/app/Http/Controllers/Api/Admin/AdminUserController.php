<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminUserController extends Controller
{
    public function update(UpdateUserRoleRequest $request, User $user): JsonResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

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
