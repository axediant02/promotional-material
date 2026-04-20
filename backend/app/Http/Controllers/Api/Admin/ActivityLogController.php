<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    public function index(): JsonResponse
    {
        $logs = ActivityLog::query()
            ->with('user:user_id,name,email,role')
            ->latest()
            ->limit(100)
            ->get();

        return response()->json([
            'message' => 'Activity logs fetched.',
            'data' => ['logs' => $logs],
        ]);
    }
}
