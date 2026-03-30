-- ═══════════════════════════════════════════════════════
--  TravelTeachings v2.0 — Database Setup Script
--  Run once on your MySQL server
-- ═══════════════════════════════════════════════════════

-- Visits tracking table
CREATE TABLE IF NOT EXISTS `visits` (
  `id`          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
  `ip_address`  VARCHAR(45)     NOT NULL,
  `user_agent`  VARCHAR(255)    DEFAULT NULL,
  `visited_at`  TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_ip_time` (`ip_address`, `visited_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Downloads tracking table
CREATE TABLE IF NOT EXISTS `downloads` (
  `id`            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `file_name`     VARCHAR(255)  NOT NULL,
  `note_name`     VARCHAR(255)  DEFAULT NULL,
  `ip_address`    VARCHAR(45)   NOT NULL,
  `downloaded_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_file` (`file_name`),
  INDEX `idx_time` (`downloaded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin activity log
CREATE TABLE IF NOT EXISTS `admin_log` (
  `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
  `action`      VARCHAR(50)   NOT NULL,
  `ip_address`  VARCHAR(45)   NOT NULL,
  `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Settings key-value store
CREATE TABLE IF NOT EXISTS `settings` (
  `key`         VARCHAR(100)  NOT NULL,
  `value`       TEXT          DEFAULT NULL,
  `updated_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Example: insert initial settings
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES
  ('groq_api_key', ''),
  ('site_name', 'TravelTeachings'),
  ('contact_email', 'travelteachings@gmail.com');
