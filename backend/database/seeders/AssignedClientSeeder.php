<?php

namespace Database\Seeders;

use App\Models\AssignedClient;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignedClientSeeder extends Seeder
{
    public function run(): void
    {
        $production = User::where('email', 'production@example.com')->first();
        $client1 = User::where('email', 'client1@example.com')->first();
        $client2 = User::where('email', 'client2@example.com')->first();

        if (!$production || !$client1 || !$client2) {
            return;
        }

        // Assign Client 1 to Production
        AssignedClient::query()->firstOrCreate(
            [
                'production_id' => $production->user_id,
                'client_id' => $client1->user_id,
            ],
            [
                'production_id' => $production->user_id,
                'client_id' => $client1->user_id,
                'status' => AssignedClient::STATUS_IN_PROGRESS,
            ]
        );

        // Assign Client 2 to Production
        AssignedClient::query()->firstOrCreate(
            [
                'production_id' => $production->user_id,
                'client_id' => $client2->user_id,
            ],
            [
                'production_id' => $production->user_id,
                'client_id' => $client2->user_id,
                'status' => AssignedClient::STATUS_PENDING,
            ]
        );
    }
}
