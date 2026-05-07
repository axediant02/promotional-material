<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AdminUserService
{
    public function usersQuery(): Builder
    {
        return User::query()
            ->orderBy('name')
            ->orderBy('email');
    }
}
