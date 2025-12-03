<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = ['class', 'q1', 'q2', 'q3'];
    
    public function answers()
    {
        return $this->hasMany(StudentAnswer::class);
    }
    
    public function assignments()
    {
        return $this->hasMany(LessonAssignment::class);
    }
}
