<?php
require_once __DIR__ . '/../../modules/wdb/db_config.php';
require_once __DIR__ . '/../../modules/wdb/db_connection.php';
require_once 'helpers.php';
require_once 'status-webhook.php';
require_once 'pinket-config.php';

/**
 * مدیریت سفارشات Pinket
 * این کلاس می تواند هم داده کامل و هم داده حداقلی ارسال کند
 */
class PinketOrderManager {
    private $mysqli;
    private $config;
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
        $this->config = getPinketConfig();
    }
    
    /**
     * ارسال سفارش جدید (داده کامل)
     */
    public function createOrder($orderId) {
        $orderData = $this->buildFullOrderData($orderId);
        if (!$orderData) {
            return false;
        }
        
        $url = $this->config['baseUrl'] . '/orders/create';
        return $this->sendRequest($url, $orderData);
    }
    
    /**
     * کنسل کردن سفارش (داده کامل)
     * توجه: این endpoint ممکن است در مستندات آینده تغییر کند
     */
    public function cancelOrder($orderId) {
        $orderData = $this->buildFullOrderData($orderId);
        if (!$orderData) {
            return false;
        }
        
        // فعلاً از همان endpoint ایجاد استفاده می‌کنیم
        // در آینده ممکن است endpoint جداگانه‌ای داشته باشد
        $url = $this->config['baseUrl'] . '/orders/create';
        return $this->sendRequest($url, $orderData);
    }
    
    /**
     * آپدیت سفارش (داده کامل)
     */
    public function updateOrder($orderId) {
        $orderData = $this->buildFullOrderData($orderId);
        if (!$orderData) {
            return false;
        }
        
        $orderCode = $orderData['code'];
        $url = $this->config['baseUrl'] . '/orders/' . $orderCode . '/update';
        return $this->sendRequest($url, $orderData);
    }
    
    /**
     * ارسال وضعیت سفارش (داده حداقلی)
     */
    public function updateOrderStatus($orderId) {
        $order = getRow($this->mysqli, "orders", "id = ? AND del_flag = 0", [$orderId], "i");
        if (!$order) {
            return false;
        }
        
        $orderCode = $order['unified_id'] ?: $order['id'];
        $status = $this->getOrderStatus($order['state']);
        
        return updateOrderStatusInPinket($orderId);
    }
    
    /**
     * ساخت داده کامل سفارش
     */
    private function buildFullOrderData($orderId) {
        $order = getRow($this->mysqli, "orders", "id = ? AND del_flag = 0", [$orderId], "i");
        if (!$order) {
            return false;
        }
        
        $orderCode = $order['unified_id'] ?: $order['id'];
        
        // دریافت اطلاعات کاربر
        $userId = (int)$order['uid'];
        $user = getRow($this->mysqli, "users", "id = ?", [$userId], "i");
        if (!$user) {
            return false;
        }
        
        // دریافت اطلاعات آدرس
        $addressId = (int)$order['aid'];
        $address = getRow($this->mysqli, "addresses", "id = ?", [$addressId], "i");
        if (!$address) {
            return false;
        }
        
        // دریافت اطلاعات مشتری
        $customer = $this->getCustomerData($user);
        
        // دریافت اطلاعات آدرس
        $addressData = $this->getAddressData($address);
        
        // دریافت جزئیات سفارش
        $orderDetails = $this->getOrderDetails($order);
        
        // دریافت لیست خرید
        $shoppingList = $this->getShoppingList($orderId);
        
        // محاسبه قیمت کل
        $totalPrice = $this->calculateTotalPrice($shoppingList);
        
        return [
            'code' => $orderCode,
            'branchId' => $this->config['defaultBranchId'],
            'customer' => $customer,
            'address' => $addressData,
            'orderDetails' => $orderDetails,
            'shoppingList' => $shoppingList,
            'totalPrice' => $totalPrice
        ];
    }
    
    /**
     * دریافت اطلاعات مشتری
     */
    private function getCustomerData($user) {
        return [
            'fullName' => $user['name'],
            'phoneNumber' => $user['tel']
        ];
    }
    
    /**
     * دریافت اطلاعات آدرس
     */
    private function getAddressData($address) {
        $fullAddress = trim($address['county'] . '، ' . $address['city'] . '، ' . $address['address']);
        
        return [
            'address' => $fullAddress,
            'lat' => 0, // در صورت وجود در دیتابیس
            'lon' => 0, // در صورت وجود در دیتابیس
            'municipalArea' => $address['city'] ?: 'نامشخص',
            'buildingNumber' => '', // در صورت وجود در دیتابیس
            'phoneNumber' => $address['rec_tel'] ?: '',
            'floor' => '' // در صورت وجود در دیتابیس
        ];
    }
    
    /**
     * دریافت جزئیات سفارش
     */
    private function getOrderDetails($order) {
        $orderTime = strtotime($order['create_date']) * 1000; // تبدیل به میلی ثانیه
        $deliveryStartTime = $orderTime + ($this->config['deliveryStartHours'] * 60 * 60 * 1000);
        $deliveryEndTime = $orderTime + ($this->config['deliveryEndHours'] * 60 * 60 * 1000);
        
        return [
            'orderTime' => $orderTime,
            'deliveryStartTime' => $deliveryStartTime,
            'deliveryEndTime' => $deliveryEndTime,
            'comment' => $order['order_detail'] ?: '',
            'deliveryType' => $this->config['defaultDeliveryType']
        ];
    }
    
    /**
     * دریافت لیست خرید
     */
    private function getShoppingList($orderId) {
        $subOrders = getRows($this->mysqli, "sub_orders", "oid = ? AND del_flag = 0", [$orderId], "i");
        $shoppingList = [];
        
        foreach ($subOrders as $item) {
            $productId = (int)$item['pid'];
            $weight = trim($item['weight']);
            $number = (int)$item['number'];
            
            // دریافت قیمت محصول
            $stmt = $this->mysqli->prepare("SELECT weight, price FROM products_price WHERE pid = ? ORDER BY id DESC LIMIT 1");
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
                    $productName = getSingleValue($this->mysqli, "products", "name", "id = ?", [$productId], "i");
                    
                    $shoppingList[] = [
                        "itemId" => $itemId,
                        "name" => $productName . " " . $weight . " گرمی",
                        "quantity" => $number,
                        "unitPrice" => $unitPrice,
                        "totalPrice" => $totalPrice,
                        "comment" => $item['comment'] ?: ''
                    ];
                }
            }
            $stmt->close();
        }
        
        return $shoppingList;
    }
    
    /**
     * محاسبه قیمت کل
     */
    private function calculateTotalPrice($shoppingList) {
        $total = 0;
        foreach ($shoppingList as $item) {
            $total += $item['totalPrice'];
        }
        return $total;
    }
    
    /**
     * تبدیل وضعیت سفارش به فرمت Pinket
     */
    private function getOrderStatus($state) {
        return getPinketStatus($state);
    }
    
    /**
     * ارسال درخواست به Pinket
     */
    private function sendRequest($url, $data) {
        $headers = [
            'Content-Type: application/json',
            'Authorization: ' . $this->config['orderToken']
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->config['timeout']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            logPinket("Pinket API error: " . $error, "ERROR");
            return false;
        }
        
        if ($httpCode >= 200 && $httpCode < 300) {
            logPinket("API call successful: $url", "INFO");
            return true;
        } else {
            logPinket("Pinket API failed: HTTP $httpCode, Response: $response", "ERROR");
            return false;
        }
    }
}

// اگر این فایل مستقیماً فراخوانی شود
if (basename(__FILE__) == basename($_SERVER['SCRIPT_NAME'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $orderId = $input['orderId'] ?? null;
    
    if (!$orderId) {
        http_response_code(400);
        echo json_encode([
            'error' => 'شناسه سفارش ارسال نشده',
            'code' => 'MISSING_ORDER_ID'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $manager = new PinketOrderManager($mysqli);
    $result = false;
    
    switch ($action) {
        case 'create':
            $result = $manager->createOrder($orderId);
            break;
        case 'cancel':
            $result = $manager->cancelOrder($orderId);
            break;
        case 'update':
            $result = $manager->updateOrder($orderId);
            break;
        case 'status':
            $result = $manager->updateOrderStatus($orderId);
            break;
        default:
            http_response_code(400);
            echo json_encode([
                'error' => 'عملیات نامعتبر',
                'code' => 'INVALID_ACTION'
            ], JSON_UNESCAPED_UNICODE);
            exit;
    }
    
    if ($result) {
        echo json_encode([
            'success' => true,
            'message' => 'عملیات با موفقیت انجام شد'
        ], JSON_UNESCAPED_UNICODE);
    } else {
        http_response_code(500);
        echo json_encode([
            'error' => 'خطا در انجام عملیات',
            'code' => 'OPERATION_FAILED'
        ], JSON_UNESCAPED_UNICODE);
    }
}
?> 