<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $primaryKey = 'folder_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'folder_name',
        'client_id',
        'created_by',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id', 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(MediaFile::class, 'folder_id', 'folder_id');
    }

    public function clientRequests(): HasMany
    {
        return $this->hasMany(ClientRequest::class, 'folder_id', 'folder_id');
    }

    protected function name(): Attribute
    {
        return Attribute::get(fn (): string => $this->folder_name);
    }
}
