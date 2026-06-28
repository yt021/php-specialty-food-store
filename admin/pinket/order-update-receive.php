<?php
// Pinket Order Update Endpoint
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../modules/wdb/db_config.php';
require_once __DIR__ . '/../../modules/wdb/db_connection.php';
require_once 'helpers.php';
require_once 'pinket-config.php';

try {
    // 1. Auth
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (!$authHeader) {
        http_response_code(401);
        echo json_encode([
            'error' => 'Missing auth token',
            'code' => 'MISSING_AUTH_TOKEN'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $token = $authHeader;
    $config = getPinketConfig();
    if ($token !== $config['orderToken']) {
        http_response_code(401);
        echo json_encode([
            'error' => 'Invalid auth token',
            'code' => 'INVALID_AUTH_TOKEN'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 2. Parse order code from URL
    $orderCode = null;
    if (preg_match('#/orders/([^/]+)/update#', $_SERVER['REQUEST_URI'], $m)) {
        $orderCode = $m[1];
    }
    if (!$orderCode) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Order code missing in URL',
            'code' => 'MISSING_ORDER_CODE'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 3. Parse JSON body
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (!$data) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Invalid JSON',
            'code' => 'INVALID_JSON'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 4. Required field checks
    $requiredFields = ['customer', 'address', 'shoppingList', 'totalPrice'];
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            http_response_code(400);
            echo json_encode([
                'error' => "Missing required field: $field",
                'code' => 'MISSING_FIELD'
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    if (!isset($data['customer']['fullName']) || !isset($data['customer']['phoneNumber'])) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Incomplete customer data',
            'code' => 'INCOMPLETE_CUSTOMER'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (!isset($data['address']['address'])) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Address missing',
            'code' => 'MISSING_ADDRESS'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    if (empty($data['shoppingList'])) {
        http_response_code(400);
        echo json_encode([
            'error' => 'Shopping list is empty',
            'code' => 'EMPTY_SHOPPING_LIST'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 5. Find order by unified_id or id
    $orderId = null;
    if (!is_numeric($orderCode)) {
        $orderId = getSingleValue($mysqli, 'orders', 'id', 'unified_id = ?', [$orderCode], 's');
    } else {
        $orderId = (int)$orderCode;
    }
    if (!$orderId) {
        http_response_code(404);
        echo json_encode([
            'error' => 'Order not found',
            'code' => 'ORDER_NOT_FOUND'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $order = getRow($mysqli, 'orders', 'id = ?', [$orderId], 'i');
    if (!$order) {
        http_response_code(404);
        echo json_encode([
            'error' => 'Order not found',
            'code' => 'ORDER_NOT_FOUND'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $mysqli->begin_transaction();
    try {
        // 6. Update customer
        if (isset($data['customer']['fullName']) && isset($data['customer']['phoneNumber'])) {
            $stmt = $mysqli->prepare('UPDATE users SET name = ?, tel = ? WHERE id = ?');
            $stmt->bind_param('ssi', $data['customer']['fullName'], $data['customer']['phoneNumber'], $order['uid']);
            $stmt->execute();
            $stmt->close();
        }
        // 7. Update address
        if (isset($data['address']['address'])) {
            $addressFields = ['address', 'lat', 'lon', 'municipalArea', 'buildingNumber', 'phoneNumber', 'floor'];
            $addressUpdate = [];
            $params = [];
            $types = '';
            foreach ($addressFields as $f) {
                if (isset($data['address'][$f])) {
                    $col = $f === 'municipalArea' ? 'city' : ($f === 'phoneNumber' ? 'rec_tel' : $f);
                    $addressUpdate[] = "$col = ?";
                    $params[] = $data['address'][$f];
                    $types .= 's';
                }
            }
            if ($addressUpdate) {
                $params[] = $order['aid'];
                $types .= 'i';
                $stmt = $mysqli->prepare('UPDATE addresses SET ' . implode(',', $addressUpdate) . ' WHERE id = ?');
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $stmt->close();
            }
        }
        // 8. Update sub_orders (shoppingList)
        if (isset($data['shoppingList']) && is_array($data['shoppingList'])) {
            // Fetch current sub_orders (only those not deleted)
            $current = getRows($mysqli, 'sub_orders', 'oid = ? AND del_flag = 0', [$orderId], 'i');
            $currentMap = [];
            foreach ($current as $row) {
                $key = $row['pid'] . '|' . trim($row['weight']);
                $currentMap[$key] = $row;
            }
            $newMap = [];
            foreach ($data['shoppingList'] as $item) {
                $itemId = $item['itemId'] ?? null;
                if (!$itemId || !preg_match('/^(\d{3,})(\d{3})$/', $itemId, $m)) continue;
                $pid = ltrim(substr($m[1], 0, 3), '0'); // first 3 digits, remove leading zeros
                $weight = ltrim($m[2], '0'); // last 3 digits, remove leading zeros
                if ($weight === '') $weight = '0';
                $key = $pid . '|' . $weight;
                $newMap[$key] = $item;
                $newMap[$key]['_parsed_pid'] = $pid;
                $newMap[$key]['_parsed_weight'] = $weight;
            }
            file_put_contents(__DIR__.'/log_step.log', "[DEBUG] currentMap=".var_export($currentMap, true)."\n", FILE_APPEND);
            file_put_contents(__DIR__.'/log_step.log', "[DEBUG] newMap=".var_export($newMap, true)."\n", FILE_APPEND);
            // Remove sub_orders not in new list (set del_flag=1 instead of delete)
            foreach ($currentMap as $key => $row) {
                if (!isset($newMap[$key])) {
                    file_put_contents(__DIR__.'/log_step.log', "[DEL_FLAG] id={$row['id']}\n", FILE_APPEND);
                    $stmt = $mysqli->prepare('UPDATE sub_orders SET del_flag = 1 WHERE id = ?');
                    if (!$stmt) {
                        file_put_contents(__DIR__.'/log_step.log', "[ERROR] DEL_FLAG prepare failed: {$mysqli->error}\n", FILE_APPEND);
                        throw new Exception("خطا در آماده‌سازی کوئری حذف آیتم سفارش: " . $mysqli->error);
                    }
                    $stmt->bind_param('i', $row['id']);
                    $stmt->execute();
                    $stmt->close();
                    file_put_contents(__DIR__.'/log_step.log', "[DEL_FLAG] Success\n", FILE_APPEND);
                }
            }
            // Add or update sub_orders
            foreach ($newMap as $key => $item) {
                $pid = $item['_parsed_pid'];
                $weight = $item['_parsed_weight'];
                $number = $item['quantity'] ?? 1;
                file_put_contents(__DIR__.'/log_step.log', "[ADD/UPDATE] key=$key, pid=$pid, weight=$weight, number=$number\n", FILE_APPEND);
                if (isset($currentMap[$key])) {
                    // Update if changed
                    $row = $currentMap[$key];
                    file_put_contents(__DIR__.'/log_step.log', "[UPDATE] id={$row['id']}, old_number={$row['number']}, new_number=$number\n", FILE_APPEND);
                    if ($row['number'] != $number) {
                        $stmt = $mysqli->prepare('UPDATE sub_orders SET number = ? WHERE id = ?');
                        if (!$stmt) {
                            file_put_contents(__DIR__.'/log_step.log', "[ERROR] UPDATE prepare failed: {$mysqli->error}\n", FILE_APPEND);
                            throw new Exception("خطا در آماده‌سازی کوئری به‌روزرسانی آیتم سفارش: " . $mysqli->error);
                        }
                        $stmt->bind_param('ii', $number, $row['id']);
                        $stmt->execute();
                        $stmt->close();
                        file_put_contents(__DIR__.'/log_step.log', "[UPDATE] Success\n", FILE_APPEND);
                    }
                } else {
                    // Insert new
                    file_put_contents(__DIR__.'/log_step.log', "[INSERT] oid=$orderId, pid=$pid, weight=$weight, number=$number\n", FILE_APPEND);
                    $stmt = $mysqli->prepare('INSERT INTO sub_orders (oid, pid, weight, number) VALUES (?, ?, ?, ?)');
                    if (!$stmt) {
                        file_put_contents(__DIR__.'/log_step.log', "[ERROR] INSERT prepare failed: {$mysqli->error}\n", FILE_APPEND);
                        throw new Exception("خطا در آماده‌سازی کوئری ایجاد آیتم سفارش: " . $mysqli->error);
                    }
                    $stmt->bind_param('iiii', $orderId, $pid, $weight, $number);
                    $stmt->execute();
                    $stmt->close();
                    file_put_contents(__DIR__.'/log_step.log', "[INSERT] Success\n", FILE_APPEND);
                }
            }
        }
        // 9. Update order details and total price
        $orderUpdateFields = [];
        $orderUpdateParams = [];
        $orderUpdateTypes = '';
        if (isset($data['orderDetails']['comment'])) {
            $orderUpdateFields[] = 'order_detail = ?';
            $orderUpdateParams[] = $data['orderDetails']['comment'];
            $orderUpdateTypes .= 's';
        }
        if (isset($data['orderDetails']['deliveryType'])) {
            $orderUpdateFields[] = 'delivery_type = ?';
            $orderUpdateParams[] = $data['orderDetails']['deliveryType'];
            $orderUpdateTypes .= 's';
        }
        if (isset($data['totalPrice'])) {
            $cart_price = (int)$data['totalPrice'];
            $cart_pure = (int)$data['totalPrice'];
            $shipping_price = getSingleValue($mysqli, 'sd_shifts', 'price', 'id = ?', [2], 'i');
            if ($shipping_price === null || $shipping_price === false || $shipping_price === '') {
                file_put_contents(__DIR__.'/log_step.log', "[ERROR] shipping_price invalid: " . var_export($shipping_price, true) . "\n", FILE_APPEND);
                throw new Exception('خطا در دریافت هزینه ارسال از پایگاه داده');
            }
            $shipping_price = (int)$shipping_price;
            $pay_price = $cart_price + $shipping_price;
            $orderId = (int)$orderId;
            // Log all values and types
            file_put_contents(__DIR__.'/log_step.log', "[DEBUG] cart_price=$cart_price (" . gettype($cart_price) . "), cart_pure=$cart_pure (" . gettype($cart_pure) . "), pay_price=$pay_price (" . gettype($pay_price) . "), orderId=$orderId (" . gettype($orderId) . ")\n", FILE_APPEND);
            // Check for invalid values
            foreach ([['cart_price',$cart_price],['cart_pure',$cart_pure],['pay_price',$pay_price],['orderId',$orderId]] as [$n,$v]) {
                if (!is_int($v) || $v < 0) {
                    file_put_contents(__DIR__.'/log_step.log', "[WARN] $n is not a valid int: " . var_export($v, true) . "\n", FILE_APPEND);
                    throw new Exception("مقدار نامعتبر برای $n");
                }
            }
            file_put_contents(__DIR__.'/log_step.log', "[INFO] Preparing UPDATE orders query\n", FILE_APPEND);
            $stmt = $mysqli->prepare('UPDATE orders SET cart_price = ?, cart_pure = ?, pay_price = ? WHERE id = ?');
            if (!$stmt) {
                file_put_contents(__DIR__.'/log_step.log', "[ERROR] UPDATE orders prepare failed: {$mysqli->error}\n", FILE_APPEND);
                throw new Exception("خطا در آماده‌سازی کوئری به‌روزرسانی سفارش: " . $mysqli->error);
            }
            file_put_contents(__DIR__.'/log_step.log', "[INFO] Binding params to UPDATE orders\n", FILE_APPEND);
            $stmt->bind_param('iiii', $cart_price, $cart_pure, $pay_price, $orderId);
            file_put_contents(__DIR__.'/log_step.log', "[INFO] Executing UPDATE orders\n", FILE_APPEND);
            $stmt->execute();
            $stmt->close();
            file_put_contents(__DIR__.'/log_step.log', "[INFO] UPDATE orders Success\n", FILE_APPEND);
        }
        $mysqli->commit();
        echo json_encode([
            'success' => true,
            'code' => 'SUCCESS'
        ], JSON_UNESCAPED_UNICODE);
    } catch (Exception $e) {
        $mysqli->rollback();
        http_response_code(500);
        echo json_encode([
            'error' => $e->getMessage(),
            'code' => 'SERVER_ERROR'
        ], JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'code' => 'SERVER_ERROR'
    ], JSON_UNESCAPED_UNICODE);
} 