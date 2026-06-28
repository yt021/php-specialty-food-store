<?php
/**
 * دریافت سفارش از Pinket
 * این endpoint برای دریافت سفارشات از Pinket استفاده می‌شود
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up the environment like the admin system does
$indexed = 1;
include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");

try {
require_once __DIR__ . '/../../modules/wdb/db_config.php';
require_once __DIR__ . '/../../modules/wdb/db_connection.php';
    require_once 'pinket-config.php';
require_once 'helpers.php';
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Include error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

// بررسی احراز هویت (اگر از endpoint جدید فراخوانی شده باشد)
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if ($authHeader) {
    $token = $authHeader;
    $config = getPinketConfig();

    // بررسی اعتبار توکن
    if ($token !== $config['orderToken']) {
        logPinket("REJECTED_AUTH: Invalid token '" . $token . "'. Headers: " . json_encode($headers), "REJECTED_AUTH");
        http_response_code(401);
        echo json_encode([
            'error' => 'توکن احراز هویت نامعتبر',
            'code' => 'INVALID_AUTH_TOKEN'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
} else {
    logPinket("REJECTED_AUTH: No Authorization header. Headers: " . json_encode($headers), "REJECTED_AUTH");
    http_response_code(401);
    echo json_encode([
        'error' => 'توکن احراز هویت ارسال نشده',
        'code' => 'MISSING_AUTH_TOKEN'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// بررسی متد درخواست
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'error' => 'Method not allowed',
        'code' => 'METHOD_NOT_ALLOWED'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// دریافت داده‌های JSON
$input = file_get_contents('php://input');

// Log every received request in full
logPinket("REQUEST_RECEIVED: " . $input, "REQUEST_RECEIVED");

// لاگ داده‌های خام برای دیباگ
logPinket("Raw input received: " . substr($input, 0, 1000) . (strlen($input) > 1000 ? '...' : ''), "DEBUG");

$data = json_decode($input, true);

if (!$data) {
    $jsonError = json_last_error();
    $jsonErrorMessage = json_last_error_msg();
    
    logPinket("REJECTED_REQUEST: Invalid JSON. Error: $jsonError, Message: $jsonErrorMessage. Input: " . $input, "REJECTED_REQUEST");
    logPinket("JSON parsing failed - Error: $jsonError, Message: $jsonErrorMessage", "ERROR");
    logPinket("Raw input that failed: " . $input, "ERROR");
    
    http_response_code(400);
    echo json_encode([
        'error' => 'Invalid JSON data: ' . $jsonErrorMessage,
        'code' => 'INVALID_JSON'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // لاگ درخواست
    logPinket("دریافت سفارش از Pinket: " . json_encode($data), "ORDER_RECEIVE");

    // بررسی داده‌های ضروری
    $requiredFields = ['code', 'customer', 'address', 'shoppingList', 'totalPrice'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            logPinket("REJECTED_REQUEST: Missing field '$field'. Data: " . json_encode($data), "REJECTED_REQUEST");
            throw new Exception("فیلد ضروری '$field' موجود نیست");
        }
    }
    
    // بررسی داده‌های مشتری
    if (!isset($data['customer']['fullName']) || !isset($data['customer']['phoneNumber'])) {
        logPinket("REJECTED_REQUEST: Incomplete customer data. Data: " . json_encode($data), "REJECTED_REQUEST");
        throw new Exception("داده‌های مشتری ناقص است");
    }
    
    // بررسی آدرس
    if (!isset($data['address']['address'])) {
        logPinket("REJECTED_REQUEST: Missing address. Data: " . json_encode($data), "REJECTED_REQUEST");
        throw new Exception("آدرس موجود نیست");
    }
    
    // بررسی لیست خرید
    if (empty($data['shoppingList'])) {
        logPinket("REJECTED_REQUEST: Empty shopping list. Data: " . json_encode($data), "REJECTED_REQUEST");
        throw new Exception("لیست خرید خالی است");
    }
    
    // شروع تراکنش
    $mysqli->begin_transaction();
    
    try {
        // ایجاد یا پیدا کردن کاربر
        $userName = $data['customer']['fullName'];
        $userPhone = $data['customer']['phoneNumber'];
        
        // بررسی وجود کاربر
        $existingUser = getRow($mysqli, 'users', 'tel = ?', [$userPhone], 's');
        
        if ($existingUser) {
            $userId = $existingUser['id'];
            // به‌روزرسانی نام کاربر
            $stmt = $mysqli->prepare("UPDATE users SET name = ? WHERE id = ?");
            if (!$stmt) {
                throw new Exception("خطا در آماده‌سازی کوئری به‌روزرسانی کاربر: " . $mysqli->error);
            }
            $stmt->bind_param('si', $userName, $userId);
        $stmt->execute();
        $stmt->close();
    } else {
            // ایجاد کاربر جدید
            $stmt = $mysqli->prepare("INSERT INTO users (name, tel, create_date) VALUES (?, ?, NOW())");
            if (!$stmt) {
                throw new Exception("خطا در آماده‌سازی کوئری ایجاد کاربر: " . $mysqli->error);
            }
            $stmt->bind_param('ss', $userName, $userPhone);
            $stmt->execute();
            $userId = $mysqli->insert_id;
            $stmt->close();
        }
        
        // ایجاد یا پیدا کردن آدرس
        $address = $data['address']['address'];
        $county = $data['address']['county'] ?? 'pinket';
        $city = $data['address']['city'] ?? 'pinket';
        $recTel = $data['address']['phoneNumber'] ?? '';
        
        // بررسی وجود آدرس برای این کاربر
        $existingAddress = getRow($mysqli, 'addresses', 'uid = ? AND address = ? AND rec_tel = ?', [$userId, $address, $recTel], 'iss');
        
        if ($existingAddress) {
            $addressId = $existingAddress['id'];
            logPinket("آدرس موجود استفاده شد: $address (ID: $addressId)", "ORDER_RECEIVE");
        } else {
            // ایجاد آدرس جدید
            $stmt = $mysqli->prepare("INSERT INTO addresses (uid, county, city, address, rec_tel) VALUES (?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("خطا در آماده‌سازی کوئری ایجاد آدرس: " . $mysqli->error);
            }
            $stmt->bind_param('issss', $userId, $county, $city, $address, $recTel);
    $stmt->execute();
            $addressId = $mysqli->insert_id;
    $stmt->close();
            logPinket("آدرس جدید ایجاد شد: $address (ID: $addressId)", "ORDER_RECEIVE");
        }
        
        // ایجاد سفارش
        $orderCode = $data['code'];
        $comment = $data['orderDetails']['comment'] ?? '';
        $totalPrice = $data['totalPrice'];
        $sale_total = 0;
        // Fetch shipping price from sd_shifts for recieve_shift = 2 (پیشتاز)
        $shipping_price = getSingleValue($mysqli, 'sd_shifts', 'price', 'id = ?', [2], 'i');
        if ($shipping_price === null || $shipping_price === false || $shipping_price === '') {
            throw new Exception('خطا در دریافت هزینه ارسال از پایگاه داده');
        }
        $pay_price = $totalPrice + (int)$shipping_price;
        // Using the exact code from Pinket as unified_id
        // Setting recieve_shift to 2 (پیشتاز/Express Post) for Pinket orders
        // Setting state to 0 (جدید) so orders appear in "جدید" tab
        $stmt = $mysqli->prepare("INSERT INTO orders (uid, aid, unified_id, state, order_detail, create_date, cart_price, cart_pure, sale_total, pay_price, admin_state, p_send_date, recieve_date, recieve_shift, lksr, del_flag) VALUES (?, ?, ?, 0, ?, NOW(), ?, ?, ?, ?, 1, NULL, NULL, 2, '', 0)");
        if (!$stmt) {
            throw new Exception("خطا در آماده‌سازی کوئری ایجاد سفارش: " . $mysqli->error);
        }
        $stmt->bind_param('iissiiii', $userId, $addressId, $orderCode, $comment, $totalPrice, $totalPrice, $sale_total, $pay_price);
        $stmt->execute();
        $orderId = $mysqli->insert_id;
        $stmt->close();
        
        // اضافه کردن اقلام سفارش
        foreach ($data['shoppingList'] as $item) {
            $itemId = $item['itemId'];
            $quantity = $item['quantity'];
            
            // Parse itemId: "0010050" -> productId=1, weight=50
            $productId = (int)substr($itemId, 0, 3);
            $weight = (int)substr($itemId, 3);
            
            // اضافه کردن آیتم سفارش
            $stmt = $mysqli->prepare("INSERT INTO sub_orders (oid, pid, weight, number) VALUES (?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("خطا در آماده‌سازی کوئری ایجاد آیتم سفارش: " . $mysqli->error);
            }
            $stmt->bind_param('iidi', $orderId, $productId, $weight, $quantity);
            $stmt->execute();
    $stmt->close();

            logPinket("محصول اضافه شد: itemId=$itemId, productId=$productId, weight=$weight, quantity=$quantity", "ORDER_RECEIVE");
        }
        
        // تایید تراکنش
        $mysqli->commit();
        
        // لاگ موفقیت
        logPinket("سفارش با موفقیت ایجاد شد - ID: $orderId, Code: $orderCode", "ORDER_RECEIVE");
        
        // پاسخ موفقیت - کد 200
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'orderId' => $orderId,
            'unifiedId' => $orderCode,
            'message' => 'سفارش با موفقیت دریافت شد'
        ], JSON_UNESCAPED_UNICODE);
        
    } catch (Exception $e) {
        // برگرداندن تراکنش
        $mysqli->rollback();
        logPinket("FAILED_SUBMISSION: " . $e->getMessage() . ". Data: " . json_encode($data), "FAILED_SUBMISSION");
        throw $e;
    }
    
} catch (Exception $e) {
    // لاگ خطا
    logPinket("خطا در دریافت سفارش: " . $e->getMessage(), "ERROR");
    logPinket("FAILED_SUBMISSION: " . $e->getMessage() . ". Data: " . (isset($data) ? json_encode($data) : $input), "FAILED_SUBMISSION");
    
    // پاسخ خطا - کد 400 برای خطاهای کلاینت
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => 'ORDER_CREATION_ERROR'
    ], JSON_UNESCAPED_UNICODE);
}
?>
