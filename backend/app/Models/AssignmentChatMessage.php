<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentChatMessage extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'message_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'thread_id',
        'sender_user_id',
        'body',
    ];

    public function thread(): BelongsTo
    {
        return $this->belongsTo(AssignmentChatThread::class, 'thread_id', 'thread_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id', 'user_id');
    }
}
