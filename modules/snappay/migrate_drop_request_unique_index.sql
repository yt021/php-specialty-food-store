-- Drop the legacy unique index that prevents multiple requests of the same type
-- for the same order after an earlier request was approved or denied.
--
-- Safe to run multiple times. On MySQL 8+, IF EXISTS avoids an error if the
-- index has already been removed.

ALTER TABLE `snappay_requests`
  DROP INDEX IF EXISTS `unique_order_request`;
