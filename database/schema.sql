-- DomainHub Database Schema
-- MySQL 5.7+ compatible

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------

--
-- Users table
--
CREATE TABLE `users` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(100) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `status` ENUM('active', 'inactive', 'banned') DEFAULT 'active',
  `last_login` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_username` (`username`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `role` (`role`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Domain extensions table
--
CREATE TABLE `domain_extensions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `extension` VARCHAR(20) NOT NULL,
  `category` VARCHAR(50) DEFAULT 'general',
  `price_usd` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `renewal_price_usd` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `transfer_price_usd` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
  `is_active` TINYINT(1) DEFAULT 1,
  `is_premium` TINYINT(1) DEFAULT 0,
  `description_fa` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_extension` (`extension`),
  KEY `category` (`category`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Currency rates table
--
CREATE TABLE `currency_rates` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `currency` VARCHAR(3) NOT NULL DEFAULT 'USD',
  `rate_to_irt` DECIMAL(15,2) NOT NULL,
  `source` VARCHAR(50) NOT NULL,
  `fetched_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `is_current` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `currency` (`currency`),
  KEY `is_current` (`is_current`),
  KEY `fetched_at` (`fetched_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Search history table (for logged-in users)
--
CREATE TABLE `search_history` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) UNSIGNED DEFAULT NULL,
  `session_id` VARCHAR(64) DEFAULT NULL,
  `domain_name` VARCHAR(255) NOT NULL,
  `extension` VARCHAR(20) NOT NULL,
  `full_domain` VARCHAR(275) NOT NULL,
  `status` ENUM('available', 'registered', 'expired', 'unknown') DEFAULT 'unknown',
  `whois_data` TEXT,
  `registration_date` DATE DEFAULT NULL,
  `expiry_date` DATE DEFAULT NULL,
  `registrar` VARCHAR(100) DEFAULT NULL,
  `suggested_alternatives` JSON DEFAULT NULL,
  `searched_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `session_id` (`session_id`),
  KEY `full_domain` (`full_domain`),
  KEY `status` (`status`),
  KEY `searched_at` (`searched_at`),
  CONSTRAINT `fk_search_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Orders table
--
CREATE TABLE `orders` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` VARCHAR(20) NOT NULL,
  `user_id` INT(11) UNSIGNED NOT NULL,
  `domain_name` VARCHAR(255) NOT NULL,
  `extension` VARCHAR(20) NOT NULL,
  `full_domain` VARCHAR(275) NOT NULL,
  `service_type` ENUM('domain', 'hosting', 'vps', 'ssl') DEFAULT 'domain',
  `period_years` INT(11) DEFAULT 1,
  `price_usd` DECIMAL(10,2) NOT NULL,
  `price_irt` DECIMAL(15,2) NOT NULL,
  `currency_rate` DECIMAL(15,2) NOT NULL,
  `payment_method` ENUM('manual_transfer', 'gateway') DEFAULT 'manual_transfer',
  `status` ENUM('pending', 'paid', 'confirmed', 'completed', 'cancelled', 'failed') DEFAULT 'pending',
  `payment_receipt` VARCHAR(255) DEFAULT NULL,
  `payment_verified_at` DATETIME DEFAULT NULL,
  `verified_by` INT(11) UNSIGNED DEFAULT NULL,
  `notes` TEXT,
  `admin_notes` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `payment_method` (`payment_method`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_verified_by` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Bank cards table (for manual transfer)
--
CREATE TABLE `bank_cards` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `bank_name` VARCHAR(50) NOT NULL,
  `card_number` VARCHAR(19) NOT NULL,
  `holder_name` VARCHAR(100) NOT NULL,
  `priority` INT(11) DEFAULT 1,
  `is_active` TINYINT(1) DEFAULT 1,
  `display_mode` ENUM('priority', 'random', 'single') DEFAULT 'priority',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- System logs table
--
CREATE TABLE `system_logs` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `level` ENUM('debug', 'info', 'warning', 'error', 'critical') DEFAULT 'info',
  `category` VARCHAR(50) DEFAULT 'general',
  `message` TEXT NOT NULL,
  `context` JSON DEFAULT NULL,
  `user_id` INT(11) UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `level` (`level`),
  KEY `category` (`category`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Settings table
--
CREATE TABLE `settings` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT,
  `setting_type` ENUM('string', 'number', 'boolean', 'json') DEFAULT 'string',
  `group_name` VARCHAR(50) DEFAULT 'general',
  `description` VARCHAR(255) DEFAULT NULL,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_setting_key` (`setting_key`),
  KEY `group_name` (`group_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Insert default data
--

-- Default admin user (password: admin123 - change immediately!)
INSERT INTO `users` (`username`, `email`, `password_hash`, `full_name`, `role`, `status`) VALUES
('admin', 'admin@domainhub.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدیر سیستم', 'admin', 'active');

-- Default domain extensions
INSERT INTO `domain_extensions` (`extension`, `category`, `price_usd`, `renewal_price_usd`, `transfer_price_usd`, `is_active`, `description_fa`) VALUES
('com', 'commercial', 8.98, 12.98, 12.98, 1, 'مناسب برای کسب‌وکارهای تجاری'),
('net', 'network', 9.98, 14.98, 14.98, 1, 'مناسب برای شبکه‌ها و خدمات اینترنتی'),
('org', 'organization', 9.98, 14.98, 14.98, 1, 'مناسب برای سازمان‌ها و موسسات'),
('ir', 'country', 0.50, 0.50, 0.50, 1, 'دامنه ملی ایران'),
('co.ir', 'country', 0.50, 0.50, 0.50, 1, 'دامنه شرکتی ایران'),
('io', 'tech', 39.00, 39.00, 39.00, 1, 'محبوب برای استارتاپ‌های تکنولوژی'),
('ai', 'tech', 80.00, 80.00, 80.00, 1, 'مناسب برای پروژه‌های هوش مصنوعی'),
('dev', 'tech', 12.00, 12.00, 12.00, 1, 'مناسب برای توسعه‌دهندگان'),
('app', 'tech', 14.00, 14.00, 14.00, 1, 'مناسب برای اپلیکیشن‌ها'),
('shop', 'commercial', 2.95, 32.95, 32.95, 1, 'مناسب برای فروشگاه‌های آنلاین');

-- Default bank card
INSERT INTO `bank_cards` (`bank_name`, `card_number`, `holder_name`, `priority`, `is_active`, `display_mode`) VALUES
('ملی', '6037-9918-XXXX-XXXX', 'نام صاحب حساب', 1, 1, 'priority');

-- Default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `group_name`, `description`) VALUES
('site_title', 'DomainHub - جستجو و ثبت دامنه', 'string', 'general', 'عنوان سایت'),
('guest_history_retention_days', '30', 'number', 'general', 'مدت نگهداری تاریخچه جستجوی مهمانان (روز)'),
('auto_confirm_orders', '0', 'boolean', 'orders', 'تأیید خودکار سفارشات'),
('maintenance_mode', '0', 'boolean', 'general', 'حالت تعمیر و نگهداری');

COMMIT;
