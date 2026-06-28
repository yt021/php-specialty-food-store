<?php
/**
 * ارسال داده‌ها به API پینکت
 * این فایل برای ارسال محصولات، دسته‌بندی‌ها و سفارشات به پینکت استفاده می‌شود
 */

// Set up the environment like the admin system does
$indexed = 1;
include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");

require_once 'pinket-config.php';
require_once 'helpers.php';

logMessage("✅ api-send.php اجرا شد");

// اتصال مستقیم به دیتابیس
require_once __DIR__ . '/../../modules/wdb/db_config.php';
require_once __DIR__ . '/../../modules/wdb/db_connection.php';
require_once 'pinket-config.php';

$indexed = 1;

if (isset($indexed) && $indexed == 1) {
    logMessage("✅ \$indexed تایید شد");

    require_once 'helpers.php';
    require_once 'products.php';
    require_once 'categories.php';
    logMessage("✅ فایل‌های کمکی لود شدند");

    header('Content-Type: application/json');

    if (!isset($mysqli) || !$mysqli instanceof mysqli || $mysqli->connect_errno) {
        logMessage("❌ اتصال دیتابیس ناموفق");
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'اتصال به دیتابیس برقرار نیست'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $rawInput = file_get_contents('php://input');
    logMessage("📥 Raw input: $rawInput");

    $body = json_decode($rawInput, true);
    logMessage("📥 Body decoded: " . print_r($body, true));

    $section = $body['section'] ?? '';
    logMessage("🔎 Section: $section", $section);

    try {
        $data = null;
        $url = null;

        switch ($section) {
            case 'products':
                logMessage("🔧 در حال آماده‌سازی اطلاعات محصولات...", $section);
                try {
                    $data = prepareProductsData($mysqli);

                    if (empty($data)) {
                        logMessage("❌ داده محصولات خالی برگشت داده شده", $section);
                        throw new Exception("داده‌ای برای ارسال وجود ندارد", 400);
                    }

                    logMessage("📦 Products data آماده شد:\n" . print_r($data, true), $section);
                } catch (Exception $e) {
                    logMessage("❌ خطا در prepareProductsData: " . $e->getMessage(), $section);
                    throw new Exception("خطا در آماده‌سازی اطلاعات محصولات", 500);
                }

                $url = PINKET_INVENTORY_URL . "/api/agent/v1/update-items";
                break;

            case 'categories':
                logMessage("🔧 در حال آماده‌سازی اطلاعات دسته‌بندی‌ها...", $section);
                try {
                    $data = prepareCategoriesData();

                    if (empty($data)) {
                        logMessage("❌ داده دسته‌بندی خالی برگشت داده شده", $section);
                        throw new Exception("داده‌ای برای ارسال وجود ندارد", 400);
                    }

                    logMessage("📁 Categories data آماده شد:\n" . print_r($data, true), $section);
                } catch (Exception $e) {
                    logMessage("❌ خطا در prepareCategoriesData: " . $e->getMessage(), $section);
                    throw new Exception("خطا در آماده‌سازی اطلاعات دسته‌بندی‌ها", 500);
                }

                $url = PINKET_INVENTORY_URL . "/api/agent/v1/categories";
                break;

            case 'orders':
                logMessage("🔧 در حال ثبت سفارش جدید...", $section);
                $orderCode = $body['code'] ?? null;
                if ($orderCode) {
                    // Insert order with unified_id = code
                    $stmt = $mysqli->prepare("INSERT INTO orders (unified_id) VALUES (?)");
                    $stmt->bind_param("s", $orderCode);
                    $stmt->execute();
                    $orderId = $stmt->insert_id;
                    logMessage("✅ سفارش با unified_id ثبت شد: $orderCode (id: $orderId)", $section);
                    $response = ["success" => true, "order_id" => $orderId, "unified_id" => $orderCode];
                } else {
                    logMessage("❌ کد سفارش (code) ارسال نشده", $section);
                    throw new Exception("کد سفارش ارسال نشده است", 400);
                }
                break;

            default:
                logMessage("❌ بخش نامعتبر: $section", $section);
                throw new Exception("بخش نامعتبر است", 400);
        }

        if (!$url || !$data) {
            logMessage("❌ داده یا آدرس API خالی است", $section);
            throw new Exception("داده یا آدرس API نامعتبر است", 500);
        }

        // ارسال به API
        logMessage("🚀 در حال ارسال به API...", $section);
        try {
            // برای محصولات و دسته‌بندی‌ها از Basic Auth استفاده کن
            $useBasicAuth = in_array($section, ['products', 'categories']);
            $response = sendToPinketApi($url, $data, $useBasicAuth);
            logMessage("📨 پاسخ از API:\n" . print_r($response, true), $section);
        } catch (Exception $e) {
            logMessage("❌ خطا در sendToPinketApi: " . $e->getMessage(), $section);
            throw new Exception("خطا در ارسال به API", 500);
        }

        logMessage("✅ آماده برای echo نهایی", $section);
        echo json_encode([
            'success' => true,
            'response' => $response
        ], JSON_UNESCAPED_UNICODE);
        logMessage("✅ echo انجام شد", $section);

    } catch (Exception $e) {
        logMessage("💥 استثنا: " . $e->getMessage(), $section);
        http_response_code($e->getCode() ?: 500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
            'code' => $e->getCode() ?: 500
        ], JSON_UNESCAPED_UNICODE);
    }

} else {
    logMessage("⛔ اجرا نشد چون \$indexed معتبر نیست");
}
