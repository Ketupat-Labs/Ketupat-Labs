<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Forum extends Model
{
    protected $table = 'forum';

    protected $fillable = [
        'created_by',
        'title',
        'description',
        'category',
        'visibility',
        'class_id',
        'start_date',
        'end_date',
        'member_count',
        'post_count',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'member_count' => 'integer',
        'post_count' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'forum_member', 'forum_id', 'user_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(ForumPost::class, 'forum_id');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(ForumTag::class, 'forum_id');
    }
}

