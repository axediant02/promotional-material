<?php

namespace App\Services;

use App\Models\ClientRequest;
use Illuminate\Database\Eloquent\Builder;

class AdminRequestService
{
    public function requestsQuery(): Builder
    {
        return ClientRequest::query()->latest('created_at');
    }
}
