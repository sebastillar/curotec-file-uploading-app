<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Builder;
class File extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\FileFactory> */
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'name',
        'extension',
        'mime_type',
        'size'
    ];

    protected $casts = [
        'size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

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
