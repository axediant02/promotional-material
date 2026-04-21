<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\MediaFile;
use App\Models\User;
use Illuminate\Database\Seeder;

class MediaFileSeeder extends Seeder
{
    public function run(): void
    {
        $production = User::where('email', 'production@example.com')->first();

        if (!$production) {
            return;
        }

        $client1Folder = Folder::where('folder_name', 'Client One Files')->first();
        $client2Folder = Folder::where('folder_name', 'Client Two Files')->first();

        // Sample files for Client 1 folder
        if ($client1Folder) {
            $this->createSampleFiles($client1Folder, $production);
        }

        // Sample files for Client 2 folder
        if ($client2Folder) {
            $this->createSampleFiles($client2Folder, $production);
        }
    }

    private function createSampleFiles(Folder $folder, User $uploader): void
    {
        $samples = [
            [
                'file_name' => 'hero-banner.jpg',
                'category' => 'image',
                'storage_disk' => 'public',
            ],
            [
                'file_name' => 'product-showcase.png',
                'category' => 'image',
                'storage_disk' => 'public',
            ],
            [
                'file_name' => 'company-profile.pdf',
                'category' => 'pdf',
                'storage_disk' => 'public',
            ],
            [
                'file_name' => 'promo-video.mp4',
                'category' => 'video',
                'storage_disk' => 'public',
            ],
        ];

        foreach ($samples as $sample) {
            $exists = MediaFile::where('folder_id', $folder->folder_id)
                ->where('file_name', $sample['file_name'])
                ->exists();

            if (!$exists) {
                MediaFile::create([
                    'folder_id' => $folder->folder_id,
                    'uploaded_by' => $uploader->user_id,
                    'file_name' => $sample['file_name'],
                    'storage_disk' => $sample['storage_disk'],
                    'storage_path' => $folder->folder_id . '/' . $sample['file_name'],
                    'category' => $sample['category'],
                    'last_deleted_at' => null,
                ]);
            }
        }
    }
}
