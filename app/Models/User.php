<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Passwords\CanResetPassword;
use App\Notifications\ResetPasswordNotification;

class User extends Authenticatable
{
    use HasFactory, Notifiable, CanResetPassword;

    protected $table = 'user';

    protected $fillable = [
        'username',
        'email',
        'password',
        'full_name',
        'role',
        'avatar_url',
        'is_online',
        'last_seen',
        'points',
        'school',
        'class',
        'bio',
        'chatbot_enabled',
        'allow_friend_requests',
        'share_badges_on_profile',
        'visible_badge_codes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_online' => 'boolean',
        'last_seen' => 'datetime',
        'share_badges_on_profile' => 'boolean',
        'visible_badge_codes' => 'array',
    ];

    // Helper method to get UI role
    public function getUiRoleAttribute()
    {
        return $this->attributes['role'] === 'teacher' ? 'cikgu' : 'pelajar';
    }

    // Relationships from Ketupat-Labs
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
        return $this->belongsToMany(Classroom::class, 'class_student', 'student_id', 'classroom_id')
            ->withPivot('enrolled_at');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function quizAttempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'teacher_id');
    }

    // Forum relationships
    public function forumPosts()
    {
        return $this->hasMany(ForumPost::class, 'author_id');
    }

    // Friend relationships
    public function friends()
    {
        return $this->belongsToMany(User::class, 'friend', 'user_id', 'friend_id')
            ->wherePivot('status', 'accepted')
            ->withPivot('accepted_at')
            ->withTimestamps();
    }

    public function friendRequests()
    {
        return $this->hasMany(Friend::class, 'user_id')->where('status', 'pending');
    }

    public function sentFriendRequests()
    {
        return $this->hasMany(Friend::class, 'user_id');
    }

    public function receivedFriendRequests()
    {
        return $this->hasMany(Friend::class, 'friend_id')->where('status', 'pending');
    }

    public function isFriendWith($userId)
    {
        return $this->friends()->where('friend_id', $userId)->exists() ||
               Friend::where('user_id', $userId)
                   ->where('friend_id', $this->id)
                   ->where('status', 'accepted')
                   ->exists();
    }

    public function hasPendingRequestWith($userId)
    {
        return Friend::where(function ($q) use ($userId) {
            $q->where('user_id', $this->id)->where('friend_id', $userId);
        })->orWhere(function ($q) use ($userId) {
            $q->where('user_id', $userId)->where('friend_id', $this->id);
        })->where('status', 'pending')->exists();
    }

    // Badge relationships
    public function userBadges()
    {
        return $this->hasMany(UserBadge::class, 'user_id');
    }

    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badge', 'user_id', 'badge_code', 'id', 'code')
            ->withTimestamps();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification(#[\SensitiveParameter] $token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}

