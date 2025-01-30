<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class FileCollaborator extends Model
{
    /** @use HasFactory<\Database\Factories\FileCollaboratorFactory> */
    use HasFactory;

    protected $fillable = [
        'file_id',
        'user_id',
        'role',
        'revoked_at'
    ];

    protected $casts = [
        'revoked_at' => 'datetime'
    ];

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('revoked_at');
    }

    public function isActive(): bool
    {
        return is_null($this->revoked_at);
    }
}
