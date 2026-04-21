<?php

namespace Database\Seeders;

use App\Models\ClientRequest;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ClientRequestSeeder extends Seeder
{
    public function run(): void
    {
        $client1 = User::where('email', 'client1@example.com')->first();
        $client2 = User::where('email', 'client2@example.com')->first();

        if (!$client1 || !$client2) {
            return;
        }

        $client1Folder = Folder::where('folder_name', 'Client One Files')->first();
        $client2Folder = Folder::where('folder_name', 'Client Two Files')->first();

        // Client 1 requests
        if ($client1Folder) {
            ClientRequest::query()->firstOrCreate(
                [
                    'client_id' => $client1->user_id,
                    'folder_id' => $client1Folder->folder_id,
                    'title' => 'New Event Photos',
                ],
                [
                    'client_id' => $client1->user_id,
                    'folder_id' => $client1Folder->folder_id,
                    'title' => 'New Event Photos',
                    'description' => 'Request for event coverage photos from last weekend shoot.',
                    'request_type' => ClientRequest::TYPE_NEW_ASSET,
                    'status' => ClientRequest::STATUS_PENDING,
                    'due_date' => Carbon::now()->addDays(7),
                ]
            );

            ClientRequest::query()->firstOrCreate(
                [
                    'client_id' => $client1->user_id,
                    'folder_id' => $client1Folder->folder_id,
                    'title' => 'Update Product Images',
                ],
                [
                    'client_id' => $client1->user_id,
                    'folder_id' => $client1Folder->folder_id,
                    'title' => 'Update Product Images',
                    'description' => 'Please update the product showcase images with new branding.',
                    'request_type' => ClientRequest::TYPE_UPDATE_ASSET,
                    'status' => ClientRequest::STATUS_IN_PROGRESS,
                    'due_date' => Carbon::now()->addDays(3),
                ]
            );

            ClientRequest::query()->firstOrCreate(
                [
                    'client_id' => $client1->user_id,
                    'folder_id' => $client1Folder->folder_id,
                    'title' => 'Corporate Video',
                ],
                [
                    'client_id' => $client1->user_id,
                    'folder_id' => $client1Folder->folder_id,
                    'title' => 'Corporate Video',
                    'description' => 'Corporate introduction video, 2-3 minutes duration.',
                    'request_type' => ClientRequest::TYPE_NEW_ASSET,
                    'status' => ClientRequest::STATUS_DONE,
                    'due_date' => Carbon::now()->subDays(5),
                ]
            );
        }

        // Client 2 requests
        if ($client2Folder) {
            ClientRequest::query()->firstOrCreate(
                [
                    'client_id' => $client2->user_id,
                    'folder_id' => $client2Folder->folder_id,
                    'title' => 'Social Media Pack',
                ],
                [
                    'client_id' => $client2->user_id,
                    'folder_id' => $client2Folder->folder_id,
                    'title' => 'Social Media Pack',
                    'description' => 'Collection of optimized images for Instagram, Facebook, and Twitter.',
                    'request_type' => ClientRequest::TYPE_NEW_ASSET,
                    'status' => ClientRequest::STATUS_PENDING,
                    'due_date' => Carbon::now()->addDays(14),
                ]
            );
        }
    }
}
