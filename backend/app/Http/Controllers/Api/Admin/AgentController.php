<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAgentRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AgentController extends Controller
{
    public function store(StoreAgentRequest $request): JsonResponse
    {
        $agent = User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
            'role' => User::ROLE_AGENT,
        ]);

        return response()->json([
            'message' => 'Agent account created.',
            'data' => ['agent' => $agent],
        ], 201);
    }
}
