<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BadgeCategory extends Model
{
    use HasFactory;

    protected $table = 'badge_categories';

    protected $fillable = [
        'name',
        'code', // Changed from slug to code to match database
        'description',
        'icon',
        'color',
    ];

    public function badges(): HasMany
    {
        return $this->hasMany(Badge::class, 'category_id');
    }
}

