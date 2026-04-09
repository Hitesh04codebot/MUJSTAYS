-- MUJSTAYS Database Schema
-- Database: mujstays_db
-- Engine: InnoDB | Charset: utf8mb4

CREATE DATABASE IF NOT EXISTS mujstays_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mujstays_db;

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(15) DEFAULT NULL,
  `role` ENUM('student','owner','admin') NOT NULL DEFAULT 'student',
  `gender` ENUM('male','female','other') DEFAULT NULL,
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

-- PG Listings table
CREATE TABLE IF NOT EXISTS `pg_listings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_id` INT UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(220) NOT NULL,
  `description` TEXT NOT NULL,
  `address` VARCHAR(300) NOT NULL,
  `area_name` VARCHAR(100) NOT NULL,
  `latitude` DECIMAL(10,8) NOT NULL DEFAULT 26.84000000,
  `longitude` DECIMAL(11,8) NOT NULL DEFAULT 75.56000000,
  `distance_from_muj` DECIMAL(5,2) DEFAULT NULL,
  `price_min` INT UNSIGNED NOT NULL,
  `price_max` INT UNSIGNED NOT NULL,
  `gender_preference` ENUM('male','female','any') NOT NULL DEFAULT 'any',
  `has_food` TINYINT(1) DEFAULT 0,
  `has_wifi` TINYINT(1) DEFAULT 0,
  `has_ac` TINYINT(1) DEFAULT 0,
  `has_parking` TINYINT(1) DEFAULT 0,
  `has_laundry` TINYINT(1) DEFAULT 0,
  `has_gym` TINYINT(1) DEFAULT 0,
  `has_cctv` TINYINT(1) DEFAULT 0,
  `has_warden` TINYINT(1) DEFAULT 0,
  `rules` TEXT DEFAULT NULL,
  `status` ENUM('draft','pending','approved','rejected','inactive') DEFAULT 'draft',
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
  KEY `owner_id` (`owner_id`),
  KEY `status` (`status`),
  KEY `area_name` (`area_name`),
  KEY `is_featured` (`is_featured`),
  CONSTRAINT `fk_pg_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Room Types table
CREATE TABLE IF NOT EXISTS `room_types` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pg_id` INT UNSIGNED NOT NULL,
  `type` ENUM('single','double','triple','dormitory') NOT NULL,
  `price_per_month` INT UNSIGNED NOT NULL,
  `security_deposit` INT UNSIGNED DEFAULT 0,
  `total_beds` INT UNSIGNED NOT NULL,
  `available_beds` INT UNSIGNED NOT NULL,
  `description` VARCHAR(300) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pg_id` (`pg_id`),
  CONSTRAINT `fk_room_pg` FOREIGN KEY (`pg_id`) REFERENCES `pg_listings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PG Images table
CREATE TABLE IF NOT EXISTS `pg_images` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pg_id` INT UNSIGNED NOT NULL,
  `file_path` VARCHAR(300) NOT NULL,
  `is_cover` TINYINT(1) DEFAULT 0,
  `sort_order` INT DEFAULT 0,
  `uploaded_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pg_id` (`pg_id`),
  CONSTRAINT `fk_image_pg` FOREIGN KEY (`pg_id`) REFERENCES `pg_listings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings table
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pg_id` INT UNSIGNED NOT NULL,
  `room_type_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `owner_id` INT UNSIGNED NOT NULL,
  `move_in_date` DATE NOT NULL,
  `duration_months` INT UNSIGNED DEFAULT 1,
  `status` ENUM('pending','confirmed','rejected','cancelled','completed') DEFAULT 'pending',
  `booking_type` ENUM('request','instant') NOT NULL,
  `rejection_reason` TEXT DEFAULT NULL,
  `total_amount` INT UNSIGNED NOT NULL,
  `advance_paid` INT UNSIGNED DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `pg_id` (`pg_id`),
  KEY `student_id` (`student_id`),
  KEY `owner_id` (`owner_id`),
  KEY `room_type_id` (`room_type_id`),
  CONSTRAINT `fk_booking_pg` FOREIGN KEY (`pg_id`) REFERENCES `pg_listings` (`id`),
  CONSTRAINT `fk_booking_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_booking_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_booking_room` FOREIGN KEY (`room_type_id`) REFERENCES `room_types` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payments table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` INT UNSIGNED NOT NULL,
  `payer_id` INT UNSIGNED NOT NULL,
  `amount` INT UNSIGNED NOT NULL,
  `gateway_order_id` VARCHAR(100) DEFAULT NULL,
  `gateway_payment_id` VARCHAR(100) DEFAULT NULL,
  `commission_amount` INT UNSIGNED DEFAULT 0,
  `status` ENUM('initiated','success','failed','refunded') DEFAULT 'initiated',
  `method` ENUM('upi','card','netbanking','wallet','offline') DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `payer_id` (`payer_id`),
  CONSTRAINT `fk_payment_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`),
  CONSTRAINT `fk_payment_payer` FOREIGN KEY (`payer_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reviews table
CREATE TABLE IF NOT EXISTS `reviews` (
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
  KEY `pg_id` (`pg_id`),
  KEY `student_id` (`student_id`),
  KEY `booking_id` (`booking_id`),
  CONSTRAINT `fk_review_pg` FOREIGN KEY (`pg_id`) REFERENCES `pg_listings` (`id`),
  CONSTRAINT `fk_review_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_review_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages table
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sender_id` INT UNSIGNED NOT NULL,
  `receiver_id` INT UNSIGNED NOT NULL,
  `pg_id` INT UNSIGNED DEFAULT NULL,
  `booking_id` INT UNSIGNED DEFAULT NULL,
  `message_text` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `sent_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  KEY `pg_id` (`pg_id`),
  CONSTRAINT `fk_msg_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_msg_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `type` ENUM('booking_update','booking_request','payment_receipt','payment_received','new_pg_nearby','review_posted','kyc_approved','kyc_rejected','listing_approved','listing_rejected','complaint_resolved','announcement') NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `body` TEXT NOT NULL,
  `link` VARCHAR(300) DEFAULT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Saved PGs table
CREATE TABLE IF NOT EXISTS `saved_pgs` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `pg_id` INT UNSIGNED NOT NULL,
  `saved_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_save` (`student_id`,`pg_id`),
  CONSTRAINT `fk_saved_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_saved_pg` FOREIGN KEY (`pg_id`) REFERENCES `pg_listings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- KYC Documents table
CREATE TABLE IF NOT EXISTS `kyc_documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_id` INT UNSIGNED NOT NULL,
  `doc_type` ENUM('aadhar','pan','passport','voter_id') NOT NULL,
  `file_path` VARCHAR(300) NOT NULL,
  `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` INT UNSIGNED DEFAULT NULL,
  `reviewed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`),
  CONSTRAINT `fk_kyc_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Complaints table
CREATE TABLE IF NOT EXISTS `complaints` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reporter_id` INT UNSIGNED NOT NULL,
  `reported_user_id` INT UNSIGNED DEFAULT NULL,
  `pg_id` INT UNSIGNED DEFAULT NULL,
  `booking_id` INT UNSIGNED DEFAULT NULL,
  `type` ENUM('fake_listing','owner_behaviour','payment_issue','other') NOT NULL,
  `description` TEXT NOT NULL,
  `status` ENUM('open','in_review','resolved','closed') DEFAULT 'open',
  `admin_note` TEXT DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reporter_id` (`reporter_id`),
  CONSTRAINT `fk_complaint_reporter` FOREIGN KEY (`reporter_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Compare Sessions table
CREATE TABLE IF NOT EXISTS `compare_sessions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `pg_ids` JSON NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `fk_compare_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contact Messages table
CREATE TABLE IF NOT EXISTS `contact_messages` (
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

-- Admin user (password: Admin@1234)
INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `role`, `gender`, `is_verified`, `is_kyc_verified`, `is_active`) VALUES
('Admin MUJSTAYS', 'admin@mujstays.com', '$2y$12$K8REiQGkK8F.3JO1n7S6hOmxAkXCPc8qhvEL0gY9pMj3wRYN4dLgy', '+919876543210', 'admin', NULL, 1, 0, 1);

-- Sample PG Owner (password: Owner@1234)
INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `role`, `gender`, `is_verified`, `is_kyc_verified`, `is_active`) VALUES
('Ramesh Sharma', 'owner@mujstays.com', '$2y$12$TkL8sP2W5v8F.QeXLm2NjOkiJHGM8cB0yMDkXVxWz5Ak9UvHFYOai', '+919812345678', 'owner', 'male', 1, 1, 1),
('Sunita Verma', 'sunita@mujstays.com', '$2y$12$TkL8sP2W5v8F.QeXLm2NjOkiJHGM8cB0yMDkXVxWz5Ak9UvHFYOai', '+919898765432', 'owner', 'female', 1, 1, 1);

-- Sample Student (password: Student@1234)
INSERT INTO `users` (`name`, `email`, `password_hash`, `phone`, `role`, `gender`, `is_verified`, `is_active`) VALUES
('Arjun Singh', 'student@mujstays.com', '$2y$12$abc123def456ghi789jklLmno123PqrStu456VwxYz789AbcDef012', '+919900112233', 'student', 'male', 1, 1),
('Priya Gupta', 'priya@mujstays.com', '$2y$12$abc123def456ghi789jklLmno123PqrStu456VwxYz789AbcDef012', '+919911223344', 'student', 'female', 1, 1);

-- Sample PG Listings
INSERT INTO `pg_listings` (`owner_id`, `title`, `slug`, `description`, `address`, `area_name`, `latitude`, `longitude`, `distance_from_muj`, `price_min`, `price_max`, `gender_preference`, `has_food`, `has_wifi`, `has_ac`, `has_parking`, `has_laundry`, `has_cctv`, `has_warden`, `rules`, `status`, `is_featured`, `avg_rating`, `total_reviews`) VALUES
(2, 'Shanti Girls PG Near MUJ Gate 2', 'shanti-girls-pg-near-muj-gate-2', 'A premium girls PG with all modern amenities just 500 meters from MUJ Gate 2. Safe, clean, and well-managed with 24/7 security and home-cooked meals.', 'Plot 47, Jagatpura Marg, Near MUJ Gate 2, Jagatpura, Jaipur - 302017', 'Jagatpura', 26.84582000, 75.80123000, 0.50, 7000, 12000, 'female', 1, 1, 1, 0, 1, 1, 1, 'No male visitors after 8 PM. No loud music. Curfew at 10 PM.', 'approved', 1, 4.50, 12),
(2, 'Boys PG Jagatpura MUJ Campus', 'boys-pg-jagatpura-muj-campus', 'Spacious boys PG with Wi-Fi, AC rooms, and flexible meal plans. Walking distance from MUJ. Ideal for engineering and tech students.', 'B-112, Sector 7, Jagatpura, Jaipur - 302017', 'Jagatpura', 26.84100000, 75.80456000, 0.80, 5500, 9000, 'male', 1, 1, 0, 1, 0, 1, 0, 'No alcohol. No smoking on premises. Gate closes at 11 PM.', 'approved', 1, 4.20, 8),
(3, 'Sunita Ladies PG - Govindpura', 'sunita-ladies-pg-govindpura', 'Well-maintained ladies PG in quiet Govindpura locality. AC and non-AC rooms available. Home food, laundry, and housekeeping included.', 'C-34, Govindpura Main Road, Jaipur - 302012', 'Govindpura', 26.89234000, 75.78901000, 2.10, 6000, 10000, 'female', 1, 1, 1, 0, 1, 1, 1, 'No late-night outings without permission. Kitchen access 7 AM-10 PM.', 'approved', 1, 4.70, 15),
(2, 'MUJ Student PG - Tonk Road', 'muj-student-pg-tonk-road', 'Budget-friendly PG for students on Tonk Road with easy MUJ bus connectivity. Clean rooms, fast Wi-Fi, and a great study environment.', '77-A, Tonk Road, Near Sanganer Chauraha, Jaipur - 302029', 'Tonk Road', 26.82100000, 75.81200000, 3.50, 4500, 7000, 'male', 0, 1, 0, 1, 0, 1, 0, 'Study hours 9 PM to 11 PM. No external visitors.', 'approved', 0, 3.90, 5),
(3, 'Comfort Zone PG - Sitapura', 'comfort-zone-pg-sitapura', 'Modern PG in Sitapura Industrial Area with gym, CCTV, and AC rooms. Easy connectivity to MUJ via auto/cab. Both single and double rooms available.', 'E-78, Sitapura Industrial Area, Jaipur - 302022', 'Sitapura', 26.76500000, 75.85600000, 5.20, 6500, 11000, 'any', 1, 1, 1, 1, 1, 1, 0, 'Maintain cleanliness. No pets allowed.', 'approved', 1, 4.10, 9),
(2, 'Agra Road Budget PG for Students', 'agra-road-budget-pg-students', 'Affordable PG accommodation for MUJ students on Agra Road. Homely environment, nutritious meals, and a supportive community.', '12, Ram Nagar Colony, Agra Road, Jaipur - 302002', 'Agra Road', 26.93200000, 75.81750000, 4.80, 4000, 6500, 'any', 1, 1, 0, 0, 0, 0, 1, 'Meal timings: 8 AM, 1 PM, 8 PM. Vegetarian food only.', 'approved', 0, 4.00, 6);

-- Sample Room Types
INSERT INTO `room_types` (`pg_id`, `type`, `price_per_month`, `security_deposit`, `total_beds`, `available_beds`, `description`) VALUES
(1, 'single', 12000, 15000, 5, 3, 'Fully furnished single AC room with attached bathroom'),
(1, 'double', 8000, 10000, 8, 5, 'Shared double room with common bathroom'),
(1, 'triple', 7000, 8000, 6, 2, 'Triple sharing room - economical option'),
(2, 'single', 9000, 12000, 4, 2, 'AC single room with study table'),
(2, 'double', 6500, 8000, 10, 6, 'Non-AC double sharing room'),
(2, 'dormitory', 5500, 6000, 12, 8, '4-bed dormitory with lockers'),
(3, 'single', 10000, 12000, 6, 4, 'AC single room with attached bathroom and balcony'),
(3, 'double', 7500, 9000, 8, 3, 'Spacious double room with common bathroom'),
(4, 'single', 7000, 8000, 3, 1, 'Non-AC single room with ceiling fan'),
(4, 'double', 5500, 6000, 8, 4, 'Double sharing non-AC room'),
(4, 'triple', 4500, 5000, 6, 3, 'Triple sharing budget room'),
(5, 'single', 11000, 13000, 5, 3, 'Premium AC single room with gym access'),
(5, 'double', 8000, 10000, 8, 5, 'AC double room with attached bathroom'),
(6, 'single', 6500, 7000, 4, 2, 'Budget single room with basic amenities'),
(6, 'double', 5000, 5500, 8, 6, 'Double sharing budget room'),
(6, 'triple', 4000, 4500, 6, 4, 'Triple sharing most affordable option');
