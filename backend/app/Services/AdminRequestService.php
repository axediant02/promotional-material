<?php

namespace App\Services;

use App\Models\ClientRequest;
use Illuminate\Database\Eloquent\Builder;

class AdminRequestService
{
    public function requestsQuery(): Builder
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
            ->latest('created_at');
    }
}
