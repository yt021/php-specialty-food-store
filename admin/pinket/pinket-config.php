<?php
// Pinket is intentionally disabled in the public showcase.
define('PINKET_ENABLED', false);
define('PINKET_BASE_URL', 'https://example.invalid');
define('PINKET_INVENTORY_URL', 'https://example.invalid');
define('PINKET_STORE_KEY', 'disabled');
define('PINKET_ORDER_TOKEN', 'not-configured');
define('PINKET_AUTH_TOKEN', PINKET_ORDER_TOKEN);
define('PINKET_WEBHOOK_TOKEN', 'not-configured');
define('PINKET_PROD_CAT_TOKEN', 'not-configured');
define('PINKET_BASIC_AUTH_USERNAME', 'disabled');
define('PINKET_BASIC_AUTH_PASSWORD', 'not-configured');
define('PINKET_STATUS_MAP', [0 => 'New', 1 => 'Accepted', 2 => 'Preparing', 3 => 'Preparing', 4 => 'Delivered', 5 => 'Cancel', 6 => 'NFC']);
define('PINKET_DEFAULT_BRANCH_ID', 'main');
define('PINKET_DEFAULT_DELIVERY_TYPE', 'standard');
define('PINKET_DELIVERY_START_HOURS', 2);
define('PINKET_DELIVERY_END_HOURS', 4);
define('PINKET_RETRY_ATTEMPTS', 0);
define('PINKET_TIMEOUT', 1);
define('PINKET_LOG_ENABLED', false);
define('PINKET_LOG_FILE', __DIR__ . '/pinket-api.log');

function getPinketConfig() {
    return [
        'enabled' => false, 'baseUrl' => PINKET_BASE_URL, 'inventoryUrl' => PINKET_INVENTORY_URL,
        'storeKey' => PINKET_STORE_KEY, 'orderToken' => PINKET_ORDER_TOKEN,
        'webhookToken' => PINKET_WEBHOOK_TOKEN, 'basicAuthUsername' => PINKET_BASIC_AUTH_USERNAME,
        'basicAuthPassword' => PINKET_BASIC_AUTH_PASSWORD, 'prodCatToken' => PINKET_PROD_CAT_TOKEN,
        'statusMap' => PINKET_STATUS_MAP, 'defaultBranchId' => PINKET_DEFAULT_BRANCH_ID,
        'defaultDeliveryType' => PINKET_DEFAULT_DELIVERY_TYPE,
        'deliveryStartHours' => PINKET_DELIVERY_START_HOURS, 'deliveryEndHours' => PINKET_DELIVERY_END_HOURS,
        'retryAttempts' => 0, 'timeout' => 1, 'logEnabled' => false, 'logFile' => PINKET_LOG_FILE
    ];
}
function logPinket($message, $type = 'INFO') { return; }
function getPinketStatus($state) { return PINKET_STATUS_MAP[$state] ?? 'New'; }
function getInternalStatus($pinketStatus) {
    $reverseMap = array_flip(PINKET_STATUS_MAP);
    return $reverseMap[$pinketStatus] ?? 0;
}
