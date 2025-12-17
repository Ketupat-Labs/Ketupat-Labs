<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $primaryKey = 'student_id';
    
    protected $fillable = ['name', 'class'];
    
    public function answers()
    {
        return $this->hasMany(StudentAnswer::class, 'student_id');
    }
}

