<?php
require_once __DIR__ . '/../../modules/wdb/db_config.php';
require_once __DIR__ . '/../../modules/wdb/db_connection.php';
require_once 'helpers.php';
require_once 'pinket-config.php';

header('Content-Type: application/json; charset=utf-8');

// بررسی احراز هویت
$headers = getallheaders();
$authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';

if (!$authHeader) {
    http_response_code(401);
    echo json_encode([
        'error' => 'توکن احراز هویت ارسال نشده',
        'code' => 'MISSING_AUTH_TOKEN'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
$token = $authHeader;
$config = getPinketConfig();

// بررسی اعتبار توکن
if ($token !== $config['orderToken']) {
    http_response_code(401);
    echo json_encode([
        'error' => 'توکن احراز هویت نامعتبر',
        'code' => 'INVALID_AUTH_TOKEN'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// بررسی متد درخواست
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'error' => 'متد غیرمجاز',
        'code' => 'METHOD_NOT_ALLOWED'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// دریافت کد سفارش از URL
$requestUri = $_SERVER['REQUEST_URI'];
$pathParts = explode('/', trim($requestUri, '/'));

// استخراج کد سفارش از URL pattern: /orders/{order-code}
$orderCode = null;
for ($i = 0; $i < count($pathParts) - 1; $i++) {
    if ($pathParts[$i] === 'orders' && isset($pathParts[$i + 1])) {
        $orderCode = $pathParts[$i + 1];
        break;
    }
}

// اگر از URL pattern پیدا نشد، از GET parameter استفاده کن
if (!$orderCode) {
    $orderCode = $_GET['order-code'] ?? null;
}

if (!$orderCode) {
    http_response_code(400);
    echo json_encode([
        'error' => 'کد سفارش ارسال نشده',
        'code' => 'MISSING_ORDER_CODE'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // پیدا کردن سفارش بر اساس unified_id یا id
    $orderId = null;
    if (!is_numeric($orderCode)) {
        // Try as unified_id first
        $orderId = getSingleValue($mysqli, 'orders', 'id', 'unified_id = ?', [$orderCode], 's');
    } else {
        // Try numeric id first
        $orderId = (int)$orderCode;
    }
    
    if (!$orderId) {
        http_response_code(404);
        echo json_encode([
            'error' => 'سفارش یافت نشد',
            'code' => 'ORDER_NOT_FOUND'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // دریافت اطلاعات سفارش
    $order = getRow($mysqli, "orders", "id = ?", [$orderId], "i");
    if (!$order) {
        http_response_code(404);
        echo json_encode([
            'error' => 'سفارش یافت نشد',
            'code' => 'ORDER_NOT_FOUND'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // تعیین وضعیت سفارش بر اساس مستندات Pinket
    $status = getPinketStatusForOrder($order);
    
    // دریافت لیست نهایی محصولات تحویل شده
    $shoppingList = getFinalShoppingList($mysqli, $orderId);
    
    // محاسبه قیمت کل نهایی
    $totalPrice = calculateFinalTotalPrice($shoppingList);
    
    // ثبت لاگ
    logPinket("Status query for order $orderCode: $status", "INFO");
    
    // پاسخ موفق - کد 200
    http_response_code(200);
    echo json_encode([
        'status' => $status,
        'shoppingList' => $shoppingList,
        'totalPrice' => $totalPrice
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    logPinket("Status query error: " . $e->getMessage(), "ERROR");
    http_response_code(500);
    echo json_encode([
        'error' => 'خطای سرور: ' . $e->getMessage(),
        'code' => 'SERVER_ERROR'
    ], JSON_UNESCAPED_UNICODE);
}

/**
 * تعیین وضعیت سفارش بر اساس مستندات Pinket
 */
function getPinketStatusForOrder($order) {
    $state = $order['state'];
    $adminState = $order['admin_state'] ?? 0;
    $delFlag = $order['del_flag'] ?? 0;
    
    // اگر سفارش حذف شده (del_flag = 1)، وضعیت Rejected است
    if ($delFlag == 1) {
        return 'Rejected';
    }
    
    // وضعیت‌های سفارش بر اساس مستندات Pinket:
    // New: سفارش در فروشگاه دریافت شده اما پردازشی روی آن انجام نشده
    // Accepted: سفارش در فروشگاه تایید شده و آماده آماده سازی است
    // Rejected: سفارش در فروشگاه دریافت شد ولی فروشگاه قادر به سرویس دهی نیست
    // Preparing: سفارش در حال آماده سازی در فروشگاه است
    // NFC: سفارش نیازمند تماس با مشتری و رفع مشکلات احتمالی است
    // Delivered: سفارش تحویل شد (از طریق پست خودمان)
    // Cancel: سفارش توسط پینکت کنسل شده است
    
    switch ($state) {
        case 0: // جدید
            return 'New';
            
        case 1: // در صف تولید - سفارش تایید شده و در حال پردازش
            return 'Accepted';
            
        case 2: // تولید شده
            return 'Preparing';
            
        case 3: // آماده ارسال
            return 'Preparing';
            
        case 4: // ارسال شده - تحویل شده از طریق پست خودمان
            return 'Delivered';
            
        case 5: // کنسل شده
            return 'Cancel';
            
        case 6: // نیاز به تماس
            return 'NFC';
            
        case 7: // رد شده (حالا از del_flag استفاده می‌کنیم)
            return 'Rejected';
            
        default:
            return 'New';
    }
}

/**
 * دریافت لیست نهایی محصولات تحویل شده
 */
function getFinalShoppingList($mysqli, $orderId) {
    $subOrders = getRows($mysqli, "sub_orders", "oid = ? AND del_flag = 0", [$orderId], "i");
    $shoppingList = [];
    
    foreach ($subOrders as $item) {
        $productId = (int)$item['pid'];
        $weight = trim($item['weight']);
        $number = (int)$item['number'];
        
        // دریافت قیمت محصول
        $stmt = $mysqli->prepare("SELECT weight, price FROM products_price WHERE pid = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $priceRes = $stmt->get_result();
        
        if ($priceRow = $priceRes->fetch_assoc()) {
            $weights = array_map('trim', preg_split('/[\s,]+/', $priceRow['weight']));
            $prices = array_map('trim', preg_split('/[\s,]+/', $priceRow['price']));
            
            $index = array_search($weight, $weights);
            if ($index !== false && isset($prices[$index])) {
                $unitPrice = (int)$prices[$index];
                $totalPrice = $unitPrice * $number;
                
                $itemId = str_pad($productId, 3, '0', STR_PAD_LEFT) . str_pad($weight, 4, '0', STR_PAD_LEFT);
                $productName = getSingleValue($mysqli, "products", "name", "id = ?", [$productId], "i");
                
                $shoppingList[] = [
                    "itemId" => $itemId,
                    "name" => $productName . " " . $weight . " گرمی",
                    "quantity" => $number,
                    "unitPrice" => $unitPrice,
                    "totalPrice" => $totalPrice
                ];
            }
        }
        $stmt->close();
    }
    
    return $shoppingList;
}

/**
 * محاسبه قیمت کل نهایی
 */
function calculateFinalTotalPrice($shoppingList) {
    $total = 0;
    foreach ($shoppingList as $item) {
        $total += $item['totalPrice'];
    }
    return $total;
}
?> 