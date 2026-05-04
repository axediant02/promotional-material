<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ActivityLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min($request->integer('per_page', 15), 100));

        $logs = ActivityLog::query()
            ->with('user:user_id,name,email,role')
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'message' => 'Activity logs fetched.',
            'data' => ['logs' => $logs->items()],
            'pagination' => $this->paginationMeta($logs),
        ]);
    }

    /**
     * @return array<string, int|null|string>
     */
    private function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'path' => $paginator->path(),
        ];
    }
}
