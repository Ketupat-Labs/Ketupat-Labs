-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 10, 2025 at 05:30 PM
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
-- Database: `compuplay`
--

-- --------------------------------------------------------

--
-- Table structure for table `badges`
--

CREATE TABLE `badges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `category_slug` varchar(255) NOT NULL,
  `icon` varchar(255) NOT NULL,
  `requirement_type` varchar(255) NOT NULL,
  `requirement_value` int(11) NOT NULL,
  `color` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `xp_reward` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `badges`
--

INSERT INTO `badges` (`id`, `code`, `name`, `description`, `category_slug`, `icon`, `requirement_type`, `requirement_value`, `color`, `created_at`, `updated_at`, `category_id`, `xp_reward`) VALUES
(1, '', 'Konsistensi', 'Memahami dan menerapkan konsistensi dalam antaramuka', 'keperluan', 'fas fa-check', 'points', 50, '#1abc9c', '2025-12-09 19:35:37', '2025-12-09 19:35:37', 1, 10),
(2, '', 'Kebolehan Membuat Pemerhatian', 'Merekabentuk elemen yang mudah diperhatikan', 'keperluan', 'fas fa-eye', 'points', 30, '#3498db', '2025-12-09 19:35:37', '2025-12-09 19:35:37', 1, 5),
(3, '', 'HCI Asas', 'Memahami asas interaksi manusia-komputer', 'reka', 'fas fa-laptop', 'points', 40, '#9b59b6', '2025-12-10 03:47:39', '2025-12-10 03:47:39', 2, 8),
(6, '', 'Konsistensi', 'Menerapkan prinsip konsistensi dalam reka bentuk antaramuka.', 'keperluan', 'fas fa-align-left', 'xp', 50, '#1abc9c', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 1, 0),
(7, '', 'Hierarki Visual', 'Menyusun elemen berdasarkan keutamaan pengguna.', 'keperluan', 'fas fa-layer-group', 'xp', 80, '#16a085', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 1, 0),
(8, '', 'Keterlihatan', 'Memastikan elemen penting mudah dilihat dan diakses.', 'keperluan', 'fas fa-eye', 'xp', 120, '#0a6e5c', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 1, 0),
(9, '', 'Pemahaman Pengguna', 'Mengenal pasti keperluan dan tingkah laku pengguna.', 'reka', 'fas fa-users', 'xp', 60, '#3498db', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 2, 0),
(10, '', 'Analisis Keperluan', 'Menjana dan mengumpul keperluan pengguna.', 'reka', 'fas fa-list-check', 'xp', 90, '#2980b9', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 2, 0),
(11, '', 'Senario Pengguna', 'Membina senario penggunaan untuk meramalkan interaksi.', 'reka', 'fas fa-clipboard-list', 'xp', 130, '#2471a3', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 2, 0),
(12, '', 'Heuristik Asas', 'Mengaplikasikan heuristik Nielsen dalam penilaian.', 'penilaian', 'fas fa-flask', 'xp', 50, '#9b59b6', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 4, 0),
(13, '', 'Ujian Kebolehgunaan', 'Menjalankan ujian untuk mengesan masalah pengguna.', 'penilaian', 'fas fa-user-check', 'xp', 100, '#8e44ad', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 4, 0),
(14, '', 'Analisis Maklum Balas', 'Menilai maklum balas pengguna untuk penambahbaikan.', 'penilaian', 'fas fa-comments', 'xp', 150, '#6c3483', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 4, 0),
(15, '', 'Lakaran Wireframe', 'Membina lakaran awal antaramuka.', 'prototaip', 'fas fa-pencil-ruler', 'xp', 40, '#e67e22', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 3, 0),
(16, '', 'Prototip Rendah Ketepatan', 'Mewakili idea asas sebelum binaan visual penuh.', 'prototaip', 'fas fa-drafting-compass', 'xp', 70, '#d35400', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 3, 0),
(17, '', 'Prototip Tinggi Ketepatan', 'Menghasilkan prototaip interaktif hampir produk sebenar.', 'prototaip', 'fas fa-laptop-code', 'xp', 120, '#ba4a00', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 3, 0),
(18, '', 'Navigasi Mudah', 'Membina navigasi yang jelas dan tidak mengelirukan.', 'projek', 'fas fa-compass', 'xp', 40, '#27ae60', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 5, 0),
(19, '', 'Reka Letak Efisien', 'Menyusun kandungan untuk meningkatkan aliran penggunaan.', 'projek', 'fas fa-border-all', 'xp', 70, '#229954', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 5, 0),
(20, '', 'Kemudahan Akses', 'Menambah ciri mesra OKU seperti kontras tinggi.', 'projek', 'fas fa-universal-access', 'xp', 110, '#1e8449', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 5, 0),
(21, '', 'Reka Bentuk Kreatif', 'Menghasilkan idea inovatif dalam antaramuka.', 'inovasi', 'fas fa-lightbulb', 'xp', 50, '#f1c40f', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 6, 0),
(22, '', 'Teknologi Baharu', 'Menggunakan AR/VR atau teknologi moden dalam UI.', 'inovasi', 'fas fa-vr-cardboard', 'xp', 100, '#f39c12', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 6, 0),
(23, '', 'Eksperimen UI', 'Mencuba konsep UI baharu yang unik dan kreatif.', 'inovasi', 'fas fa-flask-vial', 'xp', 140, '#d68910', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 6, 0),
(24, '', 'Penyelesaian Masalah', 'Mengenal pasti dan menyelesaikan isu pengguna.', 'solusi', 'fas fa-tools', 'xp', 60, '#c0392b', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 7, 0),
(26, '', 'Iterasi Berterusan', 'Menambah baik reka bentuk melalui proses iterasi.', 'solusi', 'fas fa-sync', 'xp', 130, '#922b21', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 7, 0),
(27, '', 'Privasi Pengguna', 'Menjaga keselamatan dan privasi maklumat pengguna.', 'etika', 'fas fa-shield-alt', 'xp', 50, '#7f8c8d', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 8, 0),
(28, '', 'Reka Bentuk Beretika', 'Mengelakkan dark patterns dalam antaramuka.', 'etika', 'fas fa-balance-scale', 'xp', 80, '#626567', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 8, 0),
(29, '', 'Keterangkuman', 'Membina reka bentuk inklusif untuk semua.', 'etika', 'fas fa-people-group', 'xp', 120, '#4d5656', '2025-12-10 07:30:19', '2025-12-10 07:30:19', 8, 0),
(30, '', 'Pengenal Keperluan', 'Mengenal pasti keperluan pengguna', 'keperluan', 'fas fa-search', 'xp', 50, '#1abc9c', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 1, 0),
(31, '', 'Penyelidik Pasaran', 'Analisis permintaan pasaran', 'keperluan', 'fas fa-chart-line', 'xp', 60, '#16a085', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 1, 0),
(32, '', 'Analisis Produktiviti', 'Kaji peningkatan produktiviti', 'keperluan', 'fas fa-tachometer-alt', 'xp', 70, '#0a6e5c', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 1, 0),
(33, '', 'Pakar Kos', 'Kaji pengurangan kos', 'keperluan', 'fas fa-wallet', 'xp', 80, '#27ae60', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 1, 0),
(34, '', 'Pengembang Aktiviti', 'Kembangkan aktiviti manusia', 'keperluan', 'fas fa-running', 'xp', 90, '#2ecc71', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 1, 0),
(35, '', 'Analis Pengkomputeran Sosial', 'Fahami interaksi sosial melalui komputer', 'keperluan', 'fas fa-users', 'xp', 100, '#1e8449', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 1, 0),
(36, '', 'Penyiasat Sasaran', 'Kenal pasti kumpulan sasaran', 'keperluan', 'fas fa-bullseye', 'xp', 110, '#145a32', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 1, 0),
(37, '', 'Pakar Konsistensi', 'Pastikan reka bentuk tekal', 'reka', 'fas fa-check', 'xp', 50, '#f39c12', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 2, 0),
(38, '', 'Pemerhati Tajam', 'Pastikan elemen mudah diperhati', 'reka', 'fas fa-eye', 'xp', 60, '#d35400', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 2, 0),
(39, '', 'Pembelajar Cepat', 'Reka bentuk mudah dipelajari', 'reka', 'fas fa-book-reader', 'xp', 70, '#e67e22', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 2, 0),
(40, '', 'Peramal Interaksi', 'Bina sistem boleh dijangka', 'reka', 'fas fa-project-diagram', 'xp', 80, '#e59866', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 2, 0),
(41, '', 'Pemberi Maklum Balas', 'Sistem beri maklum balas jelas', 'reka', 'fas fa-comment-dots', 'xp', 90, '#f1c40f', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 2, 0),
(42, '', 'Pereka Antara Muka', 'Reka bentuk skrin menarik', 'reka', 'fas fa-desktop', 'xp', 100, '#f39c12', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 2, 0),
(43, '', 'Penyusun Elemen', 'Susun atur teks, gambar, butang', 'reka', 'fas fa-th', 'xp', 110, '#d68910', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 2, 0),
(44, '', 'Pembina Lakaran', 'Hasilkan lakaran awal', 'prototaip', 'fas fa-pencil-ruler', 'xp', 50, '#3498db', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 3, 0),
(45, '', 'Jurutera Prototaip', 'Bina prototaip interaktif', 'prototaip', 'fas fa-laptop-code', 'xp', 60, '#2980b9', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 3, 0),
(46, '', 'Pengaturcara Java', 'Bangun atur cara Java', 'prototaip', 'fab fa-java', 'xp', 70, '#2471a3', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 3, 0),
(47, '', 'Pakar NetBeans', 'Mahir guna NetBeans', 'prototaip', 'fas fa-cogs', 'xp', 80, '#1f618d', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 3, 0),
(48, '', 'Penyunting Warna', 'Pilih warna sesuai', 'prototaip', 'fas fa-palette', 'xp', 90, '#5dade2', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 3, 0),
(49, '', 'Penyusun Kod', 'Susun kod atur cara', 'prototaip', 'fas fa-code', 'xp', 100, '#2e86c1', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 3, 0),
(50, '', 'Pereka GUI', 'Reka antara muka grafik', 'prototaip', 'fas fa-object-group', 'xp', 110, '#2874a6', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 3, 0),
(51, '', 'Penilai Produk Interaktif', 'Nilai produk sedia ada', 'penilaian', 'fas fa-star', 'xp', 50, '#9b59b6', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 4, 0),
(52, '', 'Pembina Soal Selidik', 'Bina instrumen penilaian', 'penilaian', 'fas fa-clipboard', 'xp', 60, '#8e44ad', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 4, 0),
(53, '', 'Penganalisis Kuantitatif', 'Analisis data berangka', 'penilaian', 'fas fa-chart-pie', 'xp', 70, '#6c3483', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 4, 0),
(54, '', 'Penguji Kebolehgunaan', 'Uji kemudahan penggunaan', 'penilaian', 'fas fa-user-check', 'xp', 80, '#7d3c98', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 4, 0),
(55, '', 'Pemerhati Pengguna', 'Perhati tingkah laku pengguna', 'penilaian', 'fas fa-users', 'xp', 90, '#5b2c6f', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 4, 0),
(56, '', 'Penyelidik Maklum Balas', 'Kumpul & analisis maklum balas', 'penilaian', 'fas fa-comments', 'xp', 100, '#4a235a', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 4, 0),
(57, '', 'Penilai Heuristik', 'Gunakan kaedah penilaian heuristik', 'penilaian', 'fas fa-search', 'xp', 110, '#3c1361', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 4, 0),
(58, '', 'Pereka Permainan', 'Bina permainan interaktif', 'projek', 'fas fa-gamepad', 'xp', 50, '#e74c3c', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 5, 0),
(59, '', 'Penyelesai ATM', 'Analisis & baiki sistem ATM', 'projek', 'fas fa-university', 'xp', 60, '#c0392b', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 5, 0),
(60, '', 'Pakar Aplikasi Sosial', 'Fahami aplikasi Facebook/Instagram', 'projek', 'fas fa-hashtag', 'xp', 70, '#a93226', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 5, 0),
(61, '', 'Pengurus Projek HCI', 'Urus projek interaksi', 'projek', 'fas fa-tasks', 'xp', 80, '#922b21', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 5, 0),
(62, '', 'Penyampai Demo', 'Persembahkan prototaip', 'projek', 'fas fa-chalkboard-teacher', 'xp', 90, '#7b241c', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 5, 0),
(63, '', 'Pembaiki Produk', 'Cadang penambahbaikan', 'projek', 'fas fa-tools', 'xp', 100, '#641e16', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 5, 0),
(64, '', 'Inovator Interaksi', 'Cipta interaksi kreatif', 'projek', 'fas fa-lightbulb', 'xp', 110, '#4d140f', '2025-12-10 07:40:43', '2025-12-10 07:40:43', 5, 0),
(65, 'badge1', 'Lencana 1', 'Selesaikan cabaran 1', 'keperluan', 'fas fa-star', '', 50, '#f39c12', NULL, NULL, NULL, 10),
(66, 'badge2', 'Lencana 2', 'Selesaikan cabaran 2', 'reka', 'fas fa-gem', '', 100, '#2980b9', NULL, NULL, NULL, 20),
(67, 'badge3', 'Lencana 3', 'Selesaikan cabaran 3', 'prototaip', 'fas fa-cogs', '', 150, '#27ae60', NULL, NULL, NULL, 30);

-- --------------------------------------------------------

--
-- Table structure for table `badge_categories`
--

CREATE TABLE `badge_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `badge_categories`
--

INSERT INTO `badge_categories` (`id`, `name`, `code`, `created_at`, `updated_at`) VALUES
(1, 'Keperluan', 'keperluan', '2025-12-10 07:53:00', '2025-12-10 07:53:00'),
(2, 'Reka Bentuk', 'reka', '2025-12-10 07:53:00', '2025-12-10 07:53:00'),
(3, 'Prototaip', 'prototaip', '2025-12-10 07:53:00', '2025-12-10 07:53:00'),
(4, 'Penilaian', 'penilaian', '2025-12-10 07:53:00', '2025-12-10 07:53:00');

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
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2025_11_15_160310_create_badges_table', 1),
(4, '2025_12_10_012312_create_badge_categories_table', 1),
(5, '2025_12_10_024300_create_users_table', 1),
(6, '2025_12_10_025712_create_sessions_table', 2),
(7, '2025_12_10_063607_add_unique_code_to_badge_categories_table', 3),
(8, '2025_12_10_093543_create_user_badges_table', 4);

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
('7PEwEUps55ZiMTIvIKRWu5t9wLHx8x1H1oyufwsP', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36 Edg/143.0.0.0', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiWVVjVUlEdVEwMjl1b3dGSDFYaFBtU0UwYXFLemNCSGxPUUtzSDdheCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9iYWRnZXMiO3M6NToicm91dGUiO3M6MTI6ImJhZGdlcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NzoidXNlcl9pZCI7czoxODoidXNlcl82OTM4ZTFhYTIzZDhhIjtzOjk6ImRlbW9fbW9kZSI7YjoxO30=', 1765374742);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `xp` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `points` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `xp`, `points`) VALUES
(1, 'Test User', 'test@example.com', '2025-12-09 18:56:16', '$2y$12$hcvh9RWcoyavqt52l8LhjugYpS2tY3D6Nbmu7eaW69wTXq7KFiGAC', 'CpayZSgmnW', '2025-12-09 18:56:16', '2025-12-09 18:56:16', 200, 200);

-- --------------------------------------------------------

--
-- Table structure for table `user_badges`
--

CREATE TABLE `user_badges` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `badge_code` varchar(255) NOT NULL,
  `status` enum('locked','earned','redeemed') NOT NULL DEFAULT 'earned',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_badges`
--

INSERT INTO `user_badges` (`id`, `user_id`, `badge_code`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'keperluan', 'earned', '2025-12-10 09:40:44', '2025-12-10 09:40:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `badges_category_slug_index` (`category_slug`),
  ADD KEY `badges_requirement_type_index` (`requirement_type`);

--
-- Indexes for table `badge_categories`
--
ALTER TABLE `badge_categories`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_badges`
--
ALTER TABLE `user_badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_badges_user_id_badge_code_unique` (`user_id`,`badge_code`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `badges`
--
ALTER TABLE `badges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `badge_categories`
--
ALTER TABLE `badge_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `user_badges`
--
ALTER TABLE `user_badges`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_badges`
--
ALTER TABLE `user_badges`
  ADD CONSTRAINT `user_badges_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
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
