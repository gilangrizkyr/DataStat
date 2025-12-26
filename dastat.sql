-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 24 Des 2025 pada 10.30
-- Versi server: 11.4.2-MariaDB-log
-- Versi PHP: 8.3.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dastat`
--

DELIMITER $$
--
-- Prosedur
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_recalculate_statistic` (IN `p_statistic_id` INT)   BEGIN
    -- Logic untuk recalculate akan diimplementasikan di aplikasi
    -- Procedure ini hanya update timestamp
    UPDATE statistic_configs 
    SET last_calculated = NOW(), 
        updated_at = NOW()
    WHERE id = p_statistic_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_soft_delete_application` (IN `p_application_id` INT)   BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
    END;
    
    START TRANSACTION;
    
    -- Soft delete application
    UPDATE applications SET deleted_at = NOW() WHERE id = p_application_id;
    
    -- Soft delete related datasets
    UPDATE datasets SET deleted_at = NOW() WHERE application_id = p_application_id;
    
    -- Soft delete related statistics
    UPDATE statistic_configs SET deleted_at = NOW() WHERE application_id = p_application_id;
    
    -- Soft delete related dashboards
    UPDATE dashboards SET deleted_at = NOW() WHERE application_id = p_application_id;
    
    COMMIT;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `applications`
--

CREATE TABLE `applications` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'Owner aplikasi',
  `app_name` varchar(255) NOT NULL,
  `app_slug` varchar(255) NOT NULL,
  `bidang` varchar(100) NOT NULL COMMENT 'Bidang/domain aplikasi',
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `color_theme` varchar(50) DEFAULT 'blue',
  `is_active` tinyint(1) DEFAULT 1,
  `settings` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Pengaturan aplikasi dalam JSON' CHECK (json_valid(`settings`)),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Trigger `applications`
--
DELIMITER $$
CREATE TRIGGER `before_insert_applications` BEFORE INSERT ON `applications` FOR EACH ROW BEGIN
    IF NEW.app_slug IS NULL OR NEW.app_slug = '' THEN
        SET NEW.app_slug = LOWER(REPLACE(REPLACE(NEW.app_name, ' ', '-'), '_', '-'));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `dashboards`
--

CREATE TABLE `dashboards` (
  `id` int(10) UNSIGNED NOT NULL,
  `application_id` int(10) UNSIGNED NOT NULL,
  `dashboard_name` varchar(255) NOT NULL,
  `dashboard_slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `layout_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Konfigurasi layout dashboard (grid, position)' CHECK (json_valid(`layout_config`)),
  `is_default` tinyint(1) DEFAULT 0 COMMENT 'Dashboard default untuk aplikasi',
  `is_public` tinyint(1) DEFAULT 0 COMMENT 'Dapat diakses tanpa login',
  `access_token` varchar(100) DEFAULT NULL COMMENT 'Token untuk akses public',
  `sort_order` int(10) UNSIGNED DEFAULT 0,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Trigger `dashboards`
--
DELIMITER $$
CREATE TRIGGER `before_insert_dashboards` BEFORE INSERT ON `dashboards` FOR EACH ROW BEGIN
    IF NEW.dashboard_slug IS NULL OR NEW.dashboard_slug = '' THEN
        SET NEW.dashboard_slug = LOWER(REPLACE(REPLACE(NEW.dashboard_name, ' ', '-'), '_', '-'));
    END IF;
    
    -- Auto-generate access token untuk public dashboard
    IF NEW.is_public = 1 AND (NEW.access_token IS NULL OR NEW.access_token = '') THEN
        SET NEW.access_token = MD5(CONCAT(NEW.dashboard_name, NOW(), RAND()));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `dashboard_widgets`
--

CREATE TABLE `dashboard_widgets` (
  `id` int(10) UNSIGNED NOT NULL,
  `dashboard_id` int(10) UNSIGNED NOT NULL,
  `statistic_config_id` int(10) UNSIGNED NOT NULL,
  `widget_title` varchar(255) DEFAULT NULL COMMENT 'Override title dari statistic_config',
  `position_x` int(10) UNSIGNED DEFAULT 0,
  `position_y` int(10) UNSIGNED DEFAULT 0,
  `width` int(10) UNSIGNED DEFAULT 4 COMMENT 'Lebar dalam grid (misal 1-12)',
  `height` int(10) UNSIGNED DEFAULT 300 COMMENT 'Tinggi dalam pixel',
  `widget_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Konfigurasi tambahan widget' CHECK (json_valid(`widget_config`)),
  `sort_order` int(10) UNSIGNED DEFAULT 0,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `datasets`
--

CREATE TABLE `datasets` (
  `id` int(10) UNSIGNED NOT NULL,
  `application_id` int(10) UNSIGNED NOT NULL,
  `dataset_name` varchar(255) NOT NULL,
  `dataset_slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_path` varchar(500) NOT NULL COMMENT 'Path file Excel asli',
  `file_name` varchar(255) NOT NULL,
  `file_size` int(10) UNSIGNED NOT NULL COMMENT 'Ukuran file dalam bytes',
  `total_rows` int(10) UNSIGNED DEFAULT 0,
  `total_columns` int(10) UNSIGNED DEFAULT 0,
  `schema_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Konfigurasi schema: field, tipe data, label, validasi' CHECK (json_valid(`schema_config`)),
  `upload_status` enum('processing','completed','failed') DEFAULT 'processing',
  `error_message` text DEFAULT NULL,
  `uploaded_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Trigger `datasets`
--
DELIMITER $$
CREATE TRIGGER `before_insert_datasets` BEFORE INSERT ON `datasets` FOR EACH ROW BEGIN
    IF NEW.dataset_slug IS NULL OR NEW.dataset_slug = '' THEN
        SET NEW.dataset_slug = LOWER(REPLACE(REPLACE(NEW.dataset_name, ' ', '-'), '_', '-'));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `dataset_records`
--

CREATE TABLE `dataset_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `dataset_id` int(10) UNSIGNED NOT NULL,
  `row_num` int(10) UNSIGNED NOT NULL COMMENT 'Nomor baris dalam Excel',
  `data_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'Data row dalam format JSON' CHECK (json_valid(`data_json`)),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_activities`
--

CREATE TABLE `log_activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `application_id` int(10) UNSIGNED DEFAULT NULL,
  `activity_type` varchar(100) NOT NULL COMMENT 'login, logout, upload, create, update, delete, view, etc',
  `module` varchar(100) NOT NULL COMMENT 'dataset, statistic, dashboard, user, etc',
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `request_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Data request yang dikirim' CHECK (json_valid(`request_data`)),
  `response_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Data response' CHECK (json_valid(`response_data`)),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `log_activities`
--

INSERT INTO `log_activities` (`id`, `user_id`, `application_id`, `activity_type`, `module`, `description`, `ip_address`, `user_agent`, `request_data`, `response_data`, `created_at`) VALUES
(1, 2, NULL, 'login', 'users', 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"email\":\"superadmin@datastat.com\",\"ip_address\":\"::1\"}', NULL, '2025-12-23 14:37:56'),
(2, 2, NULL, 'logout', 'users', 'User berhasil logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', NULL, '2025-12-23 14:42:39'),
(3, 3, NULL, 'login', 'users', 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"email\":\"owner.bps@datastat.com\",\"ip_address\":\"::1\"}', NULL, '2025-12-23 14:44:09'),
(4, 3, NULL, 'logout', 'users', 'User berhasil logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', NULL, '2025-12-23 14:49:40'),
(5, 2, NULL, 'login', 'users', 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"email\":\"superadmin@datastat.com\",\"ip_address\":\"::1\"}', NULL, '2025-12-23 16:18:18'),
(6, 2, NULL, 'login', 'users', 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"email\":\"superadmin@datastat.com\",\"ip_address\":\"::1\"}', NULL, '2025-12-24 11:05:12'),
(7, 2, NULL, 'logout', 'users', 'User berhasil logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', NULL, '2025-12-24 11:34:21'),
(8, 2, NULL, 'login', 'users', 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"email\":\"superadmin@datastat.com\",\"ip_address\":\"::1\"}', NULL, '2025-12-24 11:45:34'),
(9, 2, NULL, 'logout', 'users', 'User berhasil logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', NULL, '2025-12-24 11:45:39'),
(10, 3, NULL, 'login', 'users', 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"email\":\"owner.bps@datastat.com\",\"ip_address\":\"::1\"}', NULL, '2025-12-24 11:46:02'),
(11, 2, NULL, 'login', 'users', 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"email\":\"superadmin@datastat.com\",\"ip_address\":\"::1\"}', NULL, '2025-12-24 12:36:23'),
(12, 2, NULL, 'logout', 'users', 'User berhasil logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', NULL, '2025-12-24 13:06:28'),
(13, 2, NULL, 'login', 'users', 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"email\":\"superadmin@datastat.com\",\"ip_address\":\"::1\"}', NULL, '2025-12-24 13:28:37'),
(14, 6, NULL, 'login', 'users', 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"email\":\"viewer.dinkes@datastat.com\",\"ip_address\":\"::1\"}', NULL, '2025-12-24 13:30:32'),
(15, 6, NULL, 'login', 'users', 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"email\":\"viewer.dinkes@datastat.com\",\"ip_address\":\"::1\"}', NULL, '2025-12-24 13:32:12'),
(16, 2, NULL, 'login', 'users', 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"email\":\"superadmin@datastat.com\",\"ip_address\":\"::1\"}', NULL, '2025-12-24 13:34:13'),
(17, 2, NULL, 'logout', 'users', 'User berhasil logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', NULL, '2025-12-24 14:02:41'),
(18, 2, NULL, 'logout', 'users', 'User berhasil logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '[]', NULL, '2025-12-24 14:02:45'),
(19, 2, NULL, 'login', 'users', 'User berhasil login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"email\":\"superadmin@datastat.com\",\"ip_address\":\"::1\"}', NULL, '2025-12-24 14:51:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `migrations`
--

CREATE TABLE `migrations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `version` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `group` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `batch` int(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `roles`
--

CREATE TABLE `roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `role_name` varchar(50) NOT NULL COMMENT 'superadmin, owner, viewer',
  `role_label` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Daftar permission dalam format JSON' CHECK (json_valid(`permissions`)),
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `roles`
--

INSERT INTO `roles` (`id`, `role_name`, `role_label`, `description`, `permissions`, `created_at`, `updated_at`) VALUES
(1, 'superadmin', 'Super Administrator', 'Akses penuh ke seluruh sistem', '[\"all\"]', '2025-12-22 09:31:40', '2025-12-22 09:31:40'),
(2, 'owner', 'Owner/Administrator', 'Mengelola workspace sendiri', '[\"manage_workspace\", \"upload_dataset\", \"create_statistic\", \"manage_dashboard\", \"view_all\"]', '2025-12-22 09:31:40', '2025-12-22 09:31:40'),
(3, 'viewer', 'Viewer', 'Hanya melihat dashboard dan statistik', '[\"view_dashboard\", \"view_statistic\"]', '2025-12-22 09:31:40', '2025-12-22 09:31:40');

-- --------------------------------------------------------

--
-- Struktur dari tabel `statistic_configs`
--

CREATE TABLE `statistic_configs` (
  `id` int(10) UNSIGNED NOT NULL,
  `application_id` int(10) UNSIGNED NOT NULL,
  `dataset_id` int(10) UNSIGNED NOT NULL,
  `stat_name` varchar(255) NOT NULL,
  `stat_slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `metric_type` enum('count','sum','average','min','max','percentage','ratio','growth','ranking','custom_formula') NOT NULL,
  `target_field` varchar(255) DEFAULT NULL COMMENT 'Field yang dihitung',
  `group_by_fields` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Array field untuk group by' CHECK (json_valid(`group_by_fields`)),
  `filters` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Kondisi filter dalam JSON' CHECK (json_valid(`filters`)),
  `custom_formula` text DEFAULT NULL COMMENT 'Formula kustom jika metric_type = custom_formula',
  `calculation_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Konfigurasi detail perhitungan' CHECK (json_valid(`calculation_config`)),
  `visualization_type` enum('table','bar_chart','pie_chart','line_chart','area_chart','kpi_card','progress_bar','donut_chart','scatter_chart') NOT NULL DEFAULT 'table',
  `visualization_config` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Konfigurasi tampilan: warna, label, format, dll' CHECK (json_valid(`visualization_config`)),
  `sort_by` varchar(255) DEFAULT NULL,
  `sort_order` enum('asc','desc') DEFAULT 'asc',
  `limit_rows` int(10) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_calculated` datetime DEFAULT NULL,
  `cached_result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Cache hasil perhitungan terakhir' CHECK (json_valid(`cached_result`)),
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Trigger `statistic_configs`
--
DELIMITER $$
CREATE TRIGGER `before_insert_statistic_configs` BEFORE INSERT ON `statistic_configs` FOR EACH ROW BEGIN
    IF NEW.stat_slug IS NULL OR NEW.stat_slug = '' THEN
        SET NEW.stat_slug = LOWER(REPLACE(REPLACE(NEW.stat_name, ' ', '-'), '_', '-'));
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `nama_lengkap` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `bidang` varchar(100) DEFAULT NULL COMMENT 'Bidang/Departemen user',
  `avatar` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `email`, `nama_lengkap`, `password`, `bidang`, `avatar`, `is_active`, `last_login`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'superadmin@datastat.com', 'Gilang Rizky', '$2y$10$9J/6WAWYOfrK1/Ywe.GP/usgi5Cr6DKSZz1XQkBv5LZDaRJOaDRy.', 'System Administration', NULL, 1, '2025-12-24 06:51:16', '2025-12-23 06:32:53', '2025-12-24 14:51:16', NULL),
(3, 'owner.bps@datastat.com', 'Ahmad Rizki', '$2y$10$FNU6gbior3qb5dgj6NNG6.48uVN7c1uO/FeNX7YzQukWEUKt8.SD2', 'Statistik & Data', NULL, 1, '2025-12-24 03:46:02', '2025-12-23 06:32:53', '2025-12-24 11:46:02', NULL),
(4, 'owner.dinkes@datastat.com', 'Siti Aminah', '$2y$10$TzJ4KMd8TamVnV..agiFc..ZdZWRmoWCB7ePxE7.cRIQYoQaJ6Q36', 'Kesehatan Masyarakat', NULL, 1, NULL, '2025-12-23 06:32:53', '2025-12-23 14:32:53', NULL),
(5, 'viewer.bps@datastat.com', 'Budi Santoso', '$2y$10$O.CrXbswCE9LqdA1gmUUjOqhhtFxxudpWruqHPHOq8tfViLbwCDBy', 'Analisis Data', NULL, 1, NULL, '2025-12-23 06:32:53', '2025-12-23 14:32:53', NULL),
(6, 'viewer.dinkes@datastat.com', 'Dewi Lestari', '$2y$10$qvATI1RcoK55.tSmB/xhFu3ZwMMRDc6Ji1CEISetcbo05lQXcQv7.', 'Monitoring & Evaluasi', NULL, 1, '2025-12-24 05:32:12', '2025-12-23 06:32:53', '2025-12-24 13:32:12', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `role_id` int(10) UNSIGNED NOT NULL,
  `application_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'NULL untuk superadmin, ada nilai untuk owner/viewer',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `application_id`, `is_active`, `created_at`, `updated_at`) VALUES
(2, 2, 1, NULL, 1, '2025-12-23 14:36:03', '2025-12-23 14:36:03'),
(3, 3, 2, NULL, 1, '2025-12-23 14:36:18', '2025-12-23 14:36:18'),
(4, 4, 2, NULL, 1, '2025-12-23 14:36:26', '2025-12-23 14:36:26'),
(5, 5, 3, NULL, 1, '2025-12-23 14:36:34', '2025-12-23 14:36:34'),
(6, 6, 3, NULL, 1, '2025-12-23 14:36:43', '2025-12-23 14:36:43');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_dashboards_summary`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_dashboards_summary` (
`dashboard_id` int(10) unsigned
,`dashboard_name` varchar(255)
,`dashboard_slug` varchar(255)
,`is_default` tinyint(1)
,`is_public` tinyint(1)
,`application_id` int(10) unsigned
,`app_name` varchar(255)
,`widget_count` bigint(21)
,`created_by_name` varchar(255)
,`created_at` datetime
,`updated_at` datetime
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_statistics_detail`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_statistics_detail` (
`statistic_id` int(10) unsigned
,`stat_name` varchar(255)
,`stat_slug` varchar(255)
,`metric_type` enum('count','sum','average','min','max','percentage','ratio','growth','ranking','custom_formula')
,`visualization_type` enum('table','bar_chart','pie_chart','line_chart','area_chart','kpi_card','progress_bar','donut_chart','scatter_chart')
,`is_active` tinyint(1)
,`application_id` int(10) unsigned
,`app_name` varchar(255)
,`dataset_id` int(10) unsigned
,`dataset_name` varchar(255)
,`total_rows` int(10) unsigned
,`created_by_name` varchar(255)
,`created_at` datetime
,`updated_at` datetime
,`last_calculated` datetime
);

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `v_user_roles`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `v_user_roles` (
`user_id` int(10) unsigned
,`email` varchar(255)
,`nama_lengkap` varchar(255)
,`user_bidang` varchar(100)
,`user_active` tinyint(1)
,`role_id` int(10) unsigned
,`role_name` varchar(50)
,`role_label` varchar(100)
,`application_id` int(10) unsigned
,`app_name` varchar(255)
,`app_slug` varchar(255)
,`app_bidang` varchar(100)
,`role_active` tinyint(1)
);

-- --------------------------------------------------------

--
-- Struktur untuk view `v_dashboards_summary`
--
DROP TABLE IF EXISTS `v_dashboards_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_dashboards_summary`  AS SELECT `d`.`id` AS `dashboard_id`, `d`.`dashboard_name` AS `dashboard_name`, `d`.`dashboard_slug` AS `dashboard_slug`, `d`.`is_default` AS `is_default`, `d`.`is_public` AS `is_public`, `d`.`application_id` AS `application_id`, `a`.`app_name` AS `app_name`, count(`dw`.`id`) AS `widget_count`, `u`.`nama_lengkap` AS `created_by_name`, `d`.`created_at` AS `created_at`, `d`.`updated_at` AS `updated_at` FROM (((`dashboards` `d` join `applications` `a` on(`d`.`application_id` = `a`.`id`)) join `users` `u` on(`d`.`created_by` = `u`.`id`)) left join `dashboard_widgets` `dw` on(`d`.`id` = `dw`.`dashboard_id`)) WHERE `d`.`deleted_at` is null AND `a`.`deleted_at` is null GROUP BY `d`.`id`, `d`.`dashboard_name`, `d`.`dashboard_slug`, `d`.`is_default`, `d`.`is_public`, `d`.`application_id`, `a`.`app_name`, `u`.`nama_lengkap`, `d`.`created_at`, `d`.`updated_at` ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_statistics_detail`
--
DROP TABLE IF EXISTS `v_statistics_detail`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_statistics_detail`  AS SELECT `sc`.`id` AS `statistic_id`, `sc`.`stat_name` AS `stat_name`, `sc`.`stat_slug` AS `stat_slug`, `sc`.`metric_type` AS `metric_type`, `sc`.`visualization_type` AS `visualization_type`, `sc`.`is_active` AS `is_active`, `sc`.`application_id` AS `application_id`, `a`.`app_name` AS `app_name`, `sc`.`dataset_id` AS `dataset_id`, `d`.`dataset_name` AS `dataset_name`, `d`.`total_rows` AS `total_rows`, `u`.`nama_lengkap` AS `created_by_name`, `sc`.`created_at` AS `created_at`, `sc`.`updated_at` AS `updated_at`, `sc`.`last_calculated` AS `last_calculated` FROM (((`statistic_configs` `sc` join `applications` `a` on(`sc`.`application_id` = `a`.`id`)) join `datasets` `d` on(`sc`.`dataset_id` = `d`.`id`)) join `users` `u` on(`sc`.`created_by` = `u`.`id`)) WHERE `sc`.`deleted_at` is null AND `a`.`deleted_at` is null ;

-- --------------------------------------------------------

--
-- Struktur untuk view `v_user_roles`
--
DROP TABLE IF EXISTS `v_user_roles`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_user_roles`  AS SELECT `u`.`id` AS `user_id`, `u`.`email` AS `email`, `u`.`nama_lengkap` AS `nama_lengkap`, `u`.`bidang` AS `user_bidang`, `u`.`is_active` AS `user_active`, `r`.`id` AS `role_id`, `r`.`role_name` AS `role_name`, `r`.`role_label` AS `role_label`, `ur`.`application_id` AS `application_id`, `a`.`app_name` AS `app_name`, `a`.`app_slug` AS `app_slug`, `a`.`bidang` AS `app_bidang`, `ur`.`is_active` AS `role_active` FROM (((`users` `u` join `user_roles` `ur` on(`u`.`id` = `ur`.`user_id`)) join `roles` `r` on(`ur`.`role_id` = `r`.`id`)) left join `applications` `a` on(`ur`.`application_id` = `a`.`id`)) WHERE `u`.`deleted_at` is null ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `app_slug` (`app_slug`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_app_slug` (`app_slug`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indeks untuk tabel `dashboards`
--
ALTER TABLE `dashboards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `access_token` (`access_token`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_dashboard_slug` (`dashboard_slug`),
  ADD KEY `idx_is_default` (`is_default`),
  ADD KEY `idx_deleted_at` (`deleted_at`),
  ADD KEY `idx_app_default` (`application_id`,`is_default`);

--
-- Indeks untuk tabel `dashboard_widgets`
--
ALTER TABLE `dashboard_widgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dashboard_id` (`dashboard_id`),
  ADD KEY `idx_statistic_config_id` (`statistic_config_id`),
  ADD KEY `idx_sort_order` (`sort_order`);

--
-- Indeks untuk tabel `datasets`
--
ALTER TABLE `datasets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `uploaded_by` (`uploaded_by`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_dataset_slug` (`dataset_slug`),
  ADD KEY `idx_upload_status` (`upload_status`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indeks untuk tabel `dataset_records`
--
ALTER TABLE `dataset_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dataset_id` (`dataset_id`),
  ADD KEY `idx_row_num` (`row_num`),
  ADD KEY `idx_dataset_created` (`dataset_id`,`created_at`);

--
-- Indeks untuk tabel `log_activities`
--
ALTER TABLE `log_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_activity_type` (`activity_type`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_user_created` (`user_id`,`created_at`);

--
-- Indeks untuk tabel `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`),
  ADD KEY `idx_role_name` (`role_name`);

--
-- Indeks untuk tabel `statistic_configs`
--
ALTER TABLE `statistic_configs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_application_id` (`application_id`),
  ADD KEY `idx_dataset_id` (`dataset_id`),
  ADD KEY `idx_stat_slug` (`stat_slug`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_deleted_at` (`deleted_at`),
  ADD KEY `idx_app_active` (`application_id`,`is_active`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_deleted_at` (`deleted_at`);

--
-- Indeks untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_role_app` (`user_id`,`role_id`,`application_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_role_id` (`role_id`),
  ADD KEY `idx_application_id` (`application_id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `applications`
--
ALTER TABLE `applications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `dashboards`
--
ALTER TABLE `dashboards`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `dashboard_widgets`
--
ALTER TABLE `dashboard_widgets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `datasets`
--
ALTER TABLE `datasets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `dataset_records`
--
ALTER TABLE `dataset_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `log_activities`
--
ALTER TABLE `log_activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT untuk tabel `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `statistic_configs`
--
ALTER TABLE `statistic_configs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `dashboards`
--
ALTER TABLE `dashboards`
  ADD CONSTRAINT `dashboards_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dashboards_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `dashboard_widgets`
--
ALTER TABLE `dashboard_widgets`
  ADD CONSTRAINT `dashboard_widgets_ibfk_1` FOREIGN KEY (`dashboard_id`) REFERENCES `dashboards` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `dashboard_widgets_ibfk_2` FOREIGN KEY (`statistic_config_id`) REFERENCES `statistic_configs` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `datasets`
--
ALTER TABLE `datasets`
  ADD CONSTRAINT `datasets_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `datasets_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `dataset_records`
--
ALTER TABLE `dataset_records`
  ADD CONSTRAINT `dataset_records_ibfk_1` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `log_activities`
--
ALTER TABLE `log_activities`
  ADD CONSTRAINT `log_activities_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `log_activities_ibfk_2` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `statistic_configs`
--
ALTER TABLE `statistic_configs`
  ADD CONSTRAINT `statistic_configs_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `statistic_configs_ibfk_2` FOREIGN KEY (`dataset_id`) REFERENCES `datasets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `statistic_configs_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_3` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
