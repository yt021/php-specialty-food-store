-- Add Sitemap as an admin module so it appears in admin sidebar and module list.
-- Safe to run multiple times.

INSERT INTO `admin_modules` (`name`, `flag`, `min_level`, `state_names`, `state_flags`)
SELECT 'Sitemap', 'sitemap', 2, NULL, NULL
WHERE NOT EXISTS (
    SELECT 1 FROM `admin_modules` WHERE `flag` = 'sitemap'
);

-- Grant access to Sitemap module for level>=2 admins that do not already have it.
UPDATE `admins`
SET `access_str` = CONCAT(
    IF(`access_str` IS NULL OR `access_str` = '', '', CONCAT(`access_str`, ',')),
    'sitemap'
)
WHERE `level` >= 2
  AND (`access_str` IS NULL OR `access_str` = '' OR FIND_IN_SET('sitemap', `access_str`) = 0);
