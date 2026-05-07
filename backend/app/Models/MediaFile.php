<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MediaFile extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'files';

    protected $primaryKey = 'file_id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'folder_id',
        'uploaded_by',
        'file_name',
        'storage_disk',
        'storage_path',
        'category',
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
        return $this->belongsTo(Folder::class, 'folder_id', 'folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'user_id');
    }

    public function scopeAccessibleTo(Builder $query, User $user, bool $onlyTrashed = false, bool $withTrashed = false): Builder
    {
        $query = $query
            ->when($onlyTrashed, fn (Builder $builder) => $builder->onlyTrashed())
            ->when($withTrashed && ! $onlyTrashed, fn (Builder $builder) => $builder->withTrashed());

        if ($user->isAdmin() || $user->isAgent()) {
            return $query;
        }

        return $query->whereHas('folder', fn (Builder $folderQuery) => $folderQuery->accessibleTo($user));
    }

    protected function originalName(): Attribute
    {
        return Attribute::get(fn (): string => $this->file_name);
    }

    protected function mimeType(): Attribute
    {
        return Attribute::get(fn (): ?string => match ($this->category) {
            'image' => 'image/*',
            'video' => 'video/*',
            'pdf' => 'application/pdf',
            default => null,
        });
    }
}
