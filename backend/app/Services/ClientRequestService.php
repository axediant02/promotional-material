<?php

namespace App\Services;

use App\Models\ClientRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ClientRequestService
{
    public function requestsQuery(User $user): Builder
    {
        return ClientRequest::query()
            ->where('client_id', $user->user_id)
            ->latest('created_at');
    }
}
