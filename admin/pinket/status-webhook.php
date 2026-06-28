<?php
require_once __DIR__ . '/../../modules/wdb/db_config.php';
require_once __DIR__ . '/../../modules/wdb/db_connection.php';
require_once 'helpers.php';
require_once 'pinket-config.php';

/**
 * ارسال وضعیت سفارش به Pinket
 * این تابع فقط وضعیت را ارسال می کند (حداقل داده)
 */
function sendStatusToPinket($orderCode, $status) {
    $config = getPinketConfig();
    
    $url = $config['baseUrl'] . '/stores/' . $config['storeKey'] . '/orders/' . $orderCode . '/update-status';
    
    $data = [
        'status' => $status
    ];
    
    $headers = [
        'Content-Type: application/json',
        'Authorization: ' . $config['webhookToken']
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $config['timeout']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        logPinket("Pinket webhook error: " . $error, "ERROR");
        return false;
    }
    
    if ($httpCode >= 200 && $httpCode < 300) {
        logPinket("Webhook sent successfully: $url", "INFO");
        return true;
    } else {
        logPinket("Pinket webhook failed: HTTP $httpCode, Response: $response", "ERROR");
        return false;
    }
}

/**
 * تبدیل وضعیت سفارش به فرمت Pinket
 */
function getOrderStatus($state) {
    return getPinketStatus($state);
}

/**
 * ارسال وضعیت سفارش به Pinket (برای استفاده در سایر فایل ها)
 */
function updateOrderStatusInPinket($orderId) {
    file_put_contents(__DIR__.'/pinket_debug.log', "In webhook for OID: $orderId\n", FILE_APPEND);
    global $mysqli;
    
    $order = getRow($mysqli, "orders", "id = ?", [$orderId], "i");
    if (!$order) {
        logPinket("Order not found: $orderId", "ERROR");
        return false;
    }
    
    $orderCode = $order['unified_id'] ?: $order['id'];
    if (isset($order['del_flag']) && $order['del_flag'] == 1) {
        $status = 'Rejected';
    } else {
        $status = getOrderStatus($order['state']);
    }
    
    logPinket("Sending status update for order $orderId: $status", "INFO");
    return sendStatusToPinket($orderCode, $status);
}

// اگر این فایل مستقیماً فراخوانی شود
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $orderId = $input['orderId'] ?? null;
    
    if (!$orderId) {
        http_response_code(400);
        echo json_encode([
            'error' => 'شناسه سفارش ارسال نشده',
            'code' => 'MISSING_ORDER_ID'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $result = updateOrderStatusInPinket($orderId);
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'وضعیت سفارش با موفقیت به Pinket ارسال شد'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            'error' => 'خطا در ارسال وضعیت به Pinket',
            'code' => 'WEBHOOK_FAILED'
        ], JSON_UNESCAPED_UNICODE);
    }
}
?> 