<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function taughtClassrooms()
    {
        return $this->hasMany(Classroom::class, 'teacher_id');
    }

    public function enrolledClassrooms()
    {
        return $this->belongsToMany(Classroom::class, 'class_students', 'student_id', 'classroom_id')
            ->withPivot('enrolled_at');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'role',
        'points',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
