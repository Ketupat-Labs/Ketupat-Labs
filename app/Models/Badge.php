<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    protected $table = 'badge';

    protected $fillable = [
        'code',
        'name',
        'description',
        'category_slug',
        'icon',
        'requirement_type',
        'requirement_value',
        'color',
        'category_id',
        'xp_reward',
    ];

    protected $casts = [
        'requirement_value' => 'integer',
        'xp_reward' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BadgeCategory::class, 'category_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badge', 'badge_code', 'user_id', 'code', 'id')
            ->withTimestamps();
    }
}

