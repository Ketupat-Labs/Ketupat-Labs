-- Badge Data Export

INSERT INTO `badge_categories` (`code`, `name`, `description`, `icon`, `color`) VALUES
('general', 'Umum', 'Lencana untuk pencapaian umum.', 'fas fa-globe', '#3B82F6') ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), icon=VALUES(icon), color=VALUES(color);

INSERT INTO `badge_categories` (`code`, `name`, `description`, `icon`, `color`) VALUES
('skill', 'Kemahiran', 'Lencana untuk penguasaan kemahiran spesifik.', 'fas fa-tools', '#10B981') ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), icon=VALUES(icon), color=VALUES(color);

INSERT INTO `badge_categories` (`code`, `name`, `description`, `icon`, `color`) VALUES
('special', 'Pencapaian Khas', 'Lencana untuk pencapaian luar biasa.', 'fas fa-star', '#F59E0B') ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), icon=VALUES(icon), color=VALUES(color);

INSERT INTO `badge_categories` (`code`, `name`, `description`, `icon`, `color`) VALUES
('social', 'Sosial', 'Lencana untuk interaksi komuniti.', 'fas fa-users', '#EC4899') ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), icon=VALUES(icon), color=VALUES(color);

-- Badges (Assuming category IDs are mapped correctly or using slug/code lookups if possible, but raw SQL usually needs IDs.
-- Since we are concatenating, we might not know IDs.
-- However, we can use INSERT INTO ... SELECT.

INSERT INTO `badges` (`code`, `name`, `description`, `category_slug`, `category_id`, `icon`, `requirement_type`, `requirement_value`, `color`, `xp_reward`) 
SELECT 'newcomer', 'Pendatang Baru', 'Selamat datang ke komuniti kami! Anda telah mendaftar masuk buat kali pertama.', 'general', id, 'fas fa-door-open', 'login', 1, '#3B82F6', 50 FROM `badge_categories` WHERE `code` = 'general'
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO `badges` (`code`, `name`, `description`, `category_slug`, `category_id`, `icon`, `requirement_type`, `requirement_value`, `color`, `xp_reward`) 
SELECT 'explorer', 'Penjelajah', 'Melawat 5 halaman berbeza dalam satu sesi.', 'general', id, 'fas fa-compass', 'visit', 5, '#3B82F6', 100 FROM `badge_categories` WHERE `code` = 'general'
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO `badges` (`code`, `name`, `description`, `category_slug`, `category_id`, `icon`, `requirement_type`, `requirement_value`, `color`, `xp_reward`) 
SELECT 'quiz_master', 'Pakar Kuiz', 'Mendapat markah penuh dalam 3 kuiz berturut-turut.', 'skill', id, 'fas fa-brain', 'quiz_score', 3, '#10B981', 200 FROM `badge_categories` WHERE `code` = 'skill'
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO `badges` (`code`, `name`, `description`, `category_slug`, `category_id`, `icon`, `requirement_type`, `requirement_value`, `color`, `xp_reward`) 
SELECT 'coder', 'Pengaturcara Muda', 'Menyiapkan tugasan pengaturcaraan pertama.', 'skill', id, 'fas fa-laptop-code', 'assignment', 1, '#10B981', 150 FROM `badge_categories` WHERE `code` = 'skill'
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Social
INSERT INTO `badges` (`code`, `name`, `description`, `category_slug`, `category_id`, `icon`, `requirement_type`, `requirement_value`, `color`, `xp_reward`) 
SELECT 'friendly', 'Rakan Baik', 'Mempunyai 5 rakan yang disahkan.', 'social', id, 'fas fa-smile', 'friends', 5, '#EC4899', 100 FROM `badge_categories` WHERE `code` = 'social'
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO `badges` (`code`, `name`, `description`, `category_slug`, `category_id`, `icon`, `requirement_type`, `requirement_value`, `color`, `xp_reward`) 
SELECT 'helper', 'Pembantu', 'Menjawab 10 soalan di forum.', 'social', id, 'fas fa-hands-helping', 'forum_reply', 10, '#EC4899', 300 FROM `badge_categories` WHERE `code` = 'social'
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Special
INSERT INTO `badges` (`code`, `name`, `description`, `category_slug`, `category_id`, `icon`, `requirement_type`, `requirement_value`, `color`, `xp_reward`) 
SELECT 'champion', 'Juara Kelas', 'Mendapat tempat pertama dalam papan pendahulu mingguan.', 'special', id, 'fas fa-trophy', 'leaderboard', 1, '#F59E0B', 500 FROM `badge_categories` WHERE `code` = 'special'
ON DUPLICATE KEY UPDATE name=VALUES(name);
