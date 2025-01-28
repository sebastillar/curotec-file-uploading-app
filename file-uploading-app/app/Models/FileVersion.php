<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileVersion extends Model
{
    /** @use HasFactory<\Database\Factories\FileVersionFactory> */
    use HasFactory;

    protected $fillable = [
        'file_id',
        'version_number',
        'path'
    ];

    protected $casts = [
        'file_id' => 'integer',
        'version_number' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function file()
    {
        return $this->belongsTo(File::class);
    }

    public function comments()
    {
        return $this->hasMany(FileVersionComment::class);
    }
}
