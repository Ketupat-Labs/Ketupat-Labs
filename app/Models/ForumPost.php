<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumPost extends Model
{
    protected $table = 'forum_post';

    protected $fillable = [
        'forum_id',
        'author_id',
        'title',
        'content',
        'category',
        'post_type',
        'is_pinned',
        'view_count',
        'reply_count',
        'is_deleted',
        'deleted_at',
        'is_hidden',
        'hidden_at',
        'hidden_by',
        'lesson_id', // Added for lesson sharing
    ];

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }

    protected $casts = [
        'is_pinned' => 'boolean',
        'is_deleted' => 'boolean',
        'is_hidden' => 'boolean',
        'view_count' => 'integer',
        'reply_count' => 'integer',
        'deleted_at' => 'datetime',
        'hidden_at' => 'datetime',
    ];

    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class, 'forum_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(PostAttachment::class, 'post_id');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(PostTag::class, 'post_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class, 'target_id')
            ->where('target_type', 'post');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'reportable_id')
            ->where('reportable_type', 'post');
    }

    public function hiddenBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hidden_by');
    }
}

