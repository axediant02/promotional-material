<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

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
        'status',
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
        return $this->belongsTo(Folder::class, 'assigned_folder_id');
    }

    public function createdFolders(): HasMany
    {
        return $this->hasMany(Folder::class, 'created_by');
    }

    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(MediaFile::class, 'uploaded_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    #[Scope]
    protected function pending(Builder $query): void
    {
        $query->where('status', self::STATUS_PENDING);
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

    protected function displayRole(): Attribute
    {
        return Attribute::get(fn (): string => ucfirst($this->role));
    }
}
