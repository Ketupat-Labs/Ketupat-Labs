<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['classroom_id', 'lesson_id', 'type', 'assigned_at', 'due_date', 'notes'];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}

