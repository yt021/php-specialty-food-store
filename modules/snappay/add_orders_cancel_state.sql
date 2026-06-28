-- Add a new "cancelled" state for `orders.state` (used after SnappPay Cancel).
--
-- IMPORTANT: This project maps order state like this:
--   orders.state = admin_orders_state.id - 1
-- So the numeric value you must put in `SNAPPAY_ORDER_STATE_CANCELLED` is:
--   (id of admin_orders_state row with flag='cancelled') - 1
--
-- This script assigns a new id automatically as (MAX(id)+1) unless 'cancelled' already exists.
-- Run the SELECTs first to confirm you don't already have a cancelled state.

-- Pre-checks
-- SELECT id, name, flag FROM admin_orders_state ORDER BY id;
-- SELECT flag, state_flags, state_names FROM admin_modules WHERE flag = 'orders';

-- 1) Add admin_orders_state row (id = MAX(id)+1 => orders.state = that_id - 1)
SET @snappay_cancel_state_id := (
  SELECT COALESCE(MAX(`id`), 0) + 1
  FROM `admin_orders_state`
);

INSERT INTO `admin_orders_state` (`id`, `name`, `flag`, `submit_text`)
SELECT @snappay_cancel_state_id, 'لغو شده', 'cancelled', NULL
WHERE NOT EXISTS (SELECT 1 FROM `admin_orders_state` WHERE `flag` = 'cancelled');

-- 2) Make admin UI aware of the new state by appending to the module lists.
--    This is required because code uses admin_modules.state_flags/state_names to map numeric state -> label.
UPDATE `admin_modules`
SET
  `state_flags` = CONCAT(TRIM(TRAILING ',' FROM TRIM(`state_flags`)), ',cancelled'),
  `state_names` = CONCAT(TRIM(TRAILING ',' FROM TRIM(`state_names`)), ',لغو شده')
WHERE `flag` = 'orders'
  AND FIND_IN_SET('cancelled', REPLACE(`state_flags`, ' ', '')) = 0;

-- Post-check
-- SELECT id, name, flag FROM admin_orders_state ORDER BY id;
-- SELECT flag, state_flags, state_names FROM admin_modules WHERE flag = 'orders';

-- Recommended config value:
-- SELECT CONCAT('SNAPPAY_ORDER_STATE_CANCELLED = ', id-1) AS recommended
-- FROM admin_orders_state WHERE flag='cancelled' LIMIT 1;
