<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityLogService
{
    public function log(User $user, string $action, Model $subject, string $description, array $metadata = []): ActivityLog
    {
        return ActivityLog::create([
            'user_id' => $user->user_id,
            'action' => $action,
            'subject_type' => $subject::class,
            'subject_id' => $subject->getKey(),
            'description' => $description,
            'metadata' => $metadata,
        ]);
    }
}
