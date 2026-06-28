-- SnappPay schema migration (v2)
-- Use this ONLY if you already created the tables from an older schema.sql.
-- For storage engine upgrade to InnoDB, run migrate_v3_innodb.sql after this script.

-- 1) Pre-check for duplicates (must be 0 rows before adding UNIQUE(oid,attempt_no))
-- SELECT oid, attempt_no, COUNT(*) c FROM snappay_transactions GROUP BY oid, attempt_no HAVING c > 1;

-- 2) Expand amount columns to BIGINT UNSIGNED (toman + IRR)
ALTER TABLE `snappay_transactions`
  MODIFY `amount_toman` bigint unsigned NOT NULL,
  MODIFY `amount_irr` bigint unsigned NOT NULL,
  MODIFY `callback_amount_irr` bigint unsigned DEFAULT NULL,
  MODIFY `status_amount_irr` bigint unsigned DEFAULT NULL;

-- 3) Make updated_at auto-update (keeps existing app updates working too)
ALTER TABLE `snappay_transactions`
  MODIFY `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- 4) Add idempotency + performance indexes
-- If these fail due to "Duplicate key name", you already have them (safe to ignore).
ALTER TABLE `snappay_transactions`
  ADD UNIQUE KEY `snappay_oid_attempt_uq` (`oid`,`attempt_no`),
  ADD KEY `snappay_oid_id_idx` (`oid`,`id`);

-- 5) Errors table: index (tx_id, created_at) for faster debugging
ALTER TABLE `snappay_errors`
  ADD KEY `snappay_errors_tx_created_idx` (`snappay_tx_id`,`created_at`);
