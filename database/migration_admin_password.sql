-- ============================================================
-- Migration: Admin username/password login (replaces email code login)
-- Database: ebookstore
-- Safe to import repeatedly.
-- Run: mysql -u root ebookstore < migration_admin_password.sql
-- ============================================================

USE `ebookstore`;

-- Password column on the existing admin table (plaintext, matching the
-- project's existing `user` table convention).
ALTER TABLE `admin`
    ADD COLUMN IF NOT EXISTS `password` VARCHAR(100) NOT NULL DEFAULT '' AFTER `vcode`;

-- Default passwords for the seeded admin accounts. Change them after
-- first login or via SQL:
--   UPDATE admin SET password='newpassword' WHERE email='...';
UPDATE `admin` SET `password` = 'admin123' WHERE `email` = 'sachinsunar2151@gmail.com' AND `password` = '';
UPDATE `admin` SET `password` = 'admin123' WHERE `email` = 'nena123maharjan@gmail.com' AND `password` = '';

-- Any other admin rows created without a password get a locked placeholder.
UPDATE `admin` SET `password` = CONCAT('LOCKED-', `email`) WHERE `password` = '';
