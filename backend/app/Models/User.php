<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    public const ROLE_CLIENT = 'client';
    public const ROLE_AGENT = 'agent';
    public const ROLE_PRODUCTION = 'production';
    public const ROLE_ADMIN = 'admin';

    protected $primaryKey = 'user_id';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'assigned_folder_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function assignedFolder(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'assigned_folder_id', 'folder_id');
    }

    public function folder(): HasOne
    {
        return $this->hasOne(Folder::class, 'client_id', 'user_id');
    }

    public function createdFolders(): HasMany
    {
        return $this->hasMany(Folder::class, 'created_by', 'user_id');
    }

    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(MediaFile::class, 'uploaded_by', 'user_id');
    }

    public function clientRequests(): HasMany
    {
        return $this->hasMany(ClientRequest::class, 'client_id', 'user_id');
    }

    public function productionAssignments(): HasMany
    {
        return $this->hasMany(AssignedClient::class, 'production_id', 'user_id');
    }

    public function assignedProduction(): HasOne
    {
        return $this->hasOne(AssignedClient::class, 'client_id', 'user_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id', 'user_id');
    }

    public function isProduction(): bool
    {
        return $this->role === self::ROLE_PRODUCTION;
    }

    public function isAgent(): bool
    {
        return $this->role === self::ROLE_AGENT;
    }

    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    protected function displayRole(): Attribute
    {
        return Attribute::get(fn (): string => ucfirst($this->role));
    }
}
