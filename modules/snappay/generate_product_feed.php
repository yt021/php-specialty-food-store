<?php

$root = dirname(__DIR__, 2);
$GLOBALS['bu'] = $root . '/';
$GLOBALS['dbc_adrs'] = 'modules/wdb/db_connection.php';

require_once $root . '/modules/wdb/db_config.php';
require_once $root . '/modules/wdb/db_connection.php';
require_once $root . '/modules/wdb/db_funcs.php';
require_once $root . '/modules/snappay/snappay_config.php';
require_once $root . '/modules/cart/cart_funcs.php';
require_once $root . '/modules/snappay/product_feed.php';

$is_cli = (PHP_SAPI === 'cli');
if (!$is_cli) {
    $token = defined('SNAPPAY_PRODUCT_FEED_CRON_TOKEN') ? (string)SNAPPAY_PRODUCT_FEED_CRON_TOKEN : '';
    $provided = isset($_GET['token']) ? (string)$_GET['token'] : '';
    if ($token === '' || !hash_equals($token, $provided)) {
        http_response_code(403);
        echo "Forbidden\n";
        exit(1);
    }
    header('Content-Type: text/plain; charset=utf-8');
}

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    echo "DB connection failed\n";
    exit(1);
}

$error = '';
$result = snappay_product_feed_write($mysqli, $error);
if ($result === false) {
    http_response_code(500);
    echo "Snappay product feed generation failed: " . $error . "\n";
    exit(1);
}

echo "Snappay product feed generated\n";
echo "Items: " . (int)$result['items_count'] . "\n";
echo "Path: " . $result['path'] . "\n";
echo "URL: " . $result['url'] . "\n";
echo "Generated at: " . $result['generated_at'] . "\n";

?>
