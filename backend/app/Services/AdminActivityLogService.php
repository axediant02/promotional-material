<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;

class AdminActivityLogService
{
    public function activityLogsQuery(): Builder
    {
        return ActivityLog::query()
            ->with('user:user_id,name,email,role')
            ->latest();
    }
}
