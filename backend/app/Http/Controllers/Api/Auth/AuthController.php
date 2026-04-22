<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
            'role' => User::ROLE_CLIENT,
        ]);

        return response()->json([
            'message' => 'Registration completed. Your folder will be created when you submit your first request.',
            'data' => ['user' => $user->fresh()],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email'))->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 422);
        }

        if ($user->isAdmin()) {
            return response()->json([
                'message' => 'Admin accounts are not available in the live portal yet. Use a production, agent, or client account.',
            ], 403);
        }

        $token = $user->createToken('frontend')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'data' => compact('token', 'user'),
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json([
            'message' => 'Authenticated user fetched.',
            'data' => ['user' => request()->user()->load('assignedFolder')],
        ]);
    }

    public function logout(): JsonResponse
    {
        request()->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
            'data' => null,
        ]);
    }
}
