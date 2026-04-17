<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaFile extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'files';

    protected $fillable = [
        'folder_id',
        'uploaded_by',
        'original_name',
        'storage_disk',
        'storage_path',
        'mime_type',
        'size',
        'last_deleted_at',
    ];

    protected function casts(): array
    {
        return [
            'last_deleted_at' => 'datetime',
        ];
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
