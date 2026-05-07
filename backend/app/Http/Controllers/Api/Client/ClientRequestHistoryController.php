<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientRequest;
use App\Services\ClientRequestService;
use Illuminate\Http\JsonResponse;

class ClientRequestHistoryController extends Controller
{
    public function __construct(private readonly ClientRequestService $clientRequestService)
    {
    }

    public function index(): JsonResponse
    {
        $user = request()->user();
        $this->authorize('viewHistory', ClientRequest::class);

        $requests = $this->clientRequestService->requestsQuery($user)->get();

        return response()->json([
            'message' => 'Requests fetched.',
            'data' => [
                'requests' => $requests,
            ],
        ]);
    }
}
