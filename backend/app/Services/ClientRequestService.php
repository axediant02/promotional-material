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
            ->select([
                'request_id',
                'client_id',
                'folder_id',
                'title',
                'description',
                'request_type',
                'status',
                'due_date',
                'created_at',
                'updated_at',
            ])
            ->where('client_id', $user->user_id)
            ->latest('created_at');
    }
}
