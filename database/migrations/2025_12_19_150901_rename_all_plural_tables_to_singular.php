<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Define all plural to singular table name mappings
        $tableMappings = [
            'messages' => 'message',
            'activities' => 'activity',
            'friends' => 'friend',
            'class_students' => 'class_student',
            'activity_assignments' => 'activity_assignment',
            'enrollments' => 'enrollment',
            'quiz_attempts' => 'quiz_attempt',
            'classrooms' => 'class',
            'lessons' => 'lesson',
            'badges' => 'badge',
            'conversations' => 'conversation',
            'user_badges' => 'user_badge',
            'post_tags' => 'post_tag',
            'lesson_assignments' => 'lesson_assignment',
            'students' => 'student',
            'forum_tags' => 'forum_tag',
            'submissions' => 'submission',
            'student_answers' => 'student_answer',
            'reports' => 'report',
            'conversation_participants' => 'conversation_participant',
            'badge_categories' => 'badge_category',
            'activity_submissions' => 'activity_submission',
            'notifications' => 'notification',
            'sessions' => 'session',
            'cache_locks' => 'cache_lock',
            'jobs' => 'job',
            'job_batches' => 'job_batch',
            'failed_jobs' => 'failed_job',
            'personal_access_tokens' => 'personal_access_token',
        ];

        // Rename tables
        foreach ($tableMappings as $pluralName => $singularName) {
            if (Schema::hasTable($pluralName) && !Schema::hasTable($singularName)) {
                Schema::rename($pluralName, $singularName);
            }
        }

        // Update foreign key constraints that reference renamed tables
        $this->updateForeignKeys($tableMappings);
    }

    /**
     * Update foreign key constraints
     */
    private function updateForeignKeys(array $tableMappings): void
    {
        // Get all foreign keys from information_schema
        $foreignKeys = DB::select("
            SELECT 
                TABLE_NAME,
                CONSTRAINT_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        foreach ($foreignKeys as $fk) {
            $tableName = $fk->TABLE_NAME;
            $constraintName = $fk->CONSTRAINT_NAME;
            $referencedTable = $fk->REFERENCED_TABLE_NAME;
            
            // Check if the referenced table was renamed
            if (isset($tableMappings[$referencedTable])) {
                $newReferencedTable = $tableMappings[$referencedTable];
                
                // Drop the old foreign key
                try {
                    DB::statement("ALTER TABLE `{$tableName}` DROP FOREIGN KEY `{$constraintName}`");
                } catch (\Exception $e) {
                    // Foreign key might not exist or have different name, continue
                }
                
                // Recreate with new table name
                try {
                    $columnName = $fk->COLUMN_NAME;
                    $referencedColumn = $fk->REFERENCED_COLUMN_NAME;
                    
                    DB::statement("
                        ALTER TABLE `{$tableName}` 
                        ADD CONSTRAINT `{$constraintName}` 
                        FOREIGN KEY (`{$columnName}`) 
                        REFERENCES `{$newReferencedTable}` (`{$referencedColumn}`) 
                        ON DELETE CASCADE
                    ");
                } catch (\Exception $e) {
                    // Might already exist or constraint name conflict, continue
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse mappings (singular to plural)
        $reverseMappings = [
            'message' => 'messages',
            'activity' => 'activities',
            'friend' => 'friends',
            'class_student' => 'class_students',
            'activity_assignment' => 'activity_assignments',
            'enrollment' => 'enrollments',
            'quiz_attempt' => 'quiz_attempts',
            'class' => 'classrooms',
            'lesson' => 'lessons',
            'badge' => 'badges',
            'conversation' => 'conversations',
            'user_badge' => 'user_badges',
            'post_tag' => 'post_tags',
            'lesson_assignment' => 'lesson_assignments',
            'student' => 'students',
            'forum_tag' => 'forum_tags',
            'submission' => 'submissions',
            'student_answer' => 'student_answers',
            'report' => 'reports',
            'conversation_participant' => 'conversation_participants',
            'badge_category' => 'badge_categories',
            'activity_submission' => 'activity_submissions',
            'notification' => 'notifications',
            'session' => 'sessions',
            'cache_lock' => 'cache_locks',
            'job' => 'jobs',
            'job_batch' => 'job_batches',
            'failed_job' => 'failed_jobs',
            'personal_access_token' => 'personal_access_tokens',
        ];

        // Update foreign keys first (reverse)
        $this->updateForeignKeys($reverseMappings);

        // Rename tables back
        foreach ($reverseMappings as $singularName => $pluralName) {
            if (Schema::hasTable($singularName) && !Schema::hasTable($pluralName)) {
                Schema::rename($singularName, $pluralName);
            }
        }
    }
};
