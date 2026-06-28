<?php
require_once __DIR__ . '/../../modules/wdb/db_config.php';
require_once __DIR__ . '/../../modules/wdb/db_connection.php';
require_once __DIR__ . '/pinket-config.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/products.php';
require_once __DIR__ . '/categories.php';

file_put_contents(__DIR__ . '/debug_api_send.log', date('Y-m-d H:i:s') . " [CRON] 24h product sync started\n", FILE_APPEND);
$resultProds = sendProductsToPinket($mysqli);
file_put_contents(__DIR__ . '/debug_api_send.log', date('Y-m-d H:i:s') . " [CRON] 24h product sync result: " . json_encode($resultProds) . "\n", FILE_APPEND);

file_put_contents(__DIR__ . '/debug_api_send.log', date('Y-m-d H:i:s') . " [CRON] 24h category sync started\n", FILE_APPEND);
$resultCats = sendCategoriesToPinket($mysqli);
file_put_contents(__DIR__ . '/debug_api_send.log', date('Y-m-d H:i:s') . " [CRON] 24h category sync result: " . json_encode($resultCats) . "\n", FILE_APPEND);

echo "Product and category sync done.\n"; 