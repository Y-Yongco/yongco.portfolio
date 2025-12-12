-- SQL for ECOSCAN database

CREATE DATABASE IF NOT EXISTS `ecoscan` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecoscan`;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `admin_code` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Security questions table
CREATE TABLE IF NOT EXISTS `security_questions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `pet_name` VARCHAR(150) DEFAULT NULL,
  `birth_city` VARCHAR(150) DEFAULT NULL,
  `mother_maiden_name` VARCHAR(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_sec_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Analyses table
CREATE TABLE IF NOT EXISTS `analyses` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `image_path` VARCHAR(500) DEFAULT NULL,
  `detected_objects` TEXT DEFAULT NULL,
  `analysis_method` VARCHAR(50) DEFAULT NULL,
  `recommendations` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_analyses_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
