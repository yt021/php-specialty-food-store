-- Add Snappay as an admin module so it appears in the admin sidebar.
-- Safe to run multiple times.

INSERT INTO `admin_modules` (`name`, `flag`, `min_level`, `state_names`, `state_flags`)
SELECT 'Snappay', 'snappay', 2, NULL, NULL
WHERE NOT EXISTS (
    SELECT 1 FROM `admin_modules` WHERE `flag` = 'snappay'
);

-- Grant access to Snappay module for level>=2 admins that do not already have it.
-- Note: access control is based on admins.access_str in this codebase.
UPDATE `admins`
SET `access_str` = CONCAT(
    IF(`access_str` IS NULL OR `access_str` = '', '', CONCAT(`access_str`, ',')),
    'snappay'
)
WHERE `level` >= 2
  AND (`access_str` IS NULL OR `access_str` = '' OR FIND_IN_SET('snappay', `access_str`) = 0);
