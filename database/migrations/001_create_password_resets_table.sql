-- Migration: Create password_resets table for OTP verification
-- Run this SQL in your database

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `user_id` INT(11) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `reset_token` VARCHAR(64) NOT NULL,
    `otp_code` VARCHAR(6) NULL,
    `expires_at` DATETIME NOT NULL,
    `verified_at` DATETIME NULL,
    `used_at` DATETIME NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_reset_token` (`reset_token`),
    INDEX `idx_otp_code` (`otp_code`),
    CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
