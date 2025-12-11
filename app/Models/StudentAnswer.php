<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['student_id', 'lesson_id', 'q1_answer', 'q2_answer', 'q3_answer', 'total_marks'];
    
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}

