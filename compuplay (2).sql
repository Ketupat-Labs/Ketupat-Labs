-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- 主機： 127.0.0.1
-- 產生時間： 2025 年 11 月 28 日 03:02
-- 伺服器版本： 10.4.32-MariaDB
-- PHP 版本： 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 資料庫： `compuplay`
--

-- --------------------------------------------------------

--
-- 資料表結構 `comment`
--

CREATE TABLE `comment` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `author_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `content` text NOT NULL,
  `reaction_count` int(11) NOT NULL DEFAULT 0,
  `is_edited` tinyint(1) NOT NULL DEFAULT 0,
  `edited_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `forum`
--

CREATE TABLE `forum` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `visibility` enum('public','private','class') NOT NULL DEFAULT 'public',
  `class_id` bigint(20) UNSIGNED DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','archived','deleted') NOT NULL DEFAULT 'active',
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `member_count` int(11) NOT NULL DEFAULT 0,
  `post_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `forum_member`
--

CREATE TABLE `forum_member` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `forum_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `role` enum('admin','moderator','member') NOT NULL DEFAULT 'member',
  `is_favorite` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `forum_post`
--

CREATE TABLE `forum_post` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `forum_id` bigint(20) UNSIGNED NOT NULL,
  `author_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `post_type` enum('post','poll','announcement') NOT NULL DEFAULT 'post',
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `view_count` int(11) NOT NULL DEFAULT 0,
  `reply_count` int(11) NOT NULL DEFAULT 0,
  `reaction_count` int(11) NOT NULL DEFAULT 0,
  `is_edited` tinyint(1) NOT NULL DEFAULT 0,
  `edited_at` timestamp NULL DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 替換檢視表以便查看 `forum_statistics`
-- (請參考以下實際畫面)
--
CREATE TABLE `forum_statistics` (
);

-- --------------------------------------------------------

--
-- 資料表結構 `forum_tags`
--

CREATE TABLE `forum_tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `forum_id` bigint(20) UNSIGNED NOT NULL,
  `tag_name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 替換檢視表以便查看 `forum_user_activity`
-- (請參考以下實際畫面)
--
CREATE TABLE `forum_user_activity` (
);

-- --------------------------------------------------------

--
-- 資料表結構 `muted_user`
--

CREATE TABLE `muted_user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `forum_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `muted_by` bigint(20) UNSIGNED NOT NULL,
  `reason` text DEFAULT NULL,
  `muted_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `poll_option`
--

CREATE TABLE `poll_option` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `option_text` varchar(255) NOT NULL,
  `vote_count` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `poll_vote`
--

CREATE TABLE `poll_vote` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `option_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `post_attachment`
--

CREATE TABLE `post_attachment` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `file_url` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(255) DEFAULT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `post_tags`
--

CREATE TABLE `post_tags` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `tag_name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `reaction`
--

CREATE TABLE `reaction` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `target_type` enum('post','comment') NOT NULL,
  `target_id` bigint(20) UNSIGNED NOT NULL,
  `reaction_type` enum('like','love','laugh','angry','sad') NOT NULL DEFAULT 'like',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `reported_content`
--

CREATE TABLE `reported_content` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reporter_id` bigint(20) UNSIGNED NOT NULL,
  `content_type` enum('post','comment','forum') NOT NULL,
  `content_id` bigint(20) UNSIGNED NOT NULL,
  `reason` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','reviewed','resolved','dismissed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 資料表結構 `saved_post`
--

CREATE TABLE `saved_post` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `post_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 替換檢視表以便查看 `unread_message_counts`
-- (請參考以下實際畫面)
--
CREATE TABLE `unread_message_counts` (
);

-- --------------------------------------------------------

--
-- 資料表結構 `user`
--

CREATE TABLE `user` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) DEFAULT 'student',
  `is_online` tinyint(1) DEFAULT 0,
  `last_seen` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- 檢視表結構 `forum_statistics`
--
DROP TABLE IF EXISTS `forum_statistics`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `forum_statistics`  AS SELECT `f`.`id` AS `forum_id`, count(distinct `fm`.`user_id`) AS `member_count`, count(distinct `fp`.`id`) AS `post_count`, count(distinct `c`.`id`) AS `comment_count`, max(greatest(ifnull(`fp`.`updated_at`,`fp`.`created_at`),ifnull(`c`.`created_at`,`fp`.`created_at`))) AS `last_activity` FROM (((`forum` `f` left join `forum_member` `fm` on(`f`.`id` = `fm`.`forum_id`)) left join `forum_post` `fp` on(`f`.`id` = `fp`.`forum_id` and `fp`.`is_deleted` = 0)) left join `comment` `c` on(`fp`.`id` = `c`.`post_id` and `c`.`is_deleted` = 0)) GROUP BY `f`.`id` ;

-- --------------------------------------------------------

--
-- 檢視表結構 `forum_user_activity`
--
DROP TABLE IF EXISTS `forum_user_activity`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `forum_user_activity`  AS SELECT `fm`.`user_id` AS `user_id`, `fm`.`forum_id` AS `forum_id`, count(distinct `fp`.`id`) AS `posts_count`, count(distinct `c`.`id`) AS `comments_count`, count(distinct `r`.`id`) AS `reactions_count`, max(greatest(ifnull(`fp`.`created_at`,'1970-01-01'),ifnull(`c`.`created_at`,'1970-01-01'))) AS `last_activity_date` FROM (((`forum_member` `fm` left join `forum_post` `fp` on(`fm`.`forum_id` = `fp`.`forum_id` and `fp`.`author_id` = `fm`.`user_id` and `fp`.`is_deleted` = 0)) left join `comment` `c` on(`fm`.`forum_id` = (select `forum_post`.`forum_id` from `forum_post` where `forum_post`.`id` = `c`.`post_id`) and `c`.`author_id` = `fm`.`user_id` and `c`.`is_deleted` = 0)) left join `reaction` `r` on(`r`.`user_id` = `fm`.`user_id` and `r`.`target_type` in ('post','comment'))) GROUP BY `fm`.`user_id`, `fm`.`forum_id` ;

-- --------------------------------------------------------

--
-- 檢視表結構 `unread_message_counts`
--
DROP TABLE IF EXISTS `unread_message_counts`;

-- CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `unread_message_counts`  AS SELECT `cp`.`user_id` AS `user_id`, `c`.`id` AS `conversation_id`, count(`m`.`id`) AS `unread_count` FROM ((`conversation_participants` `cp` left join `conversations` `c` on(`cp`.`conversation_id` = `c`.`id`)) left join `messages` `m` on(`c`.`id` = `m`.`conversation_id` and `m`.`sender_id` <> `cp`.`user_id` and `m`.`created_at` > `cp`.`last_read_at` and `m`.`is_deleted` = 0)) GROUP BY `cp`.`user_id`, `c`.`id` ;

--
-- 已傾印資料表的索引
--

--
-- 資料表索引 `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comments_parent_id_foreign` (`parent_id`),
  ADD KEY `comments_post_id_is_deleted_parent_id_index` (`post_id`,`is_deleted`,`parent_id`),
  ADD KEY `comments_author_id_index` (`author_id`);

--
-- 資料表索引 `forum`
--
ALTER TABLE `forum`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forums_status_is_pinned_index` (`status`,`is_pinned`),
  ADD KEY `forums_created_by_index` (`created_by`);

--
-- 資料表索引 `forum_member`
--
ALTER TABLE `forum_member`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `forum_members_forum_id_user_id_unique` (`forum_id`,`user_id`),
  ADD KEY `forum_members_user_id_index` (`user_id`);

--
-- 資料表索引 `forum_post`
--
ALTER TABLE `forum_post`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forum_posts_forum_id_is_deleted_is_pinned_index` (`forum_id`,`is_deleted`,`is_pinned`),
  ADD KEY `forum_posts_author_id_index` (`author_id`),
  ADD KEY `forum_posts_created_at_index` (`created_at`);

--
-- 資料表索引 `forum_tags`
--
ALTER TABLE `forum_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `forum_tags_forum_id_tag_name_index` (`forum_id`,`tag_name`);

--
-- 資料表索引 `muted_user`
--
ALTER TABLE `muted_user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `muted_users_forum_id_user_id_unique` (`forum_id`,`user_id`),
  ADD KEY `muted_users_muted_by_foreign` (`muted_by`),
  ADD KEY `muted_users_user_id_index` (`user_id`);

--
-- 資料表索引 `poll_option`
--
ALTER TABLE `poll_option`
  ADD PRIMARY KEY (`id`),
  ADD KEY `poll_options_post_id_index` (`post_id`);

--
-- 資料表索引 `poll_vote`
--
ALTER TABLE `poll_vote`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `poll_votes_post_id_user_id_unique` (`post_id`,`user_id`),
  ADD KEY `poll_votes_user_id_foreign` (`user_id`),
  ADD KEY `poll_votes_option_id_index` (`option_id`);

--
-- 資料表索引 `post_attachment`
--
ALTER TABLE `post_attachment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_attachments_post_id_index` (`post_id`);

--
-- 資料表索引 `post_tags`
--
ALTER TABLE `post_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_tags_post_id_tag_name_index` (`post_id`,`tag_name`);

--
-- 資料表索引 `reaction`
--
ALTER TABLE `reaction`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reactions_user_id_target_type_target_id_unique` (`user_id`,`target_type`,`target_id`),
  ADD KEY `reactions_target_type_target_id_index` (`target_type`,`target_id`);

--
-- 資料表索引 `reported_content`
--
ALTER TABLE `reported_content`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reported_content_content_type_content_id_index` (`content_type`,`content_id`),
  ADD KEY `reported_content_reporter_id_index` (`reporter_id`);

--
-- 資料表索引 `saved_post`
--
ALTER TABLE `saved_post`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `saved_posts_user_id_post_id_unique` (`user_id`,`post_id`),
  ADD KEY `saved_posts_post_id_foreign` (`post_id`),
  ADD KEY `saved_posts_user_id_index` (`user_id`);

--
-- 資料表索引 `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- 在傾印的資料表使用自動遞增(AUTO_INCREMENT)
--

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `comment`
--
ALTER TABLE `comment`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `forum`
--
ALTER TABLE `forum`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `forum_member`
--
ALTER TABLE `forum_member`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `forum_post`
--
ALTER TABLE `forum_post`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `forum_tags`
--
ALTER TABLE `forum_tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `muted_user`
--
ALTER TABLE `muted_user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `poll_option`
--
ALTER TABLE `poll_option`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `poll_vote`
--
ALTER TABLE `poll_vote`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `post_attachment`
--
ALTER TABLE `post_attachment`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `post_tags`
--
ALTER TABLE `post_tags`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `reaction`
--
ALTER TABLE `reaction`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `reported_content`
--
ALTER TABLE `reported_content`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `saved_post`
--
ALTER TABLE `saved_post`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 使用資料表自動遞增(AUTO_INCREMENT) `user`
--
ALTER TABLE `user`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- 已傾印資料表的限制式
--

--
-- 資料表的限制式 `comment`
--
ALTER TABLE `comment`
  ADD CONSTRAINT `comments_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `comment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `forum`
--
ALTER TABLE `forum`
  ADD CONSTRAINT `forums_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `forum_member`
--
ALTER TABLE `forum_member`
  ADD CONSTRAINT `forum_members_forum_id_foreign` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `forum_post`
--
ALTER TABLE `forum_post`
  ADD CONSTRAINT `forum_posts_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `forum_posts_forum_id_foreign` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `forum_tags`
--
ALTER TABLE `forum_tags`
  ADD CONSTRAINT `forum_tags_forum_id_foreign` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `muted_user`
--
ALTER TABLE `muted_user`
  ADD CONSTRAINT `muted_users_forum_id_foreign` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `muted_users_muted_by_foreign` FOREIGN KEY (`muted_by`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `muted_users_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `poll_option`
--
ALTER TABLE `poll_option`
  ADD CONSTRAINT `poll_options_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `poll_vote`
--
ALTER TABLE `poll_vote`
  ADD CONSTRAINT `poll_votes_option_id_foreign` FOREIGN KEY (`option_id`) REFERENCES `poll_option` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `poll_votes_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `poll_votes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `post_attachment`
--
ALTER TABLE `post_attachment`
  ADD CONSTRAINT `post_attachments_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `post_tags`
--
ALTER TABLE `post_tags`
  ADD CONSTRAINT `post_tags_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `reaction`
--
ALTER TABLE `reaction`
  ADD CONSTRAINT `reactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `reported_content`
--
ALTER TABLE `reported_content`
  ADD CONSTRAINT `reported_content_reporter_id_foreign` FOREIGN KEY (`reporter_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- 資料表的限制式 `saved_post`
--
ALTER TABLE `saved_post`
  ADD CONSTRAINT `saved_posts_post_id_foreign` FOREIGN KEY (`post_id`) REFERENCES `forum_post` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `saved_posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
