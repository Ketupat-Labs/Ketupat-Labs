<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumTag extends Model
{
    protected $table = 'forum_tag';

    protected $fillable = [
        'forum_id',
        'tag_name',
    ];

    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class, 'forum_id');
    }
}

