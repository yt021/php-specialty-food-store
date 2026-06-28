<?php

// فقط اگر مستقیماً با GET اجرا بشه، خروجی JSON بده
if (isset($_GET['order_id'])) {
    require_once __DIR__ . '/../../modules/wdb/db_config.php';
    require_once __DIR__ . '/../../modules/wdb/db_connection.php';
    require_once 'helpers.php';
    require_once 'pinket-config.php';

    header('Content-Type: application/json; charset=utf-8');

    $orderIdRaw = $_GET['order_id'];
    $orderId = null;
    if (!is_numeric($orderIdRaw)) {
        // Try as unified_id first
        $orderId = getSingleValue($mysqli, 'orders', 'id', 'unified_id = ?', [$orderIdRaw], 's');
        if ($orderId) {
            $data = prepareOrderData($mysqli, (int)$orderId);
        } else {
            $data = null;
        }
    } else {
        // Try numeric id first
        $orderId = (int)$orderIdRaw;
        $data = prepareOrderData($mysqli, $orderId);
        if (!$data) {
            // Fallback: try as unified_id
            $orderId = getSingleValue($mysqli, 'orders', 'id', 'unified_id = ?', [$orderIdRaw], 's');
            if ($orderId) {
                $data = prepareOrderData($mysqli, (int)$orderId);
            }
        }
    }

    if ($data) {
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        echo json_encode(["error" => "Order not found"], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// ✅ اینجا توابع همیشه قابل دسترسی هستن (چه در include، چه در اجرای مستقیم)
function prepareOrderData(mysqli $mysqli, int $orderId): ?array {
    $orderQuery = "SELECT * FROM orders WHERE id = ? AND del_flag = 0";
    $stmt = $mysqli->prepare($orderQuery);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $orderRes = $stmt->get_result();
    if (!$orderRow = $orderRes->fetch_assoc()) return null;

    $userId = (int)$orderRow['uid'];
    $user = getRow($mysqli, "users", "id = ?", [$userId], "i");
    if (!$user) return null;

    $addressId = (int)$orderRow['aid'];
    $address = getRow($mysqli, "addresses", "id = ?", [$addressId], "i");
    if (!$address) return null;

    $fullAddress = trim($address['county'] . '، ' . $address['city'] . '، ' . $address['address']);
    $subOrders = getRows($mysqli, "sub_orders", "oid = ? AND del_flag = 0", [$orderId], "i");

    $shoppingList = [];
    $totalPrice = 0;

    foreach ($subOrders as $item) {
        $productId = (int)$item['pid'];
        $weight = trim($item['weight']);
        $number = (int)$item['number'];

        $stmt2 = $mysqli->prepare("SELECT weight, price FROM products_price WHERE pid = ? ORDER BY id DESC LIMIT 1");
        $stmt2->bind_param("i", $productId);
        $stmt2->execute();
        $priceRes = $stmt2->get_result();
        if ($priceRow = $priceRes->fetch_assoc()) {
            $weights = array_map('trim', preg_split('/[\s,]+/', $priceRow['weight']));
            $prices = array_map('trim', preg_split('/[\s,]+/', $priceRow['price']));

            $index = array_search($weight, $weights);
            if ($index !== false && isset($prices[$index])) {
                $unitPrice = (int)$prices[$index];
                $total = $unitPrice * $number;
                $totalPrice += $total;

                $itemId = str_pad($productId, 3, '0', STR_PAD_LEFT) . str_pad($weight, 4, '0', STR_PAD_LEFT);
                $productName = getSingleValue($mysqli, "products", "name", "id = ?", [$productId], "i");

                $shoppingList[] = [
                    "itemId" => $itemId,
                    "name" => $productName . " " . $weight . " گرمی",
                    "quantity" => $number,
                    "unitPrice" => $unitPrice,
                    "totalPrice" => $total
                ];
            }
        }
        $stmt2->close();
    }

    // Fetch unified_id for this order
    $unified_id = $orderRow['unified_id'] ?? null;
    if (!$unified_id) $unified_id = (string)$orderId;

    return [
        "code" => $unified_id,
        "customer" => [
            "fullName" => $user['name'],
            "phoneNumber" => $user['tel']
        ],
        "address" => [
            "address" => $fullAddress,
            "lat" => null,
            "lon" => null,
            "municipalArea" => null,
            "buildingNumber" => null,
            "phoneNumber" => $address['rec_tel'],
            "floor" => null
        ],
        "orderDetails" => [
            "orderTime" => strtotime($orderRow['create_date']) * 1000,
            "deliveryStartTime" => null,
            "deliveryEndTime" => null,
            "comment" => $orderRow['order_detail'] ?? "",
            "deliveryType" => "courier"
        ],
        "shoppingList" => $shoppingList,
        "totalPrice" => $totalPrice
    ];
}