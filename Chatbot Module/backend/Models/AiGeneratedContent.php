<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGeneratedContent extends Model
{
    use HasFactory;

    protected $table = 'ai_generated_content';

    protected $fillable = [
        'teacher_id',
        'class_id',
        'source_document_ids',
        'content_type',
        'content',
        'title',
        'status',
        'error_message',
        'is_shared',
    ];

    protected $casts = [
        'source_document_ids' => 'array',
        'content' => 'array',
        'is_shared' => 'boolean',
    ];

    /**
     * Get the teacher who created this content.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the class this content belongs to.
     */
    public function class(): BelongsTo
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    /**
     * Get source documents used for generation.
     */
    public function sourceDocuments()
    {
        if (empty($this->source_document_ids)) {
            return collect([]);
        }
        return Document::whereIn('id', $this->source_document_ids)->get();
    }
}
