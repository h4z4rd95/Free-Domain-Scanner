-- Schema SQL for DomainHub
-- پیشوند جداول با {{PREFIX}} در زمان نصب جایگزین می‌شود

CREATE TABLE IF NOT EXISTS `{{PREFIX}}users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('user', 'admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}search_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NULL, -- NULL برای کاربران مهمان
  `session_id` VARCHAR(64) NULL, -- برای ردیابی کاربران مهمان
  `domain_name` VARCHAR(255) NOT NULL,
  `tld` VARCHAR(20) NOT NULL,
  `is_available` TINYINT(1) DEFAULT NULL, -- NULL یعنی هنوز چک نشده یا خطا
  `whois_data` TEXT NULL, -- داده‌های خام Whois
  `registrar` VARCHAR(255) NULL,
  `expiry_date` DATE NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_session` (`session_id`),
  INDEX `idx_user` (`user_id`),
  INDEX `idx_domain` (`domain_name`),
  FOREIGN KEY (`user_id`) REFERENCES `{{PREFIX}}users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}orders` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT UNSIGNED NOT NULL,
  `domain_name` VARCHAR(255) NOT NULL,
  `tld` VARCHAR(20) NOT NULL,
  `price_usd` DECIMAL(10, 2) NOT NULL,
  `price_toman` DECIMAL(15, 2) NOT NULL,
  `exchange_rate` DECIMAL(15, 2) NOT NULL, -- نرخ دلار در زمان خرید
  `status` ENUM('pending', 'paid', 'processing', 'completed', 'cancelled', 'error') DEFAULT 'pending',
  `payment_method` VARCHAR(50) DEFAULT 'manual', -- manual, online
  `payment_receipt` TEXT NULL, -- تصویر رسید یا توضیحات
  `payment_date` DATETIME NULL,
  `admin_notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `{{PREFIX}}users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `description` VARCHAR(255) NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- داده‌های اولیه تنظیمات
INSERT INTO `{{PREFIX}}settings` (`setting_key`, `setting_value`, `description`) VALUES
('site_title', 'DomainHub', 'عنوان سایت'),
('currency_api_url', 'https://api.exchangerate.ir/v1/latest?base=USD', 'آدرس API نرخ ارز'),
('manual_payment_enabled', '1', 'فعال‌سازی پرداخت دستی/کارت به کارت'),
('auto_delete_guest_logs_days', '30', 'حذف خودکار لاگ جستجوی مهمان بعد از چند روز'),
('namecheap_api_user', '', 'نام کاربری API نیم‌چیپ'),
('namecheap_api_key', '', 'کلید API نیم‌چیپ'),
('namecheap_client_ip', '', 'آی‌پی سرور برای اتصال به نیم‌چیپ');

CREATE TABLE IF NOT EXISTS `{{PREFIX}}exchange_rates` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `rate` DECIMAL(15, 2) NOT NULL,
  `source` VARCHAR(50) DEFAULT 'internal',
  `fetched_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}system_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `level` ENUM('info', 'warning', 'error', 'critical') DEFAULT 'info',
  `message` TEXT NOT NULL,
  `context` JSON NULL, -- داده‌های اضافی به صورت JSON
  `file` VARCHAR(255) NULL,
  `line` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `{{PREFIX}}cards` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `bank_name` VARCHAR(100) NOT NULL,
  `card_number` VARCHAR(16) NOT NULL,
  `account_holder` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- کارت نمونه
INSERT INTO `{{PREFIX}}cards` (`bank_name`, `card_number`, `account_holder`, `is_active`, `sort_order`) VALUES
('بانک ملت', '6104337712345678', 'صاحب کسب و کار', 1, 1);
