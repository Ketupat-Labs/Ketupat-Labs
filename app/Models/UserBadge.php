<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    use HasFactory;

    protected $table = 'user_badge';

    protected $fillable = [
        'user_id',
        'badge_code',
        'status',
        'progress',
        'earned_at',
        'redeemed_at',
    ];

    protected $casts = [
        'progress' => 'integer',
        'earned_at' => 'datetime',
        'redeemed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class, 'badge_code', 'code');
    }
}
