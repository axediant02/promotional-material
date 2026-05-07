<?php

namespace App\Policies;

use App\Models\AssignedClient;
use App\Models\ClientRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClientRequestPolicy
{
    public function create(User $user): Response
    {
        return $user->isClient()
            ? Response::allow()
            : Response::deny();
    }

    public function viewHistory(User $user): Response
    {
        return $user->isClient()
            ? Response::allow()
            : Response::deny();
    }

    public function viewAnyProduction(User $user): Response
    {
        return $user->isProduction()
            ? Response::allow()
            : Response::deny();
    }

    public function updateProduction(User $user, ClientRequest $clientRequest): Response
    {
        if (! $user->isProduction()) {
            return Response::deny();
        }

        $isAssigned = AssignedClient::query()
            ->where('production_id', $user->user_id)
            ->where('client_id', $clientRequest->client_id)
            ->exists();

        return $isAssigned
            ? Response::allow()
            : Response::deny();
    }
}
