<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivitySubmission extends Model
{
    use HasFactory;

    protected $table = 'activity_submission';

    protected $fillable = [
        'activity_assignment_id',
        'activity_id',
        'user_id',
        'score',
        'results',
        'feedback',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'results' => 'array',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ActivityAssignment::class, 'activity_assignment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
