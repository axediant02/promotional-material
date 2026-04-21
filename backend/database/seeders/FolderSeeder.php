<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Database\Seeder;

class FolderSeeder extends Seeder
{
    public function run(): void
    {
        $production = User::where('email', 'production@example.com')->first();
        $client1 = User::where('email', 'client1@example.com')->first();
        $client2 = User::where('email', 'client2@example.com')->first();

        if (!$production || !$client1 || !$client2) {
            return;
        }

        // Client 1 folder
        $client1Folder = Folder::query()->firstOrCreate(
            ['folder_name' => 'Client One Files', 'client_id' => $client1->user_id],
            [
                'folder_name' => 'Client One Files',
                'client_id' => $client1->user_id,
                'created_by' => $production->user_id,
            ]
        );

        $client1->forceFill([
            'assigned_folder_id' => $client1Folder->folder_id,
        ])->save();

        $client2Folder = Folder::query()->firstOrCreate(
            ['folder_name' => 'Client Two Files', 'client_id' => $client2->user_id],
            [
                'folder_name' => 'Client Two Files',
                'client_id' => $client2->user_id,
                'created_by' => $production->user_id,
            ]
        );

        $client2->forceFill([
            'assigned_folder_id' => $client2Folder->folder_id,
        ])->save();
    }
}
