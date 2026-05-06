<?php

namespace App\Services;

use App\Models\AssignedClient;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AdminAssignmentService
{
    public function assignmentsQuery(): Builder
    {
        return AssignedClient::query()
            ->select([
                'id',
                'production_id',
                'client_id',
                'status',
                'created_at',
                'updated_at',
            ])
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function productionUsersQuery(): Builder
    {
        return User::query()
            ->where('role', User::ROLE_PRODUCTION)
            ->orderBy('name')
            ->orderBy('email')
            ->select(['user_id', 'name', 'email']);
    }
}
