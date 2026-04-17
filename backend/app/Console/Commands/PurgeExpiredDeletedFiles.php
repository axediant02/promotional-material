<?php

namespace App\Console\Commands;

use App\Models\MediaFile;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PurgeExpiredDeletedFiles extends Command
{
    protected $signature = 'app:purge-expired-deleted-files';

    protected $description = 'Permanently removes files that have been in the recycle bin for more than 30 days.';

    public function handle(): int
    {
        $expiredFiles = MediaFile::onlyTrashed()
            ->where('last_deleted_at', '<=', now()->subDays(30))
            ->get();

        foreach ($expiredFiles as $file) {
            Storage::disk($file->storage_disk)->delete($file->storage_path);
            $file->forceDelete();
        }

        $this->info('Purged '.$expiredFiles->count().' expired files.');

        return self::SUCCESS;
    }
}
