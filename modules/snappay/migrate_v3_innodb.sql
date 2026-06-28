-- SnappPay schema migration (v3): switch storage engine to InnoDB
-- Use this on existing installations that already have snappay tables.
--
-- Why:
-- - transaction safety
-- - row-level locking for concurrent callback/reconcile/admin operations
-- - better crash recovery

-- Optional pre-check:
-- SHOW TABLE STATUS WHERE Name IN ('snappay_transactions','snappay_errors');

ALTER TABLE `snappay_transactions` ENGINE=InnoDB;
ALTER TABLE `snappay_errors` ENGINE=InnoDB;

-- Optional post-check:
-- SHOW TABLE STATUS WHERE Name IN ('snappay_transactions','snappay_errors');

