<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Connects to the 'class' table.
 * Renamed to ClassModel to avoid conflicts with the PHP 'class' keyword.
 */
class ClassModel extends Model
{
    use HasFactory;

    /**
     * Set the table name explicitly.
     */
    protected $table = 'class';

    /**
     * Allow mass assignment for these fields.
     */
    protected $fillable = [
        'teacher_id',
        'name',
        'description',
    ];

    /**
     * Get the teacher (a user) who owns this class.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get all the documents uploaded to this class.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'class_id');
    }

    /**
     * Get all the students (users) enrolled in this class.
     */
    public function students(): BelongsToMany
    {
        // Links 'class' table to 'user' table via the 'class_user' pivot table
        return $this->belongsToMany(User::class, 'class_user', 'class_id', 'user_id');
    }
}
