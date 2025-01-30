<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileVersionComment extends Model
{
    /** @use HasFactory<\Database\Factories\FileVersionCommentFactory> */
    use HasFactory;

    protected $fillable = [
        'file_version_id',
        'comment'
    ];

    protected $casts = [
        'file_version_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function fileVersion()
    {
        return $this->belongsTo(FileVersion::class, 'file_version_id');
    }
}
