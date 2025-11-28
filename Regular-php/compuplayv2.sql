-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2025 at 03:42 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `compuplayv2`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `topic` varchar(255) NOT NULL,
  `teacher_id` bigint(20) UNSIGNED NOT NULL,
  `duration` int(11) DEFAULT NULL,
  `material_path` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`id`, `title`, `topic`, `teacher_id`, `duration`, `material_path`, `is_published`, `created_at`, `updated_at`) VALUES
(1, 'Introduction to Interaction Design', 'HCI', 1, 12, 'storage/lessons/mRVsD5w7wxQl6lI0CxAZuafJz34KP3a1UMfhakrp.pdf', 1, '2025-11-10 19:37:01', '2025-11-10 19:37:01');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2025_11_10_000000_create_users_table', 1),
(2, '2025_11_10_145900_create_sessions_table', 1),
(3, '2025_11_11_023241_create_cache_table', 1),
(4, '2025_11_11_030627_create_quiz_attempts_table', 1),
(5, '2025_11_11_030640_create_submissions_table', 1),
(6, '2025_11_11_030648_add_points_to_users_table', 1),
(7, '2025_11_11_030755_create_lessons_table', 1),
(8, '2025_11_11_031426_add_foreign_key_to_quiz_attempts_lesson_id', 1),
(9, '2025_11_12_153138_add_additional_fields_to_users_table', 2);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_attempts`
--

CREATE TABLE `quiz_attempts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `lesson_id` bigint(20) UNSIGNED DEFAULT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `total_questions` int(11) NOT NULL DEFAULT 0,
  `points_earned` int(11) NOT NULL DEFAULT 0,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers`)),
  `submitted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('E4BcAclbbjCuyLuk7WXekvk50uYTpoZadARFTLO8', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YToxMjp7czo2OiJfdG9rZW4iO3M6NDA6IkhoOVFGcjZYQ2lseHlFWW55Z3FNU3BINzVzeVdYcU53VktOeWlZZloiO3M6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjIxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAiO3M6NToicm91dGUiO3M6NToiaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjc6InVzZXJfaWQiO2k6MztzOjEwOiJ1c2VyX2VtYWlsIjtzOjExOiJoQGdtYWlsLmNvbSI7czo5OiJ1c2VyX25hbWUiO3M6NToiaGF6aXEiO3M6ODoidXNlcm5hbWUiO3M6MToiaCI7czo5OiJ1c2VyX3JvbGUiO3M6NToiY2lrZ3UiO3M6MTI6InVzZXJfcm9sZV9kYiI7czo3OiJ0ZWFjaGVyIjtzOjE0OiJ1c2VyX2xvZ2dlZF9pbiI7YjoxO3M6MTA6ImF2YXRhcl91cmwiO047czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMjoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZD8iO319', 1763608699),
('FCQeGot9IhwHiQTjdEVxQATkP3rrHsEZPoVakb6J', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YToxMjp7czo2OiJfdG9rZW4iO3M6NDA6IkcxNEVuNXRGUWxkeTQ3Y3k5NUpPTmhKRDh2dGMwRGR0b29oV1pEZlQiO3M6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjc6InVzZXJfaWQiO2k6MztzOjEwOiJ1c2VyX2VtYWlsIjtzOjExOiJoQGdtYWlsLmNvbSI7czo5OiJ1c2VyX25hbWUiO3M6NToiaGF6aXEiO3M6ODoidXNlcm5hbWUiO3M6MToiaCI7czo5OiJ1c2VyX3JvbGUiO3M6NToiY2lrZ3UiO3M6MTI6InVzZXJfcm9sZV9kYiI7czo3OiJ0ZWFjaGVyIjtzOjE0OiJ1c2VyX2xvZ2dlZF9pbiI7YjoxO3M6MTA6ImF2YXRhcl91cmwiO047czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fX0=', 1763388531),
('GanLLAa6190ldmcYvG5AGExfVDho6ENEKwPPYPPH', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiajNjZ2xhNmlFcEhFUDZpRWZpaUR4SUtEQWJYYnZBMTJ6amFWQk1ibiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo1OiJpbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1763571408),
('jUBW4a0aRALWpeE6aB87rUzot4aBRPumYatR4u6t', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YToxMTp7czo2OiJfdG9rZW4iO3M6NDA6IkNmNEJkS0cxS2R0eklkcFJtOThrZXJ2cjY1VWs0RXkyVWdaSzhpcm8iO3M6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI5OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbGVzc29ucyI7czo1OiJyb3V0ZSI7czoxMzoibGVzc29ucy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NzoidXNlcl9pZCI7aTozO3M6MTA6InVzZXJfZW1haWwiO3M6MTE6ImhAZ21haWwuY29tIjtzOjk6InVzZXJfbmFtZSI7czo1OiJoYXppcSI7czo4OiJ1c2VybmFtZSI7czoxOiJoIjtzOjk6InVzZXJfcm9sZSI7czo1OiJjaWtndSI7czoxMjoidXNlcl9yb2xlX2RiIjtzOjc6InRlYWNoZXIiO3M6MTQ6InVzZXJfbG9nZ2VkX2luIjtiOjE7czoxMDoiYXZhdGFyX3VybCI7Tjt9', 1763437299),
('pxgZK2iCF3PicpFwU8voOZwXGPwA00cvUcdfJipQ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YToxMjp7czo2OiJfdG9rZW4iO3M6NDA6Imc3U0g2Z0NxMHdHMlJUSlg3ZFlzQmZuU0xtMzJITVo0c01pS2wzZ0siO3M6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjI3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvbG9naW4iO3M6NToicm91dGUiO3M6NToibG9naW4iO31zOjc6InVzZXJfaWQiO2k6MztzOjEwOiJ1c2VyX2VtYWlsIjtzOjExOiJoQGdtYWlsLmNvbSI7czo5OiJ1c2VyX25hbWUiO3M6NToiaGF6aXEiO3M6ODoidXNlcm5hbWUiO3M6MToiaCI7czo5OiJ1c2VyX3JvbGUiO3M6NToiY2lrZ3UiO3M6MTI6InVzZXJfcm9sZV9kYiI7czo3OiJ0ZWFjaGVyIjtzOjE0OiJ1c2VyX2xvZ2dlZF9pbiI7YjoxO3M6MTA6ImF2YXRhcl91cmwiO047czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2Rhc2hib2FyZCI7fX0=', 1762964613),
('QT75YqCwe4vTd7DIv7uyrRY1UZaeZ8jTUnZPhGmw', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', 'YToxMjp7czo2OiJfdG9rZW4iO3M6NDA6IlZnSVhEaTZKQXBNeG9ka1JQVGNRb3E2Wmx6dEpEYVhaemNRU05oSloiO3M6OToiX3ByZXZpb3VzIjthOjI6e3M6MzoidXJsIjtzOjMxOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvZGFzaGJvYXJkIjtzOjU6InJvdXRlIjtzOjk6ImRhc2hib2FyZCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NzoidXNlcl9pZCI7aTo0O3M6MTA6InVzZXJfZW1haWwiO3M6MTE6InFAZ21haWwuY29tIjtzOjk6InVzZXJfbmFtZSI7czo2OiJIYXFlZW0iO3M6ODoidXNlcm5hbWUiO3M6MToicSI7czo5OiJ1c2VyX3JvbGUiO3M6NToiY2lrZ3UiO3M6MTI6InVzZXJfcm9sZV9kYiI7czo3OiJ0ZWFjaGVyIjtzOjE0OiJ1c2VyX2xvZ2dlZF9pbiI7YjoxO3M6MTA6ImF2YXRhcl91cmwiO047czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozMjoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL3N1Ym1pc3Npb24iO319', 1764081651);

-- --------------------------------------------------------

--
-- Table structure for table `submissions`
--

CREATE TABLE `submissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `assignment_name` varchar(255) NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Not Submitted',
  `feedback` text DEFAULT NULL,
  `grade` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'student',
  `points` int(11) NOT NULL DEFAULT 0,
  `is_online` tinyint(1) NOT NULL DEFAULT 0,
  `last_seen` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `username`, `avatar_url`, `email_verified_at`, `password`, `role`, `points`, `is_online`, `last_seen`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'test', 'test@gmail.com', NULL, NULL, NULL, '$2y$12$m5axOFW57Pjcg1e/xhKeKO5mf9Urfe0vRDidee0I0Z7WF0eOsN6rG', 'student', 0, 0, NULL, NULL, '2025-11-10 19:19:15', '2025-11-10 19:19:15'),
(2, 'haziq', 'mhmohdhafizal@graduate.utm.my', NULL, NULL, NULL, '$2y$12$erDPoIxvIf8BfecpPSr.UuajXRQQyJie29UfWPrbYH9po53VimJnW', 'student', 0, 1, '2025-11-12 08:22:04', NULL, '2025-11-10 20:25:13', '2025-11-10 20:25:13'),
(3, 'haziq', 'h@gmail.com', 'h', NULL, NULL, '$2y$12$vIsp7Hh2H7Woru5a/neAUuG2MxkHEM8qSLR/BgEMwUx4ZwJLio.7O', 'teacher', 0, 1, '2025-11-25 06:10:14', NULL, '2025-11-12 07:43:26', '2025-11-12 07:43:26'),
(4, 'Haqeem', 'q@gmail.com', 'q', NULL, NULL, '$2y$12$8R4qkgfLMJ.xoPpH1Ujs6uLR2eYkOa8DGSfFhWYRjU9xV9zcL41d6', 'teacher', 0, 1, '2025-11-25 06:40:49', NULL, '2025-11-25 06:11:32', '2025-11-25 06:11:32'),
(5, 'Mrs Meow', 'm@gmail.com', 'm', NULL, NULL, '$2y$12$MQ8M.xB.uH9XptfnmRaEaef0KevvGSVKPWxw3Z3yxAyHFMOUZLL3u', 'student', 0, 1, '2025-11-25 06:17:10', NULL, '2025-11-25 06:17:02', '2025-11-25 06:17:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lessons_teacher_id_foreign` (`teacher_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quiz_attempts_user_id_foreign` (`user_id`),
  ADD KEY `quiz_attempts_lesson_id_foreign` (`lesson_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `submissions`
--
ALTER TABLE `submissions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `submissions_user_id_foreign` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `submissions`
--
ALTER TABLE `submissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lessons`
--
ALTER TABLE `lessons`
  ADD CONSTRAINT `lessons_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_attempts`
--
ALTER TABLE `quiz_attempts`
  ADD CONSTRAINT `quiz_attempts_lesson_id_foreign` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `quiz_attempts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `submissions`
--
ALTER TABLE `submissions`
  ADD CONSTRAINT `submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
