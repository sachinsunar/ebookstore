-- ============================================================
-- Migration: Replace PayHere with eSewa (gateway-neutral payments)
-- Database: ebookstore
-- Safe to import repeatedly (uses IF NOT EXISTS / guarded updates).
-- Run: mysql -u root ebookstore < migration_esewa_payment.sql
-- ============================================================

USE `ebookstore`;

-- 1. Gateway-neutral payment columns on the existing `invoice` (order) table.
--    Naming follows the project's snake_case convention.
ALTER TABLE `invoice`
    ADD COLUMN IF NOT EXISTS `transaction_uuid` VARCHAR(64) NULL DEFAULT NULL AFTER `order_id`,
    ADD COLUMN IF NOT EXISTS `payment_method`   VARCHAR(30) NOT NULL DEFAULT 'esewa' AFTER `total`,
    ADD COLUMN IF NOT EXISTS `payment_status`   VARCHAR(15) NOT NULL DEFAULT 'PENDING' AFTER `payment_method`,
    ADD COLUMN IF NOT EXISTS `transaction_code` VARCHAR(50) NULL DEFAULT NULL AFTER `payment_status`,
    ADD COLUMN IF NOT EXISTS `paid_at`          DATETIME NULL DEFAULT NULL AFTER `transaction_code`;

-- 2. One unique transaction UUID per payment attempt (prevents replay/duplicates).
ALTER TABLE `invoice`
    ADD UNIQUE INDEX IF NOT EXISTS `uq_invoice_transaction_uuid` (`transaction_uuid`);

-- 3. Index for finding a user's unpaid order rows quickly (retry + duplicate checks).
ALTER TABLE `invoice`
    ADD INDEX IF NOT EXISTS `idx_invoice_payment_status` (`user_email`, `product_id`, `payment_status`);

-- 4. Historical orders were created only AFTER successful payment in the old
--    PayHere flow, so they are already paid. Mark them so they are not shown
--    as unpaid. New eSewa orders always carry a transaction_uuid.
UPDATE `invoice`
SET `payment_method` = 'legacy',
    `payment_status` = 'PAID',
    `paid_at`        = `date`
WHERE `transaction_uuid` IS NULL;

-- 5. Payment status values used by the application:
--    PENDING | PAID | FAILED | CANCELLED
--    Delivery state remains untouched in `order_status`
--    (1=Waiting for accept, 2=Order Placed, 3=Delivered).
