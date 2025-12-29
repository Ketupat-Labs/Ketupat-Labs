<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAchievement extends Model
{
    use HasFactory;

    protected $table = 'user_achievements';

    protected $fillable = [
        'user_id',
        'achievement_id',
        'progress',
        'unlocked',
        'unlocked_at'
    ];

    protected $casts = [
        'unlocked' => 'boolean',
        'unlocked_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function achievement()
    {
        return $this->belongsTo(Achievement::class);
    }
}