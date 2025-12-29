-- CompuPlay Database Schema Export
-- Generated: 2025-12-20 10:51:01
-- Database: compuplay

SET FOREIGN_KEY_CHECKS=0;

-- Table: activity
CREATE TABLE `activity` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint(20) unsigned NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'Game',
  `suggested_duration` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activities_teacher_id_foreign` (`teacher_id`),
  CONSTRAINT `activities_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: activity_assignment
CREATE TABLE `activity_assignment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_id` bigint(20) unsigned NOT NULL,
  `classroom_id` bigint(20) unsigned NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(255) NOT NULL DEFAULT 'assigned',
  `due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_assignments_activity_id_foreign` (`activity_id`),
  KEY `activity_assignments_classroom_id_foreign` (`classroom_id`),
  CONSTRAINT `activity_assignments_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `activity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_assignments_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `class` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: activity_submission
CREATE TABLE `activity_submission` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `activity_assignment_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `score` int(11) DEFAULT NULL,
  `results` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`results`)),
  `feedback` text DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `activity_id` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_submissions_activity_assignment_id_foreign` (`activity_assignment_id`),
  KEY `activity_submissions_user_id_foreign` (`user_id`),
  KEY `activity_submission_activity_id_foreign` (`activity_id`),
  CONSTRAINT `activity_submission_activity_id_foreign` FOREIGN KEY (`activity_id`) REFERENCES `activity` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_submissions_activity_assignment_id_foreign` FOREIGN KEY (`activity_assignment_id`) REFERENCES `activity_assignment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `activity_submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: ai_generated_content
CREATE TABLE `ai_generated_content` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint(20) unsigned NOT NULL,
  `class_id` bigint(20) unsigned DEFAULT NULL,
  `source_document_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`source_document_ids`)),
  `content_type` enum('summary_notes','quiz') NOT NULL DEFAULT 'summary_notes',
  `question_type` varchar(255) DEFAULT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content`)),
  `title` varchar(255) NOT NULL,
  `status` enum('processing','completed','failed') NOT NULL DEFAULT 'processing',
  `error_message` text DEFAULT NULL,
  `is_shared` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_generated_content_teacher_id_foreign` (`teacher_id`),
  KEY `ai_generated_content_class_id_foreign` (`class_id`),
  CONSTRAINT `ai_generated_content_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ai_generated_content_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: badge
CREATE TABLE `badge` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category_slug` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `requirement_type` varchar(255) NOT NULL,
  `requirement_value` int(11) NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `category_id` bigint(20) unsigned DEFAULT NULL,
  `xp_reward` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `badges_code_unique` (`code`),
  KEY `badges_category_id_foreign` (`category_id`),
  KEY `badges_category_slug_index` (`category_slug`),
  KEY `badges_code_index` (`code`),
  CONSTRAINT `badges_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `badge_category` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: badge_category
CREATE TABLE `badge_category` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `badge_categories_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cache
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cache_lock
CREATE TABLE `cache_lock` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: cache_locks
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: class
CREATE TABLE `class` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `classrooms_teacher_id_index` (`teacher_id`),
  CONSTRAINT `classrooms_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: class_student
CREATE TABLE `class_student` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `classroom_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `enrolled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `class_students_classroom_id_foreign` (`classroom_id`),
  KEY `class_students_student_id_foreign` (`student_id`),
  CONSTRAINT `class_student_classroom_id_foreign` FOREIGN KEY (`classroom_id`) REFERENCES `class` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_student_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: classes
CREATE TABLE `classes` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `classes_teacher_id_index` (`teacher_id`),
  CONSTRAINT `classes_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: comment
CREATE TABLE `comment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL,
  `author_id` bigint(20) unsigned NOT NULL,
  `parent_id` bigint(20) unsigned DEFAULT NULL,
  `content` text NOT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `reaction_count` int(11) NOT NULL DEFAULT 0,
  `is_edited` tinyint(1) NOT NULL DEFAULT 0,
  `edited_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `comment_post_id_foreign` (`post_id`),
  KEY `comment_author_id_foreign` (`author_id`),
  KEY `comment_parent_id_foreign` (`parent_id`),
  CONSTRAINT `comment_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comment_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `comment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comment_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: conversation
CREATE TABLE `conversation` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('direct','group') NOT NULL DEFAULT 'direct',
  `name` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `conversations_created_by_foreign` (`created_by`),
  CONSTRAINT `conversations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: conversation_participant
CREATE TABLE `conversation_participant` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `last_read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `conversation_participants_conversation_id_user_id_unique` (`conversation_id`,`user_id`),
  KEY `conversation_participants_user_id_foreign` (`user_id`),
  CONSTRAINT `conversation_participants_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `conversation_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: document
CREATE TABLE `document` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: email_verification_otp
CREATE TABLE `email_verification_otp` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_verification_otp_email_index` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: enrollment
CREATE TABLE `enrollment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'in_progress',
  `progress` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `enrollment_user_lesson_unique` (`user_id`,`lesson_id`),
  KEY `enrollment_lesson_id_foreign` (`lesson_id`),
  CONSTRAINT `enrollment_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lesson` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enrollment_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: enrollments
CREATE TABLE `enrollments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `status` enum('enrolled','in_progress','completed') NOT NULL DEFAULT 'enrolled',
  `progress` int(11) NOT NULL DEFAULT 0,
  `completed_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`completed_items`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `enrollments_user_id_foreign` (`user_id`),
  KEY `enrollments_lesson_id_foreign` (`lesson_id`),
  CONSTRAINT `enrollments_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lesson` (`id`) ON DELETE CASCADE,
  CONSTRAINT `enrollments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: failed_job
CREATE TABLE `failed_job` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: failed_jobs
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: forum
CREATE TABLE `forum` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_by` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `visibility` enum('public','private','class') NOT NULL DEFAULT 'public',
  `class_id` bigint(20) unsigned DEFAULT NULL,
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `member_count` int(11) NOT NULL DEFAULT 0,
  `post_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `forum_created_by_foreign` (`created_by`),
  CONSTRAINT `forum_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: forum_member
CREATE TABLE `forum_member` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `role` enum('member','moderator','admin') NOT NULL DEFAULT 'member',
  `is_muted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `forum_member_forum_id_user_id_unique` (`forum_id`,`user_id`),
  KEY `forum_member_user_id_foreign` (`user_id`),
  CONSTRAINT `forum_member_forum_id_foreign` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`id`) ON DELETE CASCADE,
  CONSTRAINT `forum_member_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: forum_post
CREATE TABLE `forum_post` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` bigint(20) unsigned NOT NULL,
  `author_id` bigint(20) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `lesson_id` bigint(20) unsigned DEFAULT NULL,
  `content` text NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `post_type` enum('discussion','question','announcement') NOT NULL DEFAULT 'discussion',
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `view_count` int(11) NOT NULL DEFAULT 0,
  `reply_count` int(11) NOT NULL DEFAULT 0,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `is_hidden` tinyint(1) NOT NULL DEFAULT 0,
  `hidden_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `hidden_by` bigint(20) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `forum_post_forum_id_foreign` (`forum_id`),
  KEY `forum_post_author_id_foreign` (`author_id`),
  KEY `forum_post_hidden_by_foreign` (`hidden_by`),
  KEY `forum_post_lesson_id_foreign` (`lesson_id`),
  CONSTRAINT `forum_post_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `forum_post_forum_id_foreign` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`id`) ON DELETE CASCADE,
  CONSTRAINT `forum_post_hidden_by_foreign` FOREIGN KEY (`hidden_by`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `forum_post_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lesson` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: forum_statistics
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `forum_statistics` AS select `f`.`id` AS `forum_id`,count(distinct `fm`.`user_id`) AS `member_count`,count(distinct `fp`.`id`) AS `post_count`,count(distinct `c`.`id`) AS `comment_count`,max(greatest(ifnull(`fp`.`updated_at`,`fp`.`created_at`),ifnull(`c`.`created_at`,`fp`.`created_at`))) AS `last_activity` from (((`forum` `f` left join `forum_member` `fm` on(`f`.`id` = `fm`.`forum_id`)) left join `forum_post` `fp` on(`f`.`id` = `fp`.`forum_id` and `fp`.`is_deleted` = 0)) left join `comment` `c` on(`fp`.`id` = `c`.`post_id` and `c`.`is_deleted` = 0)) group by `f`.`id`;

-- Table: forum_tag
CREATE TABLE `forum_tag` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` bigint(20) unsigned NOT NULL,
  `tag_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `forum_tags_forum_id_foreign` (`forum_id`),
  CONSTRAINT `forum_tags_forum_id_foreign` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: forum_user_activity
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `forum_user_activity` AS select `fm`.`user_id` AS `user_id`,`fm`.`forum_id` AS `forum_id`,count(distinct `fp`.`id`) AS `posts_count`,count(distinct `c`.`id`) AS `comments_count`,count(distinct `r`.`id`) AS `reactions_count`,max(greatest(ifnull(`fp`.`created_at`,'1970-01-01'),ifnull(`c`.`created_at`,'1970-01-01'))) AS `last_activity_date` from (((`forum_member` `fm` left join `forum_post` `fp` on(`fm`.`forum_id` = `fp`.`forum_id` and `fp`.`author_id` = `fm`.`user_id` and `fp`.`is_deleted` = 0)) left join `comment` `c` on(`fm`.`forum_id` = (select `forum_post`.`forum_id` from `forum_post` where `forum_post`.`id` = `c`.`post_id`) and `c`.`author_id` = `fm`.`user_id` and `c`.`is_deleted` = 0)) left join `reaction` `r` on(`r`.`user_id` = `fm`.`user_id` and `r`.`target_type` in ('post','comment'))) group by `fm`.`user_id`,`fm`.`forum_id`;

-- Table: friend
CREATE TABLE `friend` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `friend_id` bigint(20) unsigned NOT NULL,
  `status` enum('pending','accepted','blocked') NOT NULL DEFAULT 'pending',
  `accepted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `friends_user_id_friend_id_unique` (`user_id`,`friend_id`),
  KEY `friends_friend_id_foreign` (`friend_id`),
  CONSTRAINT `friends_friend_id_foreign` FOREIGN KEY (`friend_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `friends_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: job
CREATE TABLE `job` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: jobs
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: lesson
CREATE TABLE `lesson` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `original_lesson_id` bigint(20) unsigned DEFAULT NULL,
  `original_author_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `content_blocks` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content_blocks`)),
  `duration` varchar(255) DEFAULT NULL,
  `material_path` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `teacher_id` bigint(20) unsigned NOT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `lessons_teacher_id_foreign` (`teacher_id`),
  KEY `lesson_original_lesson_id_foreign` (`original_lesson_id`),
  KEY `lesson_original_author_id_foreign` (`original_author_id`),
  CONSTRAINT `lesson_original_author_id_foreign` FOREIGN KEY (`original_author_id`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lesson_original_lesson_id_foreign` FOREIGN KEY (`original_lesson_id`) REFERENCES `lesson` (`id`) ON DELETE SET NULL,
  CONSTRAINT `lessons_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: lesson_assignment
CREATE TABLE `lesson_assignment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `classroom_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'Mandatory',
  `assigned_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lesson_assignments_classroom_lesson_unique` (`classroom_id`,`lesson_id`),
  KEY `lesson_assignments_lesson_id_foreign` (`lesson_id`),
  CONSTRAINT `lesson_assignments_classroom_id_foreign_fixed` FOREIGN KEY (`classroom_id`) REFERENCES `class` (`id`) ON DELETE CASCADE,
  CONSTRAINT `lesson_assignments_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lesson` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: message
CREATE TABLE `message` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `sender_id` bigint(20) unsigned NOT NULL,
  `content` text NOT NULL,
  `message_type` enum('text','link','file') NOT NULL DEFAULT 'text',
  `attachment_url` varchar(255) DEFAULT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `attachment_size` int(11) DEFAULT NULL,
  `is_edited` tinyint(1) NOT NULL DEFAULT 0,
  `edited_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_conversation_id_foreign` (`conversation_id`),
  KEY `messages_sender_id_foreign` (`sender_id`),
  KEY `messages_created_at_index` (`created_at`),
  CONSTRAINT `messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversation` (`id`) ON DELETE CASCADE,
  CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: message_user_deleted
CREATE TABLE `message_user_deleted` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `message_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `message_user_deleted_message_id_user_id_unique` (`message_id`,`user_id`),
  KEY `message_user_deleted_user_id_foreign` (`user_id`),
  CONSTRAINT `message_user_deleted_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE,
  CONSTRAINT `message_user_deleted_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: migrations
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: muted_user
CREATE TABLE `muted_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `muted_by` bigint(20) unsigned NOT NULL,
  `reason` text DEFAULT NULL,
  `muted_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `muted_users_forum_id_user_id_unique` (`forum_id`,`user_id`),
  KEY `muted_users_muted_by_foreign` (`muted_by`),
  KEY `muted_users_user_id_index` (`user_id`),
  CONSTRAINT `muted_users_forum_id_foreign` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`id`) ON DELETE CASCADE,
  CONSTRAINT `muted_users_muted_by_foreign` FOREIGN KEY (`muted_by`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `muted_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: notification
CREATE TABLE `notification` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_type` varchar(50) DEFAULT NULL,
  `related_id` bigint(20) unsigned DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  KEY `notifications_type_index` (`type`),
  KEY `notifications_is_read_index` (`is_read`),
  KEY `notifications_created_at_index` (`created_at`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: personal_access_token
CREATE TABLE `personal_access_token` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: personal_access_tokens
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: poll_option
CREATE TABLE `poll_option` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `vote_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `poll_options_post_id_index` (`post_id`),
  CONSTRAINT `poll_options_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: poll_vote
CREATE TABLE `poll_vote` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL,
  `option_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `poll_votes_post_id_user_id_unique` (`post_id`,`user_id`),
  KEY `poll_votes_user_id_foreign` (`user_id`),
  KEY `poll_votes_option_id_index` (`option_id`),
  CONSTRAINT `poll_votes_option_id_foreign` FOREIGN KEY (`option_id`) REFERENCES `poll_option` (`id`) ON DELETE CASCADE,
  CONSTRAINT `poll_votes_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE,
  CONSTRAINT `poll_votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: post_attachment
CREATE TABLE `post_attachment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL,
  `file_url` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_attachments_post_id_index` (`post_id`),
  CONSTRAINT `post_attachments_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: post_tag
CREATE TABLE `post_tag` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `post_id` bigint(20) unsigned NOT NULL,
  `tag_name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_tags_post_id_tag_name_index` (`post_id`,`tag_name`),
  CONSTRAINT `post_tags_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: quiz_attempt
CREATE TABLE `quiz_attempt` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned DEFAULT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `total_questions` int(11) NOT NULL DEFAULT 0,
  `points_earned` int(11) NOT NULL DEFAULT 0,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers`)),
  `submitted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_attempts_user_id_foreign` (`user_id`),
  KEY `quiz_attempts_lesson_id_foreign` (`lesson_id`),
  CONSTRAINT `quiz_attempts_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lesson` (`id`) ON DELETE CASCADE,
  CONSTRAINT `quiz_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: reaction
CREATE TABLE `reaction` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `target_type` enum('post','comment') NOT NULL,
  `target_id` bigint(20) unsigned NOT NULL,
  `reaction_type` enum('like','love','laugh','angry','sad') NOT NULL DEFAULT 'like',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reactions_user_id_target_type_target_id_unique` (`user_id`,`target_type`,`target_id`),
  KEY `reactions_target_type_target_id_index` (`target_type`,`target_id`),
  CONSTRAINT `reactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: report
CREATE TABLE `report` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reporter_id` bigint(20) unsigned NOT NULL,
  `reportable_type` varchar(255) NOT NULL,
  `reportable_id` bigint(20) unsigned NOT NULL,
  `reason` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `reviewed_by` bigint(20) unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reports_reporter_id_reportable_type_reportable_id_unique` (`reporter_id`,`reportable_type`,`reportable_id`),
  KEY `reports_reviewed_by_foreign` (`reviewed_by`),
  KEY `reports_reportable_type_reportable_id_status_index` (`reportable_type`,`reportable_id`,`status`),
  CONSTRAINT `reports_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reports_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `user` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: reported_content
CREATE TABLE `reported_content` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `reporter_id` bigint(20) unsigned NOT NULL,
  `content_type` enum('post','comment','forum') NOT NULL,
  `content_id` bigint(20) unsigned NOT NULL,
  `reason` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reported_content_content_type_content_id_index` (`content_type`,`content_id`),
  KEY `reported_content_reporter_id_index` (`reporter_id`),
  CONSTRAINT `reported_content_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: saved_post
CREATE TABLE `saved_post` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `post_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `saved_posts_user_id_post_id_unique` (`user_id`,`post_id`),
  KEY `saved_posts_post_id_foreign` (`post_id`),
  KEY `saved_posts_user_id_index` (`user_id`),
  CONSTRAINT `saved_posts_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE,
  CONSTRAINT `saved_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: session
CREATE TABLE `session` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: sessions
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: student
CREATE TABLE `student` (
  `student_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `class` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: student_answer
CREATE TABLE `student_answer` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned NOT NULL,
  `q1_answer` varchar(255) DEFAULT NULL,
  `q2_answer` varchar(255) DEFAULT NULL,
  `q3_answer` varchar(255) DEFAULT NULL,
  `total_marks` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `student_answers_lesson_id_foreign` (`lesson_id`),
  KEY `student_answers_student_id_foreign` (`student_id`),
  CONSTRAINT `student_answers_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lesson` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_answers_student_id_foreign` FOREIGN KEY (`student_id`) REFERENCES `student` (`student_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: submission
CREATE TABLE `submission` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `lesson_id` bigint(20) unsigned DEFAULT NULL,
  `assignment_name` varchar(255) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Not Submitted',
  `feedback` text DEFAULT NULL,
  `grade` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `submissions_user_id_foreign` (`user_id`),
  KEY `submissions_lesson_id_foreign` (`lesson_id`),
  CONSTRAINT `submissions_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lesson` (`id`) ON DELETE CASCADE,
  CONSTRAINT `submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: user
CREATE TABLE `user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `school` varchar(255) DEFAULT NULL,
  `class` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT 'student',
  `points` int(11) NOT NULL DEFAULT 0,
  `is_online` tinyint(1) DEFAULT 0,
  `chatbot_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `allow_friend_requests` tinyint(1) NOT NULL DEFAULT 1,
  `last_seen` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: user_badge
CREATE TABLE `user_badge` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `badge_code` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_badges_user_id_index` (`user_id`),
  KEY `user_badges_badge_code_index` (`badge_code`),
  CONSTRAINT `user_badges_badge_code_foreign` FOREIGN KEY (`badge_code`) REFERENCES `badge` (`code`) ON DELETE CASCADE,
  CONSTRAINT `user_badges_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS=1;
