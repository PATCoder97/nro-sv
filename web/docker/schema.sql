CREATE TABLE IF NOT EXISTS `cvh_setting` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL DEFAULT 'Teamobi 2026',
  `author` VARCHAR(255) NOT NULL DEFAULT 'Teamobi 2026',
  `description` TEXT NOT NULL,
  `keywords` TEXT NOT NULL,
  `favicon` VARCHAR(500) NOT NULL DEFAULT '/images/logo/fav.png',
  `logo` VARCHAR(500) NOT NULL DEFAULT '/images/logo/logo.gif',
  `size_logo` INT NOT NULL DEFAULT 180,
  `background` VARCHAR(500) NOT NULL DEFAULT '/images/logo/background.gif',
  `banner` VARCHAR(500) NOT NULL DEFAULT '/images/logo/banner.jpg',
  `navbar` VARCHAR(20) NOT NULL DEFAULT 'absolute',
  `amount_mtv` INT NOT NULL DEFAULT 10000,
  `thongbao` VARCHAR(10) NOT NULL DEFAULT 'false',
  `nd_thongbao` TEXT NOT NULL,
  `download` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cvh_setting`
  (`id`, `description`, `keywords`, `nd_thongbao`, `download`)
VALUES
  (1, 'Máy chủ Teamobi 2026', 'teamobi,nro,dragon ball', '', '[]')
ON DUPLICATE KEY UPDATE `id` = `id`;

CREATE TABLE IF NOT EXISTS `cvh_sessions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `token` VARCHAR(128) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cvh_sessions_token` (`token`),
  KEY `idx_cvh_sessions_user` (`user_id`),
  KEY `idx_cvh_sessions_expiry` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cvh_baiviet` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(255) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `likes` LONGTEXT NOT NULL,
  `comments` LONGTEXT NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `poster` INT NOT NULL,
  `role` TINYINT NOT NULL DEFAULT 1,
  `if_admin` LONGTEXT NULL,
  `time` BIGINT NOT NULL DEFAULT 0,
  `created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cvh_baiviet_poster` (`poster`),
  KEY `idx_cvh_baiviet_role_status` (`role`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cvh_messages` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `message` TEXT NOT NULL,
  `created_at` BIGINT NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cvh_messages_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cvh_recharge` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `account_id` INT NOT NULL,
  `code` VARCHAR(64) NULL,
  `serial` VARCHAR(64) NULL,
  `amount` INT NOT NULL DEFAULT 0,
  `type` VARCHAR(32) NULL,
  `amount_real` INT NOT NULL DEFAULT -1,
  `status` TINYINT NOT NULL DEFAULT 0,
  `tranid` VARCHAR(64) NOT NULL,
  `time` VARCHAR(32) NOT NULL,
  `declared_value` INT NULL,
  `value` INT NULL,
  `card_value` INT NULL,
  `telco` VARCHAR(32) NULL,
  `trans_id` VARCHAR(64) NULL,
  `message` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cvh_recharge_tranid` (`tranid`),
  KEY `idx_cvh_recharge_account` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cvh_sell_item` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `item` INT NOT NULL,
  `slot` INT NOT NULL DEFAULT 0,
  `price` INT NOT NULL DEFAULT 0,
  `options` LONGTEXT NOT NULL,
  `gender` TINYINT NOT NULL DEFAULT 0,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  `users_buy` LONGTEXT NOT NULL,
  `time` BIGINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_cvh_sell_item_active` (`active`),
  KEY `idx_cvh_sell_item_item` (`item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cvh_giftcode` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(100) NOT NULL,
  `luot` INT NOT NULL DEFAULT 0,
  `item` LONGTEXT NOT NULL,
  `option` LONGTEXT NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `hsd` VARCHAR(100) NOT NULL,
  `time` BIGINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cvh_giftcode_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cvh_history_giftcode` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `player_id` INT NOT NULL,
  `code` VARCHAR(100) NOT NULL,
  `time` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cvh_history_giftcode` (`player_id`, `code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bank_payment_codes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `code` VARCHAR(50) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bank_payment_user` (`user_id`),
  UNIQUE KEY `uq_bank_payment_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tb_transactions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `gateway` VARCHAR(100) NOT NULL,
  `transaction_date` TIMESTAMP NULL DEFAULT NULL,
  `account_number` VARCHAR(100) NULL,
  `sub_account` VARCHAR(250) NULL,
  `amount_in` DECIMAL(20,2) NOT NULL DEFAULT 0,
  `amount_out` DECIMAL(20,2) NOT NULL DEFAULT 0,
  `accumulated` DECIMAL(20,2) NOT NULL DEFAULT 0,
  `code` VARCHAR(250) NULL,
  `transaction_content` TEXT NULL,
  `reference_number` VARCHAR(255) NULL,
  `body` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tb_transactions_reference` (`reference_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cvh_realtime` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `session_id` VARCHAR(255) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NULL,
  `page_url` VARCHAR(255) NULL,
  `last_activity` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `is_online` TINYINT(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_cvh_realtime_session` (`session_id`),
  KEY `idx_cvh_realtime_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cvh_visits` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT NULL,
  `page_url` VARCHAR(255) NULL,
  `visit_date` DATE DEFAULT (CURRENT_DATE),
  `visit_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cvh_visits_date_ip` (`visit_date`, `ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `shop_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `admin_id` INT NOT NULL,
  `admin_username` VARCHAR(50) NOT NULL,
  `item_id` INT NOT NULL,
  `item_name` VARCHAR(255) NOT NULL,
  `planet` VARCHAR(50) NOT NULL,
  `price` INT NOT NULL,
  `max_buy` INT NOT NULL,
  `options` TEXT NOT NULL,
  `icon_id` INT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `buff_requests` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `player_id` INT NOT NULL,
  `player_name` VARCHAR(50) NOT NULL,
  `item_id` INT NOT NULL,
  `options_string` VARCHAR(255) NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `status` ENUM('pending','completed','failed') DEFAULT 'pending',
  `admin_name` VARCHAR(50) NOT NULL,
  `admin_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `completed_at` TIMESTAMP NULL,
  `result_message` TEXT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_buff_requests_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_activity_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `admin_id` INT NOT NULL,
  `admin_username` VARCHAR(50) NOT NULL,
  `action` VARCHAR(100) NOT NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `admin_id` INT NOT NULL,
  `admin_name` VARCHAR(50) NULL,
  `admin_username` VARCHAR(50) NULL,
  `action` VARCHAR(100) NOT NULL,
  `target` VARCHAR(255) NULL,
  `details` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_admin_log_admin` (`admin_id`),
  KEY `idx_admin_log_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `admin_roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `permissions` JSON NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_roles_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `system_status` (
  `id` INT NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `server_control` (
  `id` TINYINT NOT NULL,
  `start_request` TINYINT(1) NOT NULL DEFAULT 0,
  `stop_request` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `girl` (`id` INT NOT NULL AUTO_INCREMENT, `url` VARCHAR(500) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS `sexy` (`id` INT NOT NULL AUTO_INCREMENT, `url` VARCHAR(500) NOT NULL, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `legacy_token_revoked` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `is_super_admin` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_home` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_users_manager` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_ip_manager` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_item_template_manager` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_maintenance_manager` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_vnd_manager` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_recharge_manager` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_post_manager` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_notification_manager` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_giftcode_manager` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_shop_manager` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_shop_log` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_download_manager` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_website_manager` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_logout_all` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_ddos_check` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_file_manager` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_system_settings` TINYINT(1) NOT NULL DEFAULT 0;
ALTER TABLE `account` ADD COLUMN IF NOT EXISTS `perm_buff_item_manager` TINYINT(1) NOT NULL DEFAULT 0;

UPDATE `account`
SET `is_super_admin` = 1
WHERE `id` = (
  SELECT `id` FROM (
    SELECT MIN(`id`) AS `id` FROM `account` WHERE `is_admin` = 1
  ) AS `first_admin`
);
