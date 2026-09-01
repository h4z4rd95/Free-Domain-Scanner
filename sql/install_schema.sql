-- DomainHub Installation Schema
-- MySQL Database Structure

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table structure for table `domains`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `domains` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `extension` varchar(50) NOT NULL,
  `full_domain` varchar(300) NOT NULL,
  `status` enum('available','registered','reserved') DEFAULT 'available',
  `price_usd` decimal(10,2) DEFAULT '0.00',
  `price_toman` decimal(15,2) DEFAULT '0.00',
  `category` varchar(100) DEFAULT 'general',
  `description` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `full_domain` (`full_domain`),
  KEY `extension` (`extension`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `search_history`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `search_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) DEFAULT NULL,
  `domain_name` varchar(255) NOT NULL,
  `extension` varchar(50) NOT NULL,
  `result_status` enum('available','registered','error') DEFAULT NULL,
  `whois_data` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `session_id` (`session_id`),
  KEY `created_at` (`created_at`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `phone` varchar(20) DEFAULT NULL,
  `telegram_id` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `orders`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `domain_id` int(11) DEFAULT NULL,
  `order_type` enum('domain','hosting','vps','other') DEFAULT 'domain',
  `service_details` text,
  `amount_usd` decimal(10,2) DEFAULT '0.00',
  `amount_toman` decimal(15,2) DEFAULT '0.00',
  `payment_method` enum('card_to_card','online','manual') DEFAULT 'card_to_card',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `order_status` enum('pending','processing','completed','cancelled','suspended') DEFAULT 'pending',
  `payment_receipt` varchar(255) DEFAULT NULL,
  `payment_date` timestamp NULL DEFAULT NULL,
  `admin_notes` text,
  `customer_notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `domain_id` (`domain_id`),
  KEY `payment_status` (`payment_status`),
  KEY `order_status` (`order_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `exchange_rates`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `exchange_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `currency` varchar(10) NOT NULL DEFAULT 'USD',
  `rate` decimal(15,2) NOT NULL,
  `source` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `currency` (`currency`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `settings`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT '0',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `payment_cards`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `payment_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_number` varchar(20) NOT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `card_holder` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `display_order` int(11) DEFAULT '0',
  `is_random_display` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_active` (`is_active`),
  KEY `display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `system_logs`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `log_level` enum('info','warning','error','critical') DEFAULT 'info',
  `module` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `details` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `log_level` (`log_level`),
  KEY `module` (`module`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `domain_categories`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `domain_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `name_fa` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `icon` varchar(100) DEFAULT NULL,
  `sort_order` int(11) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `is_active` (`is_active`),
  KEY `sort_order` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Insert default data
-- --------------------------------------------------------

-- Default admin user (password: admin123 - change after first login!)
INSERT INTO `users` (`username`, `email`, `password_hash`, `role`) VALUES
('admin', 'admin@localhost', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Default exchange rate
INSERT INTO `exchange_rates` (`currency`, `rate`, `source`, `is_active`) VALUES
('USD', 60000.00, 'manual', 1);

-- Default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`, `is_system`) VALUES
('site_name', 'DomainHub', 'string', 'Site Name', 1),
('site_description', 'جستجو و ثبت دامنه‌های اینترنتی', 'string', 'Site Description', 1),
('telegram_bot_id', '', 'string', 'Telegram Bot ID for notifications', 0),
('telegram_channel', '', 'string', 'Telegram Channel for order notifications', 0),
('search_history_expiry_days', '30', 'number', 'Days to keep guest search history', 0),
('enable_whois', '1', 'boolean', 'Enable WHOIS lookup', 0),
('default_currency', 'USD', 'string', 'Default Currency', 1),
('maintenance_mode', '0', 'boolean', 'Maintenance Mode', 0);

-- Default domain categories
INSERT INTO `domain_categories` (`name`, `name_fa`, `slug`, `description`, `icon`, `sort_order`, `is_active`) VALUES
('Popular', 'محبوب', 'popular', 'دامنه‌های محبوب و پرکاربرد', 'star', 1, 1),
('Technology', 'تکنولوژی', 'technology', 'دامنه‌های مرتبط با فناوری و استارتاپ‌ها', 'code', 2, 1),
('Business', 'کسب‌وکار', 'business', 'دامنه‌های تجاری و شرکتی', 'briefcase', 3, 1),
('Iranian', 'ایرانی', 'iranian', 'پسوندهای مخصوص ایران', 'flag', 4, 1),
('New', 'جدید', 'new', 'پسوندهای جدید و خلاقانه', 'sparkles', 5, 1);

COMMIT;
