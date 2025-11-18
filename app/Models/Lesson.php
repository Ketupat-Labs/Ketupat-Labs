<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    use HasFactory;

    // The 'lessons' table is explicitly defined in your SQL dump.
    
    // Mass assignable fields, matching the columns in your 'lessons' table.
    protected $fillable = [
        'title',
        'topic',
        'teacher_id',
        'duration',
        'material_path', // For the uploaded PDF/file
        'is_published',  // Set default to FALSE in migration/schema
    ];

    // Define the relationship to the User who created the lesson (teacher_id)
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}