<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['lesson_id', 'class', 'due_date', 'notes'];
    
    protected $casts = [
        'due_date' => 'date',
    ];
    
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}

