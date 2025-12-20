<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'name',
        'description',
        'icon',
        'points',
        'condition_type',
        'condition_value'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
                    ->withPivot('progress', 'unlocked', 'unlocked_at')
                    ->withTimestamps();
    }
}