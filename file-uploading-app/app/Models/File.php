<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class File extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\FileFactory> */
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'author_id',
        'name',
        'extension',
        'mime_type',
        'size'
    ];

    protected $casts = [
        'author_id' => 'integer',
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function collaborators()
    {
        return $this->hasMany(FileCollaborator::class);
    }

    public function activeCollaborators()
    {
        return $this->collaborators()->active();
    }

    public function isOwnedBy(User $user): bool
    {
        return $this->author_id === $user->id;
    }

    public function canBeEditedBy(User $user): bool
    {
        if ($this->isOwnedBy($user)) {
            return true;
        }

        return $this->activeCollaborators()
                    ->where('user_id', $user->id)
                    ->where('role', 'editor')
                    ->exists();
    }

    public function versions()
    {
        return $this->hasMany(FileVersion::class);
    }


    /**
     * Scope to get latest files with their versions
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeLatestWithVersions(Builder $query): Builder
    {
        return $query->with('versions')
        ->withCount('versions')
        ->latest();
    }
}
