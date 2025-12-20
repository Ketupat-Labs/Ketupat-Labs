-- =====================================================
-- CompuPlay Database - Complete Export
-- Generated: 2025-12-20 13:10:06
-- 
-- This file contains the complete database schema
-- for the CompuPlay Learning Management System.
-- 
-- To use: Import this file into your MySQL database
-- mysql -u root -p your_database_name < CompuPlay.sql
-- =====================================================

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


-- =====================================================
-- Table structure for table `activity`
-- =====================================================

DROP TABLE IF EXISTS `activity`;
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

-- Data for table `activity`
LOCK TABLES `activity` WRITE;
/*!40000 ALTER TABLE `activity` DISABLE KEYS */;
INSERT INTO `activity` VALUES ('1', '2', '0', 'Memory Game (Hardware)', 'Game', '15 mins', 'Match the computer hardware pairs.', '{\"theme\":\"tech\",\"gridSize\":4,\"pairs\":[{\"text\":\"CPU\",\"icon\":\"fas fa-microchip\"},{\"text\":\"RAM\",\"icon\":\"fas fa-memory\"},{\"text\":\"HDD\",\"icon\":\"fas fa-hdd\"},{\"text\":\"Mouse\",\"icon\":\"fas fa-mouse\"}]}', '2025-12-16 10:50:58', '2025-12-16 10:50:58'),
('2', '2', '0', 'Kuiz Pangkalan Data', 'Quiz', '30 mins', 'Kuiz pendek mengenai asas pangkalan data.', '{\"questions\":[{\"question\":\"Apakah maksud SQL?\",\"options\":[\"Structured Query Language\",\"Strong Question Language\",\"Structured Question List\"],\"answer\":0},{\"question\":\"Manakah kunci unik?\",\"options\":[\"Foreign Key\",\"Primary Key\",\"Index\"],\"answer\":1}]}', '2025-12-16 10:50:58', '2025-12-16 10:50:58'),
('3', '8', '1', 'aktiviti test', 'Game', '30', 'dvzsdvs', '{\"mode\":\"custom\",\"gridSize\":4,\"customPairs\":[{\"card1\":\"1\",\"card2\":\"1\"},{\"card1\":\"2\",\"card2\":\"2\"}]}', '2025-12-20 08:26:07', '2025-12-20 09:21:51'),
('4', '8', '0', 'aktivti test 3', 'Quiz', '30', NULL, '{\"questions\":[{\"question\":\"1\",\"answers\":[\"1\",\"2\",\"3\",\"4\"],\"correctAnswer\":0}]}', '2025-12-20 09:55:41', '2025-12-20 10:19:44');
/*!40000 ALTER TABLE `activity` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `activity_assignment`
-- =====================================================

DROP TABLE IF EXISTS `activity_assignment`;
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

-- Data for table `activity_assignment`
LOCK TABLES `activity_assignment` WRITE;
/*!40000 ALTER TABLE `activity_assignment` DISABLE KEYS */;
INSERT INTO `activity_assignment` VALUES ('3', '3', '5', '2025-12-20 09:10:17', 'assigned', '2025-12-21', NULL, '2025-12-20 09:10:17', '2025-12-20 09:19:35'),
('4', '4', '5', '2025-12-20 09:56:06', 'assigned', '2025-12-22', NULL, '2025-12-20 09:56:06', '2025-12-20 09:56:06');
/*!40000 ALTER TABLE `activity_assignment` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `activity_submission`
-- =====================================================

DROP TABLE IF EXISTS `activity_submission`;
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `activity_submission`
LOCK TABLES `activity_submission` WRITE;
/*!40000 ALTER TABLE `activity_submission` DISABLE KEYS */;
INSERT INTO `activity_submission` VALUES ('2', '4', '9', '100', '{\"percentage\":100}', NULL, '2025-12-20 10:19:57', '2025-12-20 10:19:57', '2025-12-20 10:19:57', NULL),
('3', '4', '9', '100', '{\"percentage\":100}', NULL, '2025-12-20 10:53:15', '2025-12-20 10:53:15', '2025-12-20 10:53:15', '4');
/*!40000 ALTER TABLE `activity_submission` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `ai_generated_content`
-- =====================================================

DROP TABLE IF EXISTS `ai_generated_content`;
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


-- =====================================================
-- Table structure for table `badge`
-- =====================================================

DROP TABLE IF EXISTS `badge`;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `badge`
LOCK TABLES `badge` WRITE;
/*!40000 ALTER TABLE `badge` DISABLE KEYS */;
INSERT INTO `badge` VALUES ('1', '', 'Konsistensi', 'Memahami dan menerapkan konsistensi dalam antaramuka', 'keperluan', 'fas fa-check', 'points', '50', '#1abc9c', '1', '10', '2025-12-10 03:35:37', '2025-12-10 03:35:37'),
('2', 'newcomer', 'Pendatang Baru', 'Selamat datang ke komuniti kami! Anda telah mendaftar masuk buat kali pertama.', 'general', 'fas fa-door-open', 'login', '1', '#3B82F6', '5', '50', NULL, NULL),
('3', 'explorer', 'Penjelajah', 'Melawat 5 halaman berbeza dalam satu sesi.', 'general', 'fas fa-compass', 'visit', '5', '#3B82F6', '5', '100', NULL, NULL),
('4', 'quiz_master', 'Pakar Kuiz', 'Mendapat markah penuh dalam 3 kuiz berturut-turut.', 'skill', 'fas fa-brain', 'quiz_score', '3', '#10B981', '10', '200', NULL, NULL),
('5', 'coder', 'Pengaturcara Muda', 'Menyiapkan tugasan pengaturcaraan pertama.', 'skill', 'fas fa-laptop-code', 'assignment', '1', '#10B981', '10', '150', NULL, NULL),
('6', 'friendly', 'Rakan Baik', 'Mempunyai 5 rakan yang disahkan.', 'social', 'fas fa-smile', 'friends', '5', '#EC4899', '20', '100', NULL, NULL),
('7', 'helper', 'Pembantu', 'Menjawab 10 soalan di forum.', 'social', 'fas fa-hands-helping', 'forum_reply', '10', '#EC4899', '20', '300', NULL, NULL),
('8', 'champion', 'Juara Kelas', 'Mendapat tempat pertama dalam papan pendahulu mingguan.', 'special', 'fas fa-trophy', 'leaderboard', '1', '#F59E0B', '15', '500', NULL, NULL),
('9', 'pendatang_baru', 'Pendatang Baru', 'Selamat datang ke CompuPlay! Ini adalah permulaan perjalanan pembelajaran anda.', 'achievement', 'fas fa-star', 'first_login', '0', '#FFD700', NULL, '25', '2025-12-20 12:43:36', '2025-12-20 12:43:36');
/*!40000 ALTER TABLE `badge` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `badge_category`
-- =====================================================

DROP TABLE IF EXISTS `badge_category`;
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

-- Data for table `badge_category`
LOCK TABLES `badge_category` WRITE;
/*!40000 ALTER TABLE `badge_category` DISABLE KEYS */;
INSERT INTO `badge_category` VALUES ('1', 'Keperluan', 'keperluan', NULL, NULL, NULL, '2025-12-10 15:53:00', '2025-12-10 15:53:00'),
('2', 'Reka Bentuk', 'reka', NULL, NULL, NULL, '2025-12-10 15:53:00', '2025-12-10 15:53:00'),
('3', 'Prototaip', 'prototaip', NULL, NULL, NULL, '2025-12-10 15:53:00', '2025-12-10 15:53:00'),
('4', 'Penilaian', 'penilaian', NULL, NULL, NULL, '2025-12-10 15:53:00', '2025-12-10 15:53:00'),
('5', 'Umum', 'general', 'Lencana untuk pencapaian umum.', 'fas fa-globe', '#3B82F6', NULL, NULL),
('6', '', 'name', NULL, NULL, NULL, NULL, NULL),
('7', '', 'description', NULL, NULL, NULL, NULL, NULL),
('8', '', 'icon', NULL, NULL, NULL, NULL, NULL),
('9', '', 'color', NULL, NULL, NULL, NULL, NULL),
('10', 'Kemahiran', 'skill', 'Lencana untuk penguasaan kemahiran spesifik.', 'fas fa-tools', '#10B981', NULL, NULL),
('15', 'Pencapaian Khas', 'special', 'Lencana untuk pencapaian luar biasa.', 'fas fa-star', '#F59E0B', NULL, NULL),
('20', 'Sosial', 'social', 'Lencana untuk interaksi komuniti.', 'fas fa-users', '#EC4899', NULL, NULL);
/*!40000 ALTER TABLE `badge_category` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `cache`
-- =====================================================

DROP TABLE IF EXISTS `cache`;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `cache`
LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('compuplay-cache-7c2150d7106073168321f39ec452420d', 'i:2;', '1766236216'),
('compuplay-cache-7c2150d7106073168321f39ec452420d:timer', 'i:1766236216;', '1766236216'),
('compuplay-cache-830b3b63aeb7c393f1a75d0894c14921', 'i:1;', '1766236236'),
('compuplay-cache-830b3b63aeb7c393f1a75d0894c14921:timer', 'i:1766236236;', '1766236236'),
('compuplay-cache-df21bfa12c4e294c70f64916c0fbc9a5', 'i:1;', '1766235950'),
('compuplay-cache-df21bfa12c4e294c70f64916c0fbc9a5:timer', 'i:1766235950;', '1766235950');
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `cache_lock`
-- =====================================================

DROP TABLE IF EXISTS `cache_lock`;
CREATE TABLE `cache_lock` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- Table structure for table `cache_locks`
-- =====================================================

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- Table structure for table `class`
-- =====================================================

DROP TABLE IF EXISTS `class`;
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

-- Data for table `class`
LOCK TABLES `class` WRITE;
/*!40000 ALTER TABLE `class` DISABLE KEYS */;
INSERT INTO `class` VALUES ('4', '5', '5 Bestari', 'HCI', '2025', '2025-12-19 19:10:51', '2025-12-19 19:10:51'),
('5', '8', '5 Bendi', 'Computer Science', '2025', '2025-12-20 07:31:05', '2025-12-20 07:31:05');
/*!40000 ALTER TABLE `class` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `class_student`
-- =====================================================

DROP TABLE IF EXISTS `class_student`;
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

-- Data for table `class_student`
LOCK TABLES `class_student` WRITE;
/*!40000 ALTER TABLE `class_student` DISABLE KEYS */;
INSERT INTO `class_student` VALUES ('10', '5', '9', '2025-12-20 07:31:14', NULL, NULL);
/*!40000 ALTER TABLE `class_student` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `comment`
-- =====================================================

DROP TABLE IF EXISTS `comment`;
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


-- =====================================================
-- Table structure for table `conversation`
-- =====================================================

DROP TABLE IF EXISTS `conversation`;
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

-- Data for table `conversation`
LOCK TABLES `conversation` WRITE;
/*!40000 ALTER TABLE `conversation` DISABLE KEYS */;
INSERT INTO `conversation` VALUES ('1', 'group', '5 Bestari HCI 2025', '5', '2025-12-19 19:10:52', '2025-12-19 23:04:54'),
('3', 'group', '5 Bendi Computer Science 2025', '8', '2025-12-20 07:31:05', '2025-12-20 07:31:05');
/*!40000 ALTER TABLE `conversation` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `conversation_participant`
-- =====================================================

DROP TABLE IF EXISTS `conversation_participant`;
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

-- Data for table `conversation_participant`
LOCK TABLES `conversation_participant` WRITE;
/*!40000 ALTER TABLE `conversation_participant` DISABLE KEYS */;
INSERT INTO `conversation_participant` VALUES ('1', '1', '5', '0', '2025-12-19 23:04:55', '2025-12-19 19:10:52', '2025-12-19 19:10:52'),
('7', '3', '8', '0', NULL, '2025-12-20 07:31:05', '2025-12-20 07:31:05'),
('8', '3', '9', '0', NULL, '2025-12-20 07:31:14', '2025-12-20 07:31:14');
/*!40000 ALTER TABLE `conversation_participant` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `document`
-- =====================================================

DROP TABLE IF EXISTS `document`;
CREATE TABLE `document` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- Table structure for table `email_verification_otp`
-- =====================================================

DROP TABLE IF EXISTS `email_verification_otp`;
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

-- Data for table `email_verification_otp`
LOCK TABLES `email_verification_otp` WRITE;
/*!40000 ALTER TABLE `email_verification_otp` DISABLE KEYS */;
INSERT INTO `email_verification_otp` VALUES ('3', 'angchunwei@graduate.utm.my', '549728', 'ANG STUDENT', '$2y$12$LXjnDlCujvW9uHGP3U9glOWf90Me9mCKYI/PLr3XF7eN81wje.Meu', 'student', '2025-12-20 07:55:51', '1', '2025-12-19 23:49:47', '2025-12-19 23:55:31'),
('5', 'angchunwei13@gmail.com', '138098', 'CIKGU ANG', '$2y$12$RU3ywEOl8BcsoouRuG7hjOIbMe0EVQUNgbAuIsMMsjtFjRZihQ4Wq', 'teacher', '2025-12-20 08:26:04', '1', '2025-12-20 00:24:28', '2025-12-20 00:25:33'),
('6', 'teacher1@email.com', '949142', 'teacher 1', '$2y$12$wEUOydPArj2JLxzpC0BKLuNcS8m5nQnmaErAIIpRETVgvL/SEOIUO', 'teacher', '2025-12-20 07:18:44', '0', '2025-12-20 07:08:44', '2025-12-20 07:08:44'),
('11', 'raudhahmzn@gmail.com', '070290', 'raudhah', '$2y$12$Co8vpa0tQSfmD4e0YkWytuguG7luqCvVccr/FhWAb7oZNlEdkLWM2', 'teacher', '2025-12-20 15:25:50', '1', '2025-12-20 07:25:30', '2025-12-20 07:25:30'),
('12', 'mintybluebell@gmail.com', '415796', 'mintybluebell', '$2y$12$9fTCU5EWl367dkbtSMZh/.Rkc95Ua/zyi8PF2oT/7q7fdE8xuI0aq', 'student', '2025-12-20 15:27:14', '1', '2025-12-20 07:26:45', '2025-12-20 07:26:45');
/*!40000 ALTER TABLE `email_verification_otp` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `enrollment`
-- =====================================================

DROP TABLE IF EXISTS `enrollment`;
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

-- Data for table `enrollment`
LOCK TABLES `enrollment` WRITE;
/*!40000 ALTER TABLE `enrollment` DISABLE KEYS */;
INSERT INTO `enrollment` VALUES ('1', '9', '4', 'in_progress', '0', '2025-12-20 07:43:05', '2025-12-20 07:43:05');
/*!40000 ALTER TABLE `enrollment` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `failed_job`
-- =====================================================

DROP TABLE IF EXISTS `failed_job`;
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


-- =====================================================
-- Table structure for table `forum`
-- =====================================================

DROP TABLE IF EXISTS `forum`;
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

-- Data for table `forum`
LOCK TABLES `forum` WRITE;
/*!40000 ALTER TABLE `forum` DISABLE KEYS */;
INSERT INTO `forum` VALUES ('4', '8', 'test 1', 'test 1 desc', NULL, 'public', NULL, NULL, NULL, '1', '3', '2025-12-20 07:29:13', '2025-12-20 08:18:45'),
('5', '5', 'sdfvczsrva', 'sfvzdsrvszdva', NULL, 'public', NULL, NULL, NULL, '1', '0', '2025-12-20 08:11:22', '2025-12-20 08:11:22');
/*!40000 ALTER TABLE `forum` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `forum_member`
-- =====================================================

DROP TABLE IF EXISTS `forum_member`;
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

-- Data for table `forum_member`
LOCK TABLES `forum_member` WRITE;
/*!40000 ALTER TABLE `forum_member` DISABLE KEYS */;
INSERT INTO `forum_member` VALUES ('7', '4', '8', 'admin', '0', '2025-12-20 07:29:13', '2025-12-20 07:29:13'),
('8', '5', '5', 'admin', '0', '2025-12-20 08:11:22', '2025-12-20 08:11:22');
/*!40000 ALTER TABLE `forum_member` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `forum_post`
-- =====================================================

DROP TABLE IF EXISTS `forum_post`;
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

-- Data for table `forum_post`
LOCK TABLES `forum_post` WRITE;
/*!40000 ALTER TABLE `forum_post` DISABLE KEYS */;
INSERT INTO `forum_post` VALUES ('9', '4', '8', 'test 1 lesson', NULL, 'test 1 lesson', NULL, 'discussion', '0', '0', '0', '0', '0', NULL, NULL, '2025-12-20 08:06:27', '2025-12-20 08:06:27', NULL),
('10', '4', '8', 'cszdvs', '1', 'sedczsfsefzsfvzsrvr', NULL, 'discussion', '0', '0', '0', '0', '0', NULL, NULL, '2025-12-20 08:10:49', '2025-12-20 08:16:37', NULL),
('11', '4', '8', 'wdaesdfghjkl', '4', 'wqaesxrdgctfhvygjbhujnkl', NULL, 'discussion', '0', '0', '0', '0', '0', NULL, NULL, '2025-12-20 08:18:45', '2025-12-20 08:18:45', NULL);
/*!40000 ALTER TABLE `forum_post` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `forum_statistics`
-- =====================================================

DROP TABLE IF EXISTS `forum_statistics`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `forum_statistics` AS select `f`.`id` AS `forum_id`,count(distinct `fm`.`user_id`) AS `member_count`,count(distinct `fp`.`id`) AS `post_count`,count(distinct `c`.`id`) AS `comment_count`,max(greatest(ifnull(`fp`.`updated_at`,`fp`.`created_at`),ifnull(`c`.`created_at`,`fp`.`created_at`))) AS `last_activity` from (((`forum` `f` left join `forum_member` `fm` on(`f`.`id` = `fm`.`forum_id`)) left join `forum_post` `fp` on(`f`.`id` = `fp`.`forum_id` and `fp`.`is_deleted` = 0)) left join `comment` `c` on(`fp`.`id` = `c`.`post_id` and `c`.`is_deleted` = 0)) group by `f`.`id`;

-- Data for table `forum_statistics`
LOCK TABLES `forum_statistics` WRITE;
/*!40000 ALTER TABLE `forum_statistics` DISABLE KEYS */;
INSERT INTO `forum_statistics` VALUES ('4', '1', '3', '0', '2025-12-20 08:18:45'),
('5', '1', '0', '0', NULL);
/*!40000 ALTER TABLE `forum_statistics` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `forum_tag`
-- =====================================================

DROP TABLE IF EXISTS `forum_tag`;
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


-- =====================================================
-- Table structure for table `forum_user_activity`
-- =====================================================

DROP TABLE IF EXISTS `forum_user_activity`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `forum_user_activity` AS select `fm`.`user_id` AS `user_id`,`fm`.`forum_id` AS `forum_id`,count(distinct `fp`.`id`) AS `posts_count`,count(distinct `c`.`id`) AS `comments_count`,count(distinct `r`.`id`) AS `reactions_count`,max(greatest(ifnull(`fp`.`created_at`,'1970-01-01'),ifnull(`c`.`created_at`,'1970-01-01'))) AS `last_activity_date` from (((`forum_member` `fm` left join `forum_post` `fp` on(`fm`.`forum_id` = `fp`.`forum_id` and `fp`.`author_id` = `fm`.`user_id` and `fp`.`is_deleted` = 0)) left join `comment` `c` on(`fm`.`forum_id` = (select `forum_post`.`forum_id` from `forum_post` where `forum_post`.`id` = `c`.`post_id`) and `c`.`author_id` = `fm`.`user_id` and `c`.`is_deleted` = 0)) left join `reaction` `r` on(`r`.`user_id` = `fm`.`user_id` and `r`.`target_type` in ('post','comment'))) group by `fm`.`user_id`,`fm`.`forum_id`;

-- Data for table `forum_user_activity`
LOCK TABLES `forum_user_activity` WRITE;
/*!40000 ALTER TABLE `forum_user_activity` DISABLE KEYS */;
INSERT INTO `forum_user_activity` VALUES ('5', '5', '0', '0', '1', '1970-01-01'),
('8', '4', '3', '0', '0', '2025-12-20 08:18:45');
/*!40000 ALTER TABLE `forum_user_activity` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `friend`
-- =====================================================

DROP TABLE IF EXISTS `friend`;
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


-- =====================================================
-- Table structure for table `job`
-- =====================================================

DROP TABLE IF EXISTS `job`;
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


-- =====================================================
-- Table structure for table `lesson`
-- =====================================================

DROP TABLE IF EXISTS `lesson`;
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

-- Data for table `lesson`
LOCK TABLES `lesson` WRITE;
/*!40000 ALTER TABLE `lesson` DISABLE KEYS */;
INSERT INTO `lesson` VALUES ('1', NULL, NULL, 'frsde7tyuruihji', 'dfgdfdfg', 'fgfgdfgdfgdfgdgdfgdfg', NULL, NULL, NULL, NULL, '4', '1', NULL, NULL, '1'),
('3', NULL, NULL, 'wewe', 'weewewew', NULL, '{\"blocks\":[]}', '5', NULL, NULL, '5', '1', '2025-12-19 21:13:16', '2025-12-20 07:59:05', '1'),
('4', NULL, NULL, 'test 2', 'test 2', NULL, '{\"blocks\":[{\"id\":1766216039596,\"type\":\"heading\",\"content\":\"TEST 2\"},{\"id\":1766216047916,\"type\":\"game\",\"content\":\"{\\\"theme\\\":\\\"animals\\\",\\\"gridSize\\\":4,\\\"customPairs\\\":[],\\\"mode\\\":\\\"preset\\\"}\"}]}', '45', NULL, NULL, '8', '1', '2025-12-20 07:34:14', '2025-12-20 07:56:17', '0'),
('5', NULL, NULL, 'Copy of frsde7tyuruihji', 'dfgdfdfg', 'fgfgdfgdfgdfgdgdfgdfg', NULL, NULL, NULL, NULL, '5', '0', '2025-12-20 08:17:19', '2025-12-20 08:17:19', '0'),
('6', NULL, NULL, 'Copy of test 2', 'test 2', NULL, '{\"blocks\":[{\"id\":1766216039596,\"type\":\"heading\",\"content\":\"TEST 2\"},{\"id\":1766216047916,\"type\":\"game\",\"content\":\"{\\\"theme\\\":\\\"animals\\\",\\\"gridSize\\\":4,\\\"customPairs\\\":[],\\\"mode\\\":\\\"preset\\\"}\"}]}', '45', NULL, NULL, '5', '0', '2025-12-20 08:18:58', '2025-12-20 08:18:58', '0');
/*!40000 ALTER TABLE `lesson` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `lesson_assignment`
-- =====================================================

DROP TABLE IF EXISTS `lesson_assignment`;
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

-- Data for table `lesson_assignment`
LOCK TABLES `lesson_assignment` WRITE;
/*!40000 ALTER TABLE `lesson_assignment` DISABLE KEYS */;
INSERT INTO `lesson_assignment` VALUES ('8', '5', '4', 'Mandatory', '2025-12-20 07:44:06', '2025-12-20 07:44:06', '2025-12-20 07:44:06'),
('9', '4', '3', 'Mandatory', '2025-12-20 07:59:01', '2025-12-20 07:59:01', '2025-12-20 07:59:01');
/*!40000 ALTER TABLE `lesson_assignment` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `message`
-- =====================================================

DROP TABLE IF EXISTS `message`;
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

-- Data for table `message`
LOCK TABLES `message` WRITE;
/*!40000 ALTER TABLE `message` DISABLE KEYS */;
INSERT INTO `message` VALUES ('18', '1', '5', 'HI', 'text', NULL, NULL, NULL, '0', NULL, '0', NULL, '2025-12-19 20:00:38', '2025-12-19 20:00:38'),
('19', '1', '5', 'dwsfdf', 'text', NULL, NULL, NULL, '0', NULL, '0', NULL, '2025-12-19 20:07:09', '2025-12-19 20:07:09'),
('20', '1', '5', 'xcsazcsdlkfhncnoliwsdhnfcolsdhnrolifhsdlvfchjzdckvisxv', 'text', NULL, NULL, NULL, '0', NULL, '0', NULL, '2025-12-19 23:04:28', '2025-12-19 23:04:28'),
('21', '1', '5', 'loewaihsdcf ioawesfyh wasedfhy oi8fwseadrohyi 8wfesdrhyio u8wefsiohyu8fweiyhuokl8fewcdhuiyglkefwliuhkgyfelhiugkyfwesarhufgwaeikyrawfeuhikrgyfewahugrikyaflewrhguikfewarhguilkfewauhglrikefwauhgilfewualghifweaiughlfweailguhfwaieughlrfwhiugelfuiwhgfiuolwseghydfedf', 'text', NULL, NULL, NULL, '0', NULL, '0', NULL, '2025-12-19 23:04:37', '2025-12-19 23:04:37'),
('22', '1', '5', 'dsfcugiifeiDWLGKUASYFGLUIfwgeluILGFUIEWLGIUFEWGLUIFWEGWUFELIGFULWIEWFEGLIUFEWGLIUFWEGLIUFEWGLIUEFWGLUIFEWGULIFEWGULIFEWGULIFEWGULIFEWGLUIFEWGUFLIEWGFUWEILFGULIEWGULIWEFGLUIWEFGLIUGWFLIUWGEFLIUWGEFLIUGWEFLIUWGEFWIULEFGWEIGULFGUIEWLFLIUGEFUIGFWELIUFGWEKGHFUGDWBFUIOLGEWFKIUJWEAGDBHFCKIUJEGFBCKEUIJFGBE.DLFGBLWIEOKUFGBHEWAFUBGEWUIKJF', 'text', NULL, NULL, NULL, '0', NULL, '0', NULL, '2025-12-19 23:04:54', '2025-12-19 23:04:54');
/*!40000 ALTER TABLE `message` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `message_user_deleted`
-- =====================================================

DROP TABLE IF EXISTS `message_user_deleted`;
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


-- =====================================================
-- Table structure for table `muted_user`
-- =====================================================

DROP TABLE IF EXISTS `muted_user`;
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


-- =====================================================
-- Table structure for table `notification`
-- =====================================================

DROP TABLE IF EXISTS `notification`;
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

-- Data for table `notification`
LOCK TABLES `notification` WRITE;
/*!40000 ALTER TABLE `notification` DISABLE KEYS */;
INSERT INTO `notification` VALUES ('1', '2', 'message', 'New message from test123', 'hbjjhjhj', 'message', '2', '0', NULL, NULL, NULL),
('2', '2', 'message', 'New message from test123', 'gfhghghg', 'message', '3', '0', NULL, NULL, NULL),
('3', '2', 'message', 'New message from test123', 'xfdgfgfg', 'message', '4', '0', NULL, NULL, NULL),
('4', '2', 'message', 'New message from test123', 'sdfsdf', 'message', '5', '0', NULL, NULL, NULL),
('5', '2', 'message', 'New message from test123', 'asdfdf', 'message', '6', '0', NULL, NULL, NULL),
('6', '2', 'message', 'New message from test123', 'asdasdasd', 'message', '7', '0', NULL, NULL, NULL),
('7', '2', 'message', 'New message from test123', 'werrwerwer', 'message', '8', '0', NULL, NULL, NULL),
('8', '2', 'message', 'New message from test123', 'zxdccvxzcvbc', 'message', '9', '0', NULL, NULL, NULL),
('9', '2', 'message', 'New message from test123', 'xcvbxcvb', 'message', '10', '0', NULL, NULL, NULL),
('10', '2', 'message', 'New message from test123', 'jyfgukfxhtgufhdtfyhuftdgy', 'message', '11', '0', NULL, NULL, NULL),
('11', '2', 'message', 'New message from test123', 'QQEDED', 'message', '12', '0', NULL, NULL, NULL),
('12', '4', 'comment', 'New comment on your post', 'test123 commented on: SDFSDAFDSAFSAWDFDS', 'comment', '6', '0', NULL, '2025-12-10 12:41:50', '2025-12-10 12:41:50'),
('13', '4', 'comment', 'New comment on your post', 'test123 commented on: SDFSDAFDSAFSAWDFDS', 'comment', '7', '0', NULL, '2025-12-10 12:41:54', '2025-12-10 12:41:54'),
('14', '2', 'message', 'New message from angchunwei6', 'sdfdf', 'message', '13', '0', NULL, '2025-12-10 14:37:00', '2025-12-10 14:37:00'),
('15', '3', 'message', 'New message from angchunwei6', 'sdfdf', 'message', '13', '1', '2025-12-10 18:34:22', '2025-12-10 14:37:00', '2025-12-10 18:34:22'),
('16', '2', 'message', 'New message from angchunwei6', 'Check out this post: http://127.0.0.1:8000/forum/post/12', 'message', '14', '0', NULL, '2025-12-10 15:41:55', '2025-12-10 15:41:55'),
('17', '3', 'message', 'New message from angchunwei6', 'Check out this post: http://127.0.0.1:8000/forum/post/12', 'message', '14', '1', '2025-12-10 18:34:22', '2025-12-10 15:41:55', '2025-12-10 18:34:22'),
('18', '2', 'message', 'New message from angchunwei6', '📌 Shared Post: SDFSDAFDSAFSAWDFDS

Forum: r/HCI

http://127.0.0.1:8000/forum/post/12', 'message', '15', '0', NULL, '2025-12-10 15:46:11', '2025-12-10 15:46:11'),
('19', '3', 'message', 'New message from angchunwei6', '📌 Shared Post: SDFSDAFDSAFSAWDFDS

Forum: r/HCI

http://127.0.0.1:8000/forum/post/12', 'message', '15', '0', NULL, '2025-12-10 15:46:11', '2025-12-10 15:46:11'),
('20', '2', 'message', 'New message from angchunwei6', 'asdsadsa', 'message', '16', '0', NULL, '2025-12-10 15:59:50', '2025-12-10 15:59:50'),
('21', '3', 'message', 'New message from angchunwei6', 'asdsadsa', 'message', '16', '1', '2025-12-10 18:34:21', '2025-12-10 15:59:50', '2025-12-10 18:34:21'),
('22', '4', 'comment', 'New comment on your post', 'test123 commented on: SDFSDAFDSAFSAWDFDS', 'comment', '8', '0', NULL, '2025-12-10 18:41:04', '2025-12-10 18:41:04'),
('23', '4', 'comment', 'New comment on your post', 'test123 commented on: SDFSDAFDSAFSAWDFDS', 'comment', '9', '0', NULL, '2025-12-10 18:41:07', '2025-12-10 18:41:07'),
('24', '4', 'comment', 'New comment on your post', 'test123 commented on: DFSDFGFG', 'comment', '10', '0', NULL, '2025-12-10 18:49:20', '2025-12-10 18:49:20'),
('25', '4', 'comment', 'New comment on your post', 'angchunwei6 commented on: DFSDFGFG', 'comment', '11', '0', NULL, '2025-12-10 18:49:39', '2025-12-10 18:49:39'),
('26', '3', 'reply', 'Reply to your comment', 'angchunwei6 replied to your comment', 'comment', '11', '0', NULL, '2025-12-10 18:49:39', '2025-12-10 18:49:39'),
('27', '4', 'comment', 'New comment on your post', 'angchunwei6 commented on: DFSDFGFG', 'comment', '12', '0', NULL, '2025-12-10 19:14:23', '2025-12-10 19:14:23'),
('28', '4', 'comment', 'New comment on your post', 'angchunwei6 commented on: DFSDFGFG', 'comment', '13', '0', NULL, '2025-12-10 19:14:36', '2025-12-10 19:14:36'),
('29', '4', 'comment', 'New comment on your post', 'angchunwei6 commented on: DFSDFGFG', 'comment', '14', '0', NULL, '2025-12-10 19:14:36', '2025-12-10 19:14:36'),
('30', '4', 'comment', 'New comment on your post', 'angchunwei6 commented on: DFSDFGFG', 'comment', '15', '0', NULL, '2025-12-10 19:16:54', '2025-12-10 19:16:54'),
('31', '3', 'reply', 'Reply to your comment', 'angchunwei6 replied to your comment', 'comment', '15', '0', NULL, '2025-12-10 19:16:54', '2025-12-10 19:16:54'),
('32', '4', 'comment', 'New comment on your post', 'angchunwei6 commented on: DFSDFGFG', 'comment', '16', '0', NULL, '2025-12-10 19:25:25', '2025-12-10 19:25:25'),
('33', '4', 'comment', 'New comment on your post', 'angchunwei6 commented on: DFSDFGFG', 'comment', '17', '0', NULL, '2025-12-10 19:34:16', '2025-12-10 19:34:16'),
('34', '4', 'comment', 'New comment on your post', 'angchunwei6 commented on: DFSDFGFG', 'comment', '18', '0', NULL, '2025-12-10 19:35:00', '2025-12-10 19:35:00'),
('35', '4', 'comment', 'New comment on your post', 'angchunwei6 commented on: DFSDFGFG', 'comment', '19', '0', NULL, '2025-12-10 19:35:03', '2025-12-10 19:35:03'),
('36', '4', 'comment', 'New comment on your post', 'angchunwei6 commented on: DFSDFGFG', 'comment', '20', '0', NULL, '2025-12-10 19:39:03', '2025-12-10 19:39:03'),
('37', '3', 'mention', 'You were mentioned', 'Ang Chun Wei mentioned you in a comment on: tyu', 'comment', '5', '0', NULL, '2025-12-18 23:04:25', '2025-12-18 23:04:25'),
('38', '1', 'message', 'New message from angchunwei6', 'sdfsdf', 'message', '17', '0', NULL, '2025-12-19 19:44:13', '2025-12-19 19:44:13'),
('39', '2', 'message', 'New message from angchunwei6', 'sdfsdf', 'message', '17', '0', NULL, '2025-12-19 19:44:13', '2025-12-19 19:44:13'),
('40', '3', 'message', 'New message from angchunwei6', 'sdfsdf', 'message', '17', '0', NULL, '2025-12-19 19:44:13', '2025-12-19 19:44:13'),
('41', '4', 'message', 'New message from angchunwei6', 'sdfsdf', 'message', '17', '0', NULL, '2025-12-19 19:44:13', '2025-12-19 19:44:13');
/*!40000 ALTER TABLE `notification` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `personal_access_token`
-- =====================================================

DROP TABLE IF EXISTS `personal_access_token`;
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


-- =====================================================
-- Table structure for table `poll_option`
-- =====================================================

DROP TABLE IF EXISTS `poll_option`;
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


-- =====================================================
-- Table structure for table `poll_vote`
-- =====================================================

DROP TABLE IF EXISTS `poll_vote`;
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


-- =====================================================
-- Table structure for table `post_attachment`
-- =====================================================

DROP TABLE IF EXISTS `post_attachment`;
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

-- Data for table `post_attachment`
LOCK TABLES `post_attachment` WRITE;
/*!40000 ALTER TABLE `post_attachment` DISABLE KEYS */;
INSERT INTO `post_attachment` VALUES ('5', '11', '/Material/uploads/2025/12/file_69328f03ebcd83.17455995.png', 'xuanxuan full costume.png', 'image/png', '521569', NULL, NULL),
('6', '12', '/Material/uploads/2025/12/file_69328f223c6090.25315942.docx', 'PYTHON LAB 1.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', '16509', NULL, NULL),
('8', '16', '/uploads/2025/12/file_693a41c61d2319.47271188.pdf', 'SAINS KOMPUTER - BUKU TEKS - TING 5.pdf', 'application/pdf', '19779831', '2025-12-11 12:00:06', '2025-12-11 12:00:06');
/*!40000 ALTER TABLE `post_attachment` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `post_tag`
-- =====================================================

DROP TABLE IF EXISTS `post_tag`;
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

-- Data for table `post_tag`
LOCK TABLES `post_tag` WRITE;
/*!40000 ALTER TABLE `post_tag` DISABLE KEYS */;
INSERT INTO `post_tag` VALUES ('6', '11', 'SDFGSDFGD', NULL, NULL),
('7', '11', 'FDGHBBCHG', NULL, NULL);
/*!40000 ALTER TABLE `post_tag` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `quiz_attempt`
-- =====================================================

DROP TABLE IF EXISTS `quiz_attempt`;
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


-- =====================================================
-- Table structure for table `reaction`
-- =====================================================

DROP TABLE IF EXISTS `reaction`;
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

-- Data for table `reaction`
LOCK TABLES `reaction` WRITE;
/*!40000 ALTER TABLE `reaction` DISABLE KEYS */;
INSERT INTO `reaction` VALUES ('9', '5', 'comment', '11', 'like', '2025-12-10 19:05:52', '2025-12-10 19:05:52');
/*!40000 ALTER TABLE `reaction` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `report`
-- =====================================================

DROP TABLE IF EXISTS `report`;
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


-- =====================================================
-- Table structure for table `reported_content`
-- =====================================================

DROP TABLE IF EXISTS `reported_content`;
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


-- =====================================================
-- Table structure for table `saved_post`
-- =====================================================

DROP TABLE IF EXISTS `saved_post`;
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

-- Data for table `saved_post`
LOCK TABLES `saved_post` WRITE;
/*!40000 ALTER TABLE `saved_post` DISABLE KEYS */;
INSERT INTO `saved_post` VALUES ('7', '5', '11', '2025-12-10 15:39:37', '2025-12-10 15:39:37'),
('8', '5', '12', '2025-12-10 15:46:05', '2025-12-10 15:46:05');
/*!40000 ALTER TABLE `saved_post` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `session`
-- =====================================================

DROP TABLE IF EXISTS `session`;
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

-- Data for table `session`
LOCK TABLES `session` WRITE;
/*!40000 ALTER TABLE `session` DISABLE KEYS */;
INSERT INTO `session` VALUES ('A4UekJHNvuRiaK58dGZbkycJceZjFVXWWwcxLKTN', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Microsoft Windows 10.0.22624; en-MY) PowerShell/7.5.4', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidWdhelROS01nRm9rZDE3U1dzMDV2Y2d2STNHMVpONURHVURoSHVyRiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', '1765256232'),
('XnLhJjrwaOhz7lnmKko8CKrPc3FRNPLLch9nxNZG', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUDFBc08xVUcxbUlCQ0xVOVowMFRmUnVIQXlSUURCWThJTnZOOTh0ciI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', '1765256514');
/*!40000 ALTER TABLE `session` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `student`
-- =====================================================

DROP TABLE IF EXISTS `student`;
CREATE TABLE `student` (
  `student_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `class` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================
-- Table structure for table `student_answer`
-- =====================================================

DROP TABLE IF EXISTS `student_answer`;
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


-- =====================================================
-- Table structure for table `submission`
-- =====================================================

DROP TABLE IF EXISTS `submission`;
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

-- Data for table `submission`
LOCK TABLES `submission` WRITE;
/*!40000 ALTER TABLE `submission` DISABLE KEYS */;
INSERT INTO `submission` VALUES ('1', '5', '1', 'frsde7tyuruihji', 'storage/submissions/WvvpplFC6RCRkaMXKvJ3lQMCTQcbPcz4k1GUpaWy.docx', 'file_69328f223c6090.25315942 (1).docx', 'Graded', 'gfgfg', '90', '2025-12-11 00:44:16', '2025-12-11 00:44:33'),
('2', '3', '1', 'frsde7tyuruihji', 'storage/submissions/JfFddpj86DdoY2fdiC1u3nEN1X1DoWwoAexttqCP.docx', 'file_69328f223c6090.25315942 (1) (1).docx', 'Graded', NULL, '67', '2025-12-11 01:08:14', '2025-12-11 01:09:30');
/*!40000 ALTER TABLE `submission` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `user`
-- =====================================================

DROP TABLE IF EXISTS `user`;
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
  `first_login_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `user`
LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES ('1', 'test', 'TEST', NULL, NULL, NULL, NULL, 'test@gmail.com', NULL, '$2y$12$WzQbzSFXiSE2eZRHt4/Wdu2rOaV2EyHlw9dmW81FuPkcqCJY1Fwdq', 'student', '0', '1', '1', '1', '2025-11-20 10:56:08', NULL, '2025-11-11 11:36:37', '2025-11-11 11:36:37', NULL),
('2', 'testing', 'TESTING', NULL, NULL, NULL, NULL, 'TESTING@gmail.com', NULL, '$2y$12$OMGr6GPjT96ZVv8boH48Q.ojgKqhV7LfcQim8hUvaR4aLCw45vlN2', 'student', '0', '0', '1', '1', NULL, NULL, '2025-11-11 12:28:59', '2025-11-11 12:28:59', NULL),
('3', 'test123', 'TESTER1', NULL, NULL, NULL, NULL, 'TEST123@gmail.com', NULL, '$2y$12$ldunxriCznM.ICDABBr1U.gQY4PGr5yAMYW079ZhPCjLHjtjdIFKm', 'student', '0', '1', '1', '1', '2025-12-11 00:51:15', NULL, '2025-11-18 11:15:37', '2025-12-11 00:51:15', NULL),
('4', 'test321', 'test321', NULL, NULL, NULL, NULL, 'test321@gmail.com', NULL, '$2y$10$EXPKntT17izUvgMv1EujceaqTYIicZmOZ8IrHJxZpcZ/uPpXAnAYu', 'teacher', '0', '0', '1', '1', '2025-12-05 23:52:21', NULL, NULL, NULL, NULL),
('5', 'angchunwei6', 'Ang Chun Wei', NULL, NULL, NULL, '/uploads/avatars/2025/12/avatar_5_69441c777a230.jpg', 'angchunwei6@gmail.com', NULL, '$2y$12$I7YD4AirJJKLRd.28fWu3ePOgdQm1Bo91.oky5UGpM2zO0Q66H9fa', 'teacher', '0', '1', '0', '1', '2025-12-20 07:57:54', NULL, '2025-12-10 04:29:17', '2025-12-20 07:57:54', NULL),
('6', 'angchunwei', 'ANG STUDENT', NULL, NULL, NULL, NULL, 'angchunwei@graduate.utm.my', NULL, '$2y$12$LXjnDlCujvW9uHGP3U9glOWf90Me9mCKYI/PLr3XF7eN81wje.Meu', 'student', '0', '0', '1', '1', '2025-12-19 23:56:57', NULL, '2025-12-19 23:55:51', '2025-12-19 23:56:57', NULL),
('7', 'angchunwei13', 'CIKGU ANG', NULL, NULL, NULL, NULL, 'angchunwei13@gmail.com', NULL, '$2y$12$RU3ywEOl8BcsoouRuG7hjOIbMe0EVQUNgbAuIsMMsjtFjRZihQ4Wq', 'teacher', '0', '0', '1', '1', NULL, NULL, '2025-12-20 00:26:04', '2025-12-20 00:26:04', NULL),
('8', 'raudhahmzn', 'raudhah', NULL, NULL, NULL, NULL, 'raudhahmzn@gmail.com', NULL, '$2y$12$Co8vpa0tQSfmD4e0YkWytuguG7luqCvVccr/FhWAb7oZNlEdkLWM2', 'teacher', '0', '1', '1', '1', '2025-12-20 07:25:56', NULL, '2025-12-20 07:25:50', '2025-12-20 07:25:56', NULL),
('9', 'mintybluebell', 'mintybluebell', NULL, NULL, NULL, NULL, 'mintybluebell@gmail.com', NULL, '$2y$12$9fTCU5EWl367dkbtSMZh/.Rkc95Ua/zyi8PF2oT/7q7fdE8xuI0aq', 'student', '0', '1', '1', '1', '2025-12-20 07:59:42', NULL, '2025-12-20 07:27:14', '2025-12-20 12:49:03', '2025-12-20 12:49:03');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;


-- =====================================================
-- Table structure for table `user_badge`
-- =====================================================

DROP TABLE IF EXISTS `user_badge`;
CREATE TABLE `user_badge` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `badge_code` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'locked',
  `earned_at` timestamp NULL DEFAULT NULL,
  `redeemed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_badges_user_id_index` (`user_id`),
  KEY `user_badges_badge_code_index` (`badge_code`),
  CONSTRAINT `user_badges_badge_code_foreign` FOREIGN KEY (`badge_code`) REFERENCES `badge` (`code`) ON DELETE CASCADE,
  CONSTRAINT `user_badges_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Data for table `user_badge`
LOCK TABLES `user_badge` WRITE;
/*!40000 ALTER TABLE `user_badge` DISABLE KEYS */;
INSERT INTO `user_badge` VALUES ('1', '9', 'pendatang_baru', 'earned', '2025-12-20 12:49:03', NULL, '2025-12-20 12:49:03', '2025-12-20 12:49:03');
/*!40000 ALTER TABLE `user_badge` ENABLE KEYS */;
UNLOCK TABLES;


SET FOREIGN_KEY_CHECKS=1;
