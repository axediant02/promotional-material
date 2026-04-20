<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientRequest extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'client_requests';

    protected $primaryKey = 'request_id';

    protected $keyType = 'string';

    public $incrementing = false;

    public const TYPE_NEW_ASSET = 'new_asset';
    public const TYPE_UPDATE_ASSET = 'update_asset';

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    protected $fillable = [
        'client_id',
        'folder_id',
        'title',
        'description',
        'request_type',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id', 'user_id');
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'folder_id', 'folder_id');
    }
}
