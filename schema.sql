-- =========================================================================
-- SaNDS Lab & Al Rabeeh Dental Center EMRS Document Portal
-- Enterprise MySQL Database Schema (MySQL 8.0+ / MariaDB 10.4+)
-- Database: alrabeeh_track_db
-- =========================================================================

-- 1. Authorized Stakeholders Registry
CREATE TABLE IF NOT EXISTS `authorized_users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(191) NOT NULL UNIQUE,
  `full_name` VARCHAR(255) NOT NULL,
  `organization` VARCHAR(255) NOT NULL,
  `role` VARCHAR(100) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Authenticated Remembered Devices (256-bit Token Engine)
CREATE TABLE IF NOT EXISTS `authenticated_devices` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(191) NOT NULL,
  `device_token` VARCHAR(255) NOT NULL UNIQUE,
  `device_name` VARCHAR(255),
  `user_agent` TEXT,
  `ip_address` VARCHAR(64),
  `location` VARCHAR(255),
  `verified_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `last_active` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_dev_email` (`email`),
  INDEX `idx_dev_token` (`device_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Document Lifecycle & Lock States
CREATE TABLE IF NOT EXISTS `document_meta` (
  `doc_id` VARCHAR(100) PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `status` VARCHAR(50) DEFAULT 'IN_REVIEW',
  `finalized_by` VARCHAR(191),
  `finalized_at` DATETIME,
  `finalized_notes` TEXT,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Digital Signatures & Disagreements (Isolated Strictly Per doc_id and email)
CREATE TABLE IF NOT EXISTS `document_signatures` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `doc_id` VARCHAR(100) NOT NULL,
  `email` VARCHAR(191) NOT NULL,
  `full_name` VARCHAR(255),
  `organization` VARCHAR(255),
  `role` VARCHAR(100),
  `status` VARCHAR(50) DEFAULT 'SIGNED', -- 'SIGNED' or 'DISAGREED'
  `signature_data` MEDIUMTEXT,
  `disagree_reason` TEXT,
  `ip_address` VARCHAR(64),
  `device_name` VARCHAR(255),
  `signed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_doc_user` (`doc_id`, `email`),
  INDEX `idx_sig_doc` (`doc_id`),
  INDEX `idx_sig_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Comprehensive Access Audit Logs (Tracking HTML Reads & PDF Downloads)
CREATE TABLE IF NOT EXISTS `document_access_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(191),
  `doc_id` VARCHAR(100),
  `doc_title` VARCHAR(255),
  `action_type` VARCHAR(100), -- 'VIEW_HTML', 'DOWNLOAD_PDF', 'LOGIN_VERIFIED', 'PORTAL_ACCESS'
  `ip_address` VARCHAR(64),
  `device_name` VARCHAR(255),
  `user_agent` TEXT,
  `location` VARCHAR(255),
  `accessed_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_log_email` (`email`),
  INDEX `idx_log_doc` (`doc_id`),
  INDEX `idx_log_action` (`action_type`),
  INDEX `idx_log_time` (`accessed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. OTP Verification Logs
CREATE TABLE IF NOT EXISTS `otp_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(191) NOT NULL,
  `otp_code` VARCHAR(10) NOT NULL,
  `ip_address` VARCHAR(64),
  `location` VARCHAR(255),
  `status` VARCHAR(50) DEFAULT 'SENT',
  `dispatched_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_otp_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Geolocation Fast Cache
CREATE TABLE IF NOT EXISTS `ip_cache` (
  `ip` VARCHAR(64) PRIMARY KEY,
  `location` VARCHAR(255),
  `cached_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial Seed Data: Authorized Stakeholders
INSERT INTO `authorized_users` (`email`, `full_name`, `organization`, `role`, `is_active`) VALUES
('ajit@sandslab.com', 'Ajit Kumar KV', 'SaNDS Lab Middle East W.L.L', 'Super Admin', 1),
('projects@sandslab.com', 'SaNDS Lab Administration', 'SaNDS Lab Middle East W.L.L', 'Admin', 1),
('info@sandslab.com', 'SaNDS Lab Middle East W.L.L', 'SaNDS Lab Middle East W.L.L', 'Admin', 1),
('director@alrabeeh.bh', 'Managing Director', 'Al Rabeeh Dental Center', 'Client Director', 1),
('management@alrabeeh.bh', 'Executive Management Team', 'Al Rabeeh Dental Center', 'Client Executive', 1),
('it@alrabeeh.bh', 'IT & Systems Team', 'Al Rabeeh Dental Center', 'Client IT', 1),
('clinical@alrabeeh.bh', 'Clinical & Medical Director', 'Al Rabeeh Dental Center', 'Clinical Lead', 1)
ON DUPLICATE KEY UPDATE `full_name`=VALUES(`full_name`);

-- Initial Seed Data: Document Metadata
INSERT INTO `document_meta` (`doc_id`, `title`, `status`) VALUES
('SL-ARDC-EMRS-2026-V1', 'Technical & Commercial Proposal: Al Rabeeh Dental Center EMRS (11 Modules & 12 Milestones)', 'IN_REVIEW'),
('ARDC-RESOURCE-PRICING-2026', 'Al Rabeeh Dental Center EMRS - Resource Cost & 12-Milestone Pricing Matrix', 'IN_REVIEW'),
('ARDC-EMRS-FLOWCHART-2026', 'Al Rabeeh EMRS Architectural Flow Chart (11 Modules & 12 Milestones)', 'IN_REVIEW'),
('ARDC-NAV-SEQUENCE-2026', '11-Module Unified Navigation Sequence Flow Chart', 'IN_REVIEW'),
('ARDC-INTERNAL-REF-2026', 'Resource Cost, Executive Advisory & Complete Pricing Reference', 'IN_REVIEW'),
('ARDC-SOFTWARE-BLUEPRINT', 'Reception Login Software Development Blueprint (35-Page Specification)', 'IN_REVIEW')
ON DUPLICATE KEY UPDATE `title`=VALUES(`title`);

