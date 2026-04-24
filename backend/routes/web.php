<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'Promotional Materials Backend',
    'status' => 'ok',
]));

Route::post('/broadcasting/auth', function (Request $request) {
    $user = $request->user();
    $channelName = (string) $request->string('channel_name');

    abort_unless($user, 401);

    if (preg_match('/^private-(?:App\\.Models\\.User|users)\\.(?<id>[^\\.]+)(?:\\.notifications)?$/', $channelName, $matches)) {
        abort_unless($user->user_id === $matches['id'], 403);

        return response()->json([
            'auth' => true,
        ]);
    }

    abort(403);
})->middleware('auth:sanctum');
