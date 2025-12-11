<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'document';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uploader_id',
        'class_id',
        'original_filename',
        'storage_path',
        'mime_type',
        'file_size_bytes',
        'is_active',
        'content',
    ];

    /**
     * Get the user who uploaded this document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    /**
     * Get the class this document belongs to.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(Classroom::class, 'class_id');
    }
}
