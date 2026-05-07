<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssignmentChatThread extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_ARCHIVED = 'archived';

    protected $primaryKey = 'thread_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'assignment_id',
        'client_id',
        'production_id',
        'status',
        'started_at',
        'closed_at',
        'last_message_at',
        'last_message_by',
        'client_last_read_at',
        'production_last_read_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'closed_at' => 'datetime',
            'last_message_at' => 'datetime',
            'client_last_read_at' => 'datetime',
            'production_last_read_at' => 'datetime',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AssignedClient::class, 'assignment_id', 'id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id', 'user_id');
    }

    public function production(): BelongsTo
    {
        return $this->belongsTo(User::class, 'production_id', 'user_id');
    }

    public function lastMessageSender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_message_by', 'user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AssignmentChatMessage::class, 'thread_id', 'thread_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(AssignmentChatMessage::class, 'thread_id', 'thread_id')->latestOfMany('created_at');
    }
}
