<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MutedUser extends Model
{
    protected $table = 'muted_user';

    protected $fillable = [
        'forum_id',
        'user_id',
        'muted_by',
        'muted_until',
        'reason',
    ];

    protected $casts = [
        'muted_until' => 'datetime',
    ];

    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class, 'forum_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function mutedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'muted_by');
    }
}

