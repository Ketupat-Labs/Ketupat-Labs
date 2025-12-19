<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    use HasFactory;

    protected $table = 'class';

    protected $fillable = [
        'teacher_id',
        'name',
        'subject',
        'year',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Delete associated forum when classroom is deleted
        static::deleting(function ($classroom) {
            $classroom->forum()->delete();
            
            // Delete associated group chat when classroom is deleted
            $groupChat = $classroom->getClassroomGroupChat();
            if ($groupChat) {
                // Permanently delete all messages first
                \Illuminate\Support\Facades\DB::table('message')
                    ->where('conversation_id', $groupChat->id)
                    ->delete();
                
                // Remove all participants
                $groupChat->participants()->detach();
                
                // Delete the conversation
                $groupChat->delete();
            }
        });

        // Create group chat when classroom is created
        static::created(function ($classroom) {
            // Generate group chat name: class name + subject + year
            $groupChatName = $classroom->name;
            if ($classroom->subject) {
                $groupChatName .= ' ' . $classroom->subject;
            }
            if ($classroom->year) {
                $groupChatName .= ' ' . $classroom->year;
            }

            // Create group chat
            $conversation = \App\Models\Conversation::create([
                'type' => 'group',
                'name' => $groupChatName,
                'created_by' => $classroom->teacher_id,
            ]);

            // Add teacher as participant
            $conversation->participants()->attach($classroom->teacher_id);

            // Add all existing students as participants (if any)
            $students = $classroom->students;
            foreach ($students as $student) {
                if (!$conversation->participants()->where('user_id', $student->id)->exists()) {
                    $conversation->participants()->attach($student->id);
                }
            }
        });
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'class_student', 'classroom_id', 'student_id')
            ->withPivot('enrolled_at');
    }

    public function lessons(): BelongsToMany
    {
        return $this->belongsToMany(Lesson::class, 'lesson_assignment', 'classroom_id', 'lesson_id')
            ->withPivot('type', 'assigned_at');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(LessonAssignment::class);
    }

    public function activityAssignments(): HasMany
    {
        return $this->hasMany(ActivityAssignment::class);
    }

    public function forum(): HasMany
    {
        return $this->hasMany(Forum::class, 'class_id');
    }

    /**
     * Get group chat for this classroom (by name matching)
     */
    public function getClassroomGroupChat()
    {
        // Generate group chat name: class name + subject + year
        $groupChatName = $this->name;
        if ($this->subject) {
            $groupChatName .= ' ' . $this->subject;
        }
        if ($this->year) {
            $groupChatName .= ' ' . $this->year;
        }

        // Find conversation by name and created_by (teacher)
        return \App\Models\Conversation::where('type', 'group')
            ->where('name', $groupChatName)
            ->where('created_by', $this->teacher_id)
            ->first();
    }
}

