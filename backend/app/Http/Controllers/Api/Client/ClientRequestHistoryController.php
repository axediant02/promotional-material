<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use Illuminate\Http\JsonResponse;

class ClientRequestHistoryController extends Controller
{
    public function index(): JsonResponse
    {
        $user = request()->user();

        abort_unless($user?->isClient(), 403);

        $requests = ClientRequest::query()
            ->where('client_id', $user->user_id)
            ->latest('created_at')
            ->get();

        return response()->json([
            'message' => 'Requests fetched.',
            'data' => [
                'requests' => $requests,
            ],
        ]);
    }
}
