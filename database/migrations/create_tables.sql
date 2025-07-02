-- 用户表
CREATE TABLE IF NOT EXISTS `user` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `username` VARCHAR(50) NOT NULL DEFAULT '',
    `phone` VARCHAR(20) NOT NULL DEFAULT '',
    `email` VARCHAR(100) NOT NULL DEFAULT '',
    `wechat_openid` VARCHAR(100) NOT NULL DEFAULT '',
    `avatar` VARCHAR(255) NOT NULL DEFAULT '',
    `status` INTEGER NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 处方表
CREATE TABLE IF NOT EXISTS `prescription` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL DEFAULT 0,
    `prescription_image` VARCHAR(255) NOT NULL DEFAULT '',
    `original_filename` VARCHAR(255) NOT NULL DEFAULT '',
    `file_size` INTEGER NOT NULL DEFAULT 0,
    `extracted_content` TEXT,
    `follow_up_date` DATETIME NULL,
    `medication_frequency` VARCHAR(500) NOT NULL DEFAULT '',
    `medication_details` TEXT,
    `status` INTEGER NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 提醒记录表
CREATE TABLE IF NOT EXISTS `reminder` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL DEFAULT 0,
    `prescription_id` INTEGER NOT NULL DEFAULT 0,
    `reminder_type` VARCHAR(20) NOT NULL DEFAULT '',
    `reminder_time` DATETIME NOT NULL,
    `message` TEXT,
    `send_method` VARCHAR(20) NOT NULL DEFAULT 'wechat',
    `status` INTEGER NOT NULL DEFAULT 0,
    `sent_at` DATETIME NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 饮食记录表
CREATE TABLE IF NOT EXISTS `food_record` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL DEFAULT 0,
    `food_image` VARCHAR(255) NOT NULL DEFAULT '',
    `original_filename` VARCHAR(255) NOT NULL DEFAULT '',
    `file_size` INTEGER NOT NULL DEFAULT 0,
    `extracted_content` TEXT,
    `total_calories` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `meal_type` VARCHAR(20) NOT NULL DEFAULT '',
    `record_date` DATE NOT NULL,
    `status` INTEGER NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 饮食建议表
CREATE TABLE IF NOT EXISTS `diet_suggestion` (
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `user_id` INTEGER NOT NULL DEFAULT 0,
    `suggestion_date` DATE NOT NULL,
    `daily_calories` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    `suggestion_content` TEXT,
    `nutrition_analysis` TEXT,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 创建索引
CREATE INDEX IF NOT EXISTS `idx_prescription_user_id` ON `prescription` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_prescription_status` ON `prescription` (`status`);
CREATE INDEX IF NOT EXISTS `idx_prescription_follow_up_date` ON `prescription` (`follow_up_date`);

CREATE INDEX IF NOT EXISTS `idx_reminder_user_id` ON `reminder` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_reminder_prescription_id` ON `reminder` (`prescription_id`);
CREATE INDEX IF NOT EXISTS `idx_reminder_time` ON `reminder` (`reminder_time`);
CREATE INDEX IF NOT EXISTS `idx_reminder_status` ON `reminder` (`status`);

CREATE INDEX IF NOT EXISTS `idx_food_record_user_id` ON `food_record` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_food_record_date` ON `food_record` (`record_date`);
CREATE INDEX IF NOT EXISTS `idx_food_record_status` ON `food_record` (`status`);

CREATE INDEX IF NOT EXISTS `idx_diet_suggestion_user_id` ON `diet_suggestion` (`user_id`);
CREATE INDEX IF NOT EXISTS `idx_diet_suggestion_date` ON `diet_suggestion` (`suggestion_date`);

-- 插入测试用户数据
INSERT OR IGNORE INTO `user` (`id`, `username`, `phone`, `email`, `wechat_openid`, `status`) 
VALUES (1, '测试用户', '13800138000', 'test@example.com', 'test_openid_123', 1);