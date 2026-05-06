<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AdminUserService
{
    public function usersQuery(): Builder
    {
        return User::query()
            ->select([
                'user_id',
                'name',
                'email',
                'role',
                'status',
                'assigned_folder_id',
                'created_at',
                'updated_at',
            ])
            ->orderBy('name')
            ->orderBy('email');
    }
}
