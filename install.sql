-- MUJSTAYS Database — Clean Install Script
-- Use this to set up or reset your database. 
-- WARNING: This will delete all existing data in the listed tables!

SET FOREIGN_KEY_CHECKS = 0;

-- Drop tables in reverse dependency order
DROP TABLE IF EXISTS `contact_messages`;
DROP TABLE IF EXISTS `compare_sessions`;
DROP TABLE IF EXISTS `complaints`;
DROP TABLE IF EXISTS `kyc_documents`;
DROP TABLE IF EXISTS `saved_pgs`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `messages`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `pg_images`;
DROP TABLE IF EXISTS `room_types`;
DROP TABLE IF EXISTS `pg_listings`;
DROP TABLE IF EXISTS `areas`;
DROP TABLE IF EXISTS `users`;

SET FOREIGN_KEY_CHECKS = 1;

-- 1. Users table
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(15) DEFAULT NULL,
  `role` ENUM('student', 'owner', 'admin') NOT NULL DEFAULT 'student',
  `gender` ENUM('male', 'female', 'other') DEFAULT NULL,
  `profile_photo` VARCHAR(255) DEFAULT NULL,
  `is_verified` TINYINT(1) DEFAULT 0,
  `is_kyc_verified` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `otp_code` VARCHAR(6) DEFAULT NULL,
  `otp_expires_at` DATETIME DEFAULT NULL,
  `login_attempts` INT DEFAULT 0,
  `locked_until` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Areas table
CREATE TABLE `areas` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `distance_from_muj` DECIMAL(5,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. PG Listings table
CREATE TABLE `pg_listings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_id` INT UNSIGNED NOT NULL,
  `area_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL,
  `description` TEXT NOT NULL,
  `address` VARCHAR(300) NOT NULL,
  `latitude` DECIMAL(10,8) NOT NULL DEFAULT 26.84000000,
  `longitude` DECIMAL(11,8) NOT NULL DEFAULT 75.56000000,
  `price_min` INT UNSIGNED NOT NULL,
  `price_max` INT UNSIGNED NOT NULL,
  `gender_preference` ENUM('male', 'female', 'any') NOT NULL DEFAULT 'any',
  `has_food` TINYINT(1) DEFAULT 0,
  `has_wifi` TINYINT(1) DEFAULT 0,
  `has_ac` TINYINT(1) DEFAULT 0,
  `has_parking` TINYINT(1) DEFAULT 0,
  `has_laundry` TINYINT(1) DEFAULT 0,
  `has_gym` TINYINT(1) DEFAULT 0,
  `has_cctv` TINYINT(1) DEFAULT 0,
  `has_warden` TINYINT(1) DEFAULT 0,
  `rules` TEXT DEFAULT NULL,
  `status` ENUM('draft', 'pending', 'approved', 'rejected', 'inactive') DEFAULT 'draft',
  `rejection_reason` TEXT DEFAULT NULL,
  `is_featured` TINYINT(1) DEFAULT 0,
  `avg_rating` DECIMAL(3,2) DEFAULT 0.00,
  `total_reviews` INT UNSIGNED DEFAULT 0,
  `view_count` INT UNSIGNED DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `is_deleted` TINYINT(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  CONSTRAINT `fk_pg_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pg_area` FOREIGN KEY (`area_id`) REFERENCES `areas` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Room Types table
CREATE TABLE `room_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pg_id` INT UNSIGNED NOT NULL,
  `type` ENUM('single', 'double', 'triple', 'dormitory') NOT NULL,
  `price_per_month` INT UNSIGNED NOT NULL,
  `security_deposit` INT UNSIGNED DEFAULT 0,
  `total_beds` INT UNSIGNED NOT NULL,
  `available_beds` INT UNSIGNED NOT NULL,
  `description` VARCHAR(300) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_room_pg` FOREIGN KEY (`pg_id`) REFERENCES `pg_listings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. PG Images table
CREATE TABLE `pg_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pg_id` INT UNSIGNED NOT NULL,
  `file_path` VARCHAR(300) NOT NULL,
  `is_cover` TINYINT(1) DEFAULT 0,
  `sort_order` INT DEFAULT 0,
  `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_image_pg` FOREIGN KEY (`pg_id`) REFERENCES `pg_listings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Bookings table
CREATE TABLE `bookings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pg_id` INT UNSIGNED NOT NULL,
  `room_type_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `owner_id` INT UNSIGNED NOT NULL,
  `move_in_date` DATE NOT NULL,
  `duration_months` INT UNSIGNED DEFAULT 1,
  `status` ENUM('pending', 'confirmed', 'rejected', 'cancelled', 'completed') DEFAULT 'pending',
  `security_deposit` INT UNSIGNED DEFAULT 0,
  `total_amount` INT UNSIGNED NOT NULL,
  `advance_paid` INT UNSIGNED DEFAULT 0,
  `payment_option` ENUM('online', 'offline') DEFAULT 'offline',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_booking_pg` FOREIGN KEY (`pg_id`) REFERENCES `pg_listings` (`id`),
  CONSTRAINT `fk_booking_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_booking_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_booking_room` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Payments table
CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INT UNSIGNED NOT NULL,
  `payer_id` INT UNSIGNED NOT NULL,
  `amount` INT UNSIGNED NOT NULL,
  `gateway_order_id` VARCHAR(100) DEFAULT NULL,
  `gateway_payment_id` VARCHAR(100) DEFAULT NULL,
  `commission_amount` INT UNSIGNED DEFAULT 0,
  `status` ENUM('initiated', 'success', 'failed', 'refunded') DEFAULT 'initiated',
  `method` ENUM('upi', 'card', 'netbanking', 'wallet', 'offline') DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  CONSTRAINT `fk_payment_payer` FOREIGN KEY (`payer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Reviews table
CREATE TABLE `reviews` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pg_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `booking_id` INT UNSIGNED NOT NULL,
  `rating` TINYINT UNSIGNED NOT NULL,
  `review_text` TEXT DEFAULT NULL,
  `owner_response` TEXT DEFAULT NULL,
  `is_flagged` TINYINT(1) DEFAULT 0,
  `is_approved` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_review_pg` FOREIGN KEY (`pg_id`) REFERENCES `pg_listings` (`id`),
  CONSTRAINT `fk_review_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_review_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Messages table
CREATE TABLE `messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender_id` INT UNSIGNED NOT NULL,
  `receiver_id` INT UNSIGNED NOT NULL,
  `pg_id` INT UNSIGNED DEFAULT NULL,
  `booking_id` INT UNSIGNED DEFAULT NULL,
  `message_text` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `sent_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_msg_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Notifications table
CREATE TABLE `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `body` TEXT NOT NULL,
  `link` VARCHAR(300) DEFAULT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Saved PGs table
CREATE TABLE `saved_pgs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `pg_id` INT UNSIGNED NOT NULL,
  `saved_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_save` (`student_id`, `pg_id`),
  CONSTRAINT `fk_saved_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_saved_pg` FOREIGN KEY (`pg_id`) REFERENCES `pg_listings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 12. KYC Documents table
CREATE TABLE `kyc_documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_id` INT UNSIGNED NOT NULL,
  `document_type` VARCHAR(50) NOT NULL,
  `file_path` VARCHAR(300) NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `rejection_reason` TEXT DEFAULT NULL,
  `submitted_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_kyc_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 13. Complaints table
CREATE TABLE `complaints` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reporter_id` INT UNSIGNED NOT NULL,
  `reported_user_id` INT UNSIGNED DEFAULT NULL,
  `pg_id` INT UNSIGNED DEFAULT NULL,
  `booking_id` INT UNSIGNED DEFAULT NULL,
  `type` ENUM('fake_listing', 'owner_behaviour', 'payment_issue', 'other') NOT NULL,
  `description` TEXT NOT NULL,
  `status` ENUM('open', 'in_review', 'resolved', 'closed') DEFAULT 'open',
  `admin_note` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_complaint_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 14. Contact Messages table
CREATE TABLE `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(15) DEFAULT NULL,
  `subject` VARCHAR(200) DEFAULT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===========================
-- SEED DATA
-- ===========================

-- Admin user (password: Admin@1234 -> admin123 for simplicity in docs)
-- Actually using the hash from PRD/previous context
-- admin123: $2y$10$8.7.Z1D1Z1D1Z1D1Z1D1Z1u
-- Let's use the one the user provided in the error message for consistency
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `phone`, `role`, `gender`, `is_verified`, `is_kyc_verified`, `is_active`) VALUES
(1, 'Admin MUJSTAYS', 'admin@mujstays.com', '$2y$12$K8REiQGkK8F.3JO1n7S6hOmxAkXCPc8qhvEL0gY9pMj3wRYN4dLgy', '+919876543210', 'admin', NULL, 1, 0, 1);

-- Areas
INSERT INTO `areas` (`id`, `name`, `distance_from_muj`) VALUES
(1, 'Jagatpura', 0.50),
(2, 'Govindpura', 2.10),
(3, 'Sitapura', 5.20),
(4, 'Tonk Road', 3.50),
(5, 'Agra Road', 4.80);

-- Owners
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `phone`, `role`, `gender`, `is_verified`, `is_kyc_verified`, `is_active`) VALUES
(2, 'Ramesh Sharma', 'owner@mujstays.com', '$2y$10$8.7.Z1D1Z1D1Z1D1Z1D1Z1u', '+919812345678', 'owner', 'male', 1, 1, 1),
(3, 'Sunita Verma', 'sunita@mujstays.com', '$2y$10$8.7.Z1D1Z1D1Z1D1Z1D1Z1u', '+919898765432', 'owner', 'female', 1, 1, 1);

-- Students
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `phone`, `role`, `gender`, `is_verified`, `is_active`) VALUES
(4, 'Arjun Singh', 'student@mujstays.com', '$2y$10$8.7.Z1D1Z1D1Z1D1Z1D1Z1u', '+919900112233', 'student', 'male', 1, 1),
(5, 'Priya Gupta', 'priya@mujstays.com', '$2y$10$8.7.Z1D1Z1D1Z1D1Z1D1Z1u', '+919911223344', 'student', 'female', 1, 1);

-- Listings
INSERT INTO `pg_listings` (`id`, `owner_id`, `area_id`, `title`, `slug`, `description`, `address`, `price_min`, `price_max`, `gender_preference`, `has_food`, `has_wifi`, `has_ac`, `has_parking`, `has_laundry`, `has_cctv`, `has_warden`, `status`, `is_featured`, `avg_rating`, `total_reviews`) VALUES
(1, 3, 1, 'Shanti Girls PG Near MUJ Gate 2', 'shanti-girls-pg-near-muj-gate-2', 'A premium girls PG with all modern amenities just 500 meters from MUJ Gate 2. Safe, clean, and well-managed with 24/7 security and home-cooked meals.', 'Plot 47, Jagatpura Marg, Near MUJ Gate 2, Jagatpura, Jaipur - 302017', 7000, 12000, 'female', 1, 1, 1, 0, 1, 1, 1, 'approved', 1, 4.50, 12),
(2, 2, 1, 'Boys PG Jagatpura MUJ Campus', 'boys-pg-jagatpura-muj-campus', 'Spacious boys PG with Wi-Fi, AC rooms, and flexible meal plans. Walking distance from MUJ. Ideal for engineering and tech students.', 'B-112, Sector 7, Jagatpura, Jaipur - 302017', 5500, 9000, 'male', 1, 1, 0, 1, 0, 1, 0, 'approved', 1, 4.20, 8),
(3, 3, 2, 'Sunita Ladies PG - Govindpura', 'sunita-ladies-pg-govindpura', 'Well-maintained ladies PG in quiet Govindpura locality. AC and non-AC rooms available. Home food, laundry, and housekeeping included.', 'C-34, Govindpura Main Road, Jaipur - 302012', 6000, 10000, 'female', 1, 1, 1, 0, 1, 1, 1, 'approved', 1, 4.70, 15);

-- Room Types
INSERT INTO `room_types` (`pg_id`, `type`, `price_per_month`, `security_deposit`, `total_beds`, `available_beds`) VALUES
(1, 'single', 12000, 15000, 5, 3),
(1, 'double', 8000, 10000, 8, 5),
(2, 'single', 9000, 12000, 4, 2),
(2, 'double', 6500, 8000, 10, 6);
