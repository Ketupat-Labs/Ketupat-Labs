<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    use HasFactory;

    protected $table = 'lesson';

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    protected $fillable = [
        'title',
        'topic',
        'content',
        'content_blocks', // Block-based editor content
        'teacher_id',
        'duration',
        'material_path',
        'url',
        'is_published',
        'is_public',
    ];

    protected $casts = [
        'content_blocks' => 'array', // Automatically cast JSON to array
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function assignments()
    {
        return $this->hasMany(LessonAssignment::class);
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'lesson_assignment', 'lesson_id', 'classroom_id')
            ->withPivot('type', 'assigned_at');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }
}

