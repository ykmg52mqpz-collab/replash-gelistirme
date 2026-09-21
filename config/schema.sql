-- Replash Database Schema for MySQL / MariaDB (Linux & Windows Compatible)
CREATE TABLE IF NOT EXISTS `facilities` (
  `id` VARCHAR(64) NOT NULL PRIMARY KEY,
  `name` VARCHAR(128) NOT NULL,
  `slug` VARCHAR(128) NOT NULL,
  `city` VARCHAR(64) NOT NULL,
  `sport` VARCHAR(32) NOT NULL DEFAULT 'padel',
  `courts` TEXT NOT NULL,
  `pin` VARCHAR(10) NOT NULL,
  `sport_prices` TEXT NULL DEFAULT NULL,
  `unit_price` DECIMAL(10,2) NOT NULL DEFAULT 6.00,
  `currency` VARCHAR(5) NOT NULL DEFAULT 'EUR',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cameras` (
  `id` VARCHAR(64) NOT NULL PRIMARY KEY,
  `facility_id` VARCHAR(64) NOT NULL,
  `court` VARCHAR(64) NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `channel_id` VARCHAR(128) NOT NULL UNIQUE,
  `stream_url` TEXT NULL,
  `status` ENUM('online', 'offline') NOT NULL DEFAULT 'online',
  `last_seen_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`facility_id`),
  INDEX (`channel_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `matches` (
  `id` VARCHAR(64) NOT NULL PRIMARY KEY,
  `code` VARCHAR(64) NOT NULL,
  `access_code` VARCHAR(32) NOT NULL,
  `camera_id` VARCHAR(64) NULL,
  `facility_id` VARCHAR(64) NOT NULL,
  `court` VARCHAR(64) NOT NULL,
  `date` DATE NOT NULL,
  `time_slot` VARCHAR(32) NOT NULL,
  `status` ENUM('LOCKED', 'UNLOCKABLE', 'EXPIRED') NOT NULL DEFAULT 'LOCKED',
  `playback_url` TEXT NULL,
  `playback_type` VARCHAR(10) NOT NULL DEFAULT 'mp4',
  `thumbnail_url` TEXT NULL,
  `duration_sec` INT NOT NULL DEFAULT 3600,
  `size_bytes` BIGINT NULL,
  `recording_id` VARCHAR(128) NULL,
  `sold_at` DATETIME NULL,
  `views` INT NOT NULL DEFAULT 0,
  `billable` TINYINT(1) NOT NULL DEFAULT 0,
  `sport` VARCHAR(32) NOT NULL DEFAULT 'padel',
  `source` VARCHAR(32) NOT NULL DEFAULT 'camera',
  `recorded_at` DATETIME NULL,
  `available_at` DATETIME NULL,
  `first_opened_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`access_code`),
  INDEX (`facility_id`, `date`),
  INDEX (`camera_id`),
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admins` (
  `id` VARCHAR(64) NOT NULL PRIMARY KEY,
  `email` VARCHAR(128) NOT NULL UNIQUE,
  `name` VARCHAR(128) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(32) NOT NULL DEFAULT 'admin',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `leads` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(128) NOT NULL,
  `email` VARCHAR(128) NOT NULL,
  `phone` VARCHAR(64) NULL,
  `facility_name` VARCHAR(128) NULL,
  `city` VARCHAR(128) NULL,
  `sport` VARCHAR(64) NULL,
  `court_count` INT DEFAULT 1,
  `message` TEXT NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'new',
  `ip_address` VARCHAR(64) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `refunds` (
  `id` VARCHAR(64) NOT NULL PRIMARY KEY,
  `match_id` VARCHAR(64) NOT NULL,
  `facility_id` VARCHAR(64) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `reason` TEXT NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `viewer_sessions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `match_id` VARCHAR(64) NOT NULL,
  `token` VARCHAR(128) NOT NULL UNIQUE,
  `ip_address` VARCHAR(64) NULL,
  `user_agent` TEXT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`token`),
  INDEX (`match_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `settings` (
  `key_name` VARCHAR(64) NOT NULL PRIMARY KEY,
  `value` LONGTEXT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Satış denetim kaydı (includes/sales.php) — kayıtlar silinmez
CREATE TABLE IF NOT EXISTS `sale_events` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `match_id` VARCHAR(64) NOT NULL,
  `facility_id` VARCHAR(64) NOT NULL,
  `action` VARCHAR(20) NOT NULL,
  `actor` VARCHAR(64) NOT NULL,
  `reason` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`facility_id`, `created_at`),
  INDEX (`match_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
