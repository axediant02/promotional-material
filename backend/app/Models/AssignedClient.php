<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssignedClient extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'assigned_clients';

    protected $keyType = 'string';

    public $incrementing = false;

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';

    protected $fillable = [
        'production_id',
        'client_id',
        'status',
    ];

    public function production(): BelongsTo
    {
        return $this->belongsTo(User::class, 'production_id', 'user_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id', 'user_id');
    }

    public function chatThreads(): HasMany
    {
        return $this->hasMany(AssignmentChatThread::class, 'assignment_id', 'id');
    }
}
