<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityAssignment extends Model
{
    use HasFactory;

    protected $table = 'activity_assignment';

    protected $fillable = [
        'activity_id',
        'classroom_id',
        'assigned_at',
        'status',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'assigned_at' => 'datetime',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ActivitySubmission::class);
    }
}
