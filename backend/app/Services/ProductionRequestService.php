<?php

namespace App\Services;

use App\Models\AssignedClient;
use App\Models\ClientRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class ProductionRequestService
{
    public function accessibleRequestsQuery(User $user): Builder
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
            ->with([
                'client:user_id,name,email',
                'folder:folder_id,folder_name,client_id',
            ])
            ->whereIn('client_id', function ($query) use ($user): void {
                $query->select('client_id')
                    ->from((new AssignedClient())->getTable())
                    ->where('production_id', $user->user_id);
            });
    }
}
