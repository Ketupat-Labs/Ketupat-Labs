<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop views first (they reference tables we're renaming)
        $this->dropViews();
        
        // Handle special case: drop old 'class' table if it exists and is empty
        if (Schema::hasTable('class') && Schema::hasTable('classes')) {
            $oldClassCount = DB::table('class')->count();
            if ($oldClassCount == 0) {
                Schema::dropIfExists('class');
            }
        }

        // Rename tables from plural to singular
        $renames = [
            'activities' => 'activity',
            'activity_assignments' => 'activity_assignment',
            'activity_submissions' => 'activity_submission',
            'badge_categories' => 'badge_category',
            'badges' => 'badge',
            'classes' => 'class', // Note: old 'class' table should be dropped first if empty
            'class_students' => 'class_student',
            'conversations' => 'conversation',
            'conversation_participants' => 'conversation_participant',
            'enrollments' => 'enrollment',
            'forum_tags' => 'forum_tag',
            'friends' => 'friend',
            'lessons' => 'lesson',
            'lesson_assignments' => 'lesson_assignment',
            'messages' => 'message',
            'notifications' => 'notification',
            'post_tags' => 'post_tag',
            'quiz_attempts' => 'quiz_attempt',
            'reports' => 'report',
            'students' => 'student',
            'student_answers' => 'student_answer',
            'submissions' => 'submission',
            'user_badges' => 'user_badge',
        ];

        foreach ($renames as $oldName => $newName) {
            if (Schema::hasTable($oldName) && !Schema::hasTable($newName)) {
                Schema::rename($oldName, $newName);
            }
        }

        // Recreate views with new table names
        $this->recreateViews();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop views first
        $this->dropViews();
        
        // Rename tables back from singular to plural
        $renames = [
            'activity' => 'activities',
            'activity_assignment' => 'activity_assignments',
            'activity_submission' => 'activity_submissions',
            'badge_category' => 'badge_categories',
            'badge' => 'badges',
            'class' => 'classes',
            'class_student' => 'class_students',
            'conversation' => 'conversations',
            'conversation_participant' => 'conversation_participants',
            'enrollment' => 'enrollments',
            'forum_tag' => 'forum_tags',
            'friend' => 'friends',
            'lesson' => 'lessons',
            'lesson_assignment' => 'lesson_assignments',
            'message' => 'messages',
            'notification' => 'notifications',
            'post_tag' => 'post_tags',
            'quiz_attempt' => 'quiz_attempts',
            'report' => 'reports',
            'student' => 'students',
            'student_answer' => 'student_answers',
            'submission' => 'submissions',
            'user_badge' => 'user_badges',
        ];

        foreach ($renames as $oldName => $newName) {
            if (Schema::hasTable($oldName) && !Schema::hasTable($newName)) {
                Schema::rename($oldName, $newName);
            }
        }

        // Recreate views with old table names
        $this->recreateViews(true);
    }

    /**
     * Drop views that reference tables being renamed
     */
    private function dropViews(): void
    {
        $views = ['forum_statistics', 'forum_user_activity', 'unread_message_counts'];
        
        foreach ($views as $view) {
            try {
                DB::statement("DROP VIEW IF EXISTS `{$view}`");
            } catch (\Exception $e) {
                // View might not exist, continue
            }
        }
    }

    /**
     * Recreate views with updated table names
     */
    private function recreateViews(bool $usePlural = false): void
    {
        $forumTable = $usePlural ? 'forums' : 'forum';
        $forumMemberTable = $usePlural ? 'forum_members' : 'forum_member';
        $forumPostTable = $usePlural ? 'forum_posts' : 'forum_post';
        $commentTable = $usePlural ? 'comments' : 'comment';
        $reactionTable = $usePlural ? 'reactions' : 'reaction';
        $conversationTable = $usePlural ? 'conversations' : 'conversation';
        $conversationParticipantTable = $usePlural ? 'conversation_participants' : 'conversation_participant';
        $messageTable = $usePlural ? 'messages' : 'message';

        // Recreate forum_statistics view
        try {
            DB::statement("
                CREATE OR REPLACE VIEW `forum_statistics` AS 
                SELECT 
                    `f`.`id` AS `forum_id`, 
                    count(distinct `fm`.`user_id`) AS `member_count`, 
                    count(distinct `fp`.`id`) AS `post_count`, 
                    count(distinct `c`.`id`) AS `comment_count`, 
                    max(greatest(ifnull(`fp`.`updated_at`,`fp`.`created_at`),ifnull(`c`.`created_at`,`fp`.`created_at`))) AS `last_activity` 
                FROM (((`{$forumTable}` `f` 
                    left join `{$forumMemberTable}` `fm` on(`f`.`id` = `fm`.`forum_id`)) 
                    left join `{$forumPostTable}` `fp` on(`f`.`id` = `fp`.`forum_id` and `fp`.`is_deleted` = 0)) 
                    left join `{$commentTable}` `c` on(`fp`.`id` = `c`.`post_id` and `c`.`is_deleted` = 0)) 
                GROUP BY `f`.`id`
            ");
        } catch (\Exception $e) {
            // Continue if view creation fails
        }

        // Recreate forum_user_activity view
        try {
            DB::statement("
                CREATE OR REPLACE VIEW `forum_user_activity` AS 
                SELECT 
                    `fm`.`user_id` AS `user_id`, 
                    `fm`.`forum_id` AS `forum_id`, 
                    count(distinct `fp`.`id`) AS `posts_count`, 
                    count(distinct `c`.`id`) AS `comments_count`, 
                    count(distinct `r`.`id`) AS `reactions_count`, 
                    max(greatest(ifnull(`fp`.`created_at`,'1970-01-01'),ifnull(`c`.`created_at`,'1970-01-01'))) AS `last_activity_date` 
                FROM (((`{$forumMemberTable}` `fm` 
                    left join `{$forumPostTable}` `fp` on(`fm`.`forum_id` = `fp`.`forum_id` and `fp`.`author_id` = `fm`.`user_id` and `fp`.`is_deleted` = 0)) 
                    left join `{$commentTable}` `c` on(`fm`.`forum_id` = (select `{$forumPostTable}`.`forum_id` from `{$forumPostTable}` where `{$forumPostTable}`.`id` = `c`.`post_id`) and `c`.`author_id` = `fm`.`user_id` and `c`.`is_deleted` = 0)) 
                    left join `{$reactionTable}` `r` on(`r`.`user_id` = `fm`.`user_id` and `r`.`target_type` in ('post','comment'))) 
                GROUP BY `fm`.`user_id`, `fm`.`forum_id`
            ");
        } catch (\Exception $e) {
            // Continue if view creation fails
        }

        // Recreate unread_message_counts view
        try {
            DB::statement("
                CREATE OR REPLACE VIEW `unread_message_counts` AS 
                SELECT 
                    `cp`.`user_id` AS `user_id`, 
                    `c`.`id` AS `conversation_id`, 
                    count(`m`.`id`) AS `unread_count` 
                FROM ((`{$conversationParticipantTable}` `cp` 
                    left join `{$conversationTable}` `c` on(`cp`.`conversation_id` = `c`.`id`)) 
                    left join `{$messageTable}` `m` on(`c`.`id` = `m`.`conversation_id` and `m`.`sender_id` <> `cp`.`user_id` and `m`.`created_at` > `cp`.`last_read_at` and `m`.`is_deleted` = 0)) 
                GROUP BY `cp`.`user_id`, `c`.`id`
            ");
        } catch (\Exception $e) {
            // Continue if view creation fails
        }
    }
};
