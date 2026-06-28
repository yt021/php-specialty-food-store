<?php
// helpers.php

// getOrderIdByUnifiedId: Fetches order's numeric id by unified_id (string)
// getSingleValue: Can be used for both id and unified_id lookups

// --- Address Storage (Manual Input) ---

/**
 * DEPRECATED: Use getOrCreateManualAddress for all new code.
 * Finds or creates an address in the DB. Expects normalized input.
 *
 * @param mysqli $mysqli
 * @param int $uid
 * @param string $county
 * @param string $city
 * @param string $address
 * @return int Address ID
 */
function getOrCreateAddress(mysqli $mysqli, int $uid, string $county, string $city, string $address): int {
    $stmt = $mysqli->prepare("SELECT id FROM addresses WHERE uid = ? AND county = ? AND city = ? AND address = ? LIMIT 1");
    $stmt->bind_param("isss", $uid, $county, $city, $address);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $stmt->close();
        logMessage("آدرس موجود یافت شد: id=" . $row['id'], "ADDRESS");
        return (int)$row['id'];
    }
    $stmt->close();
    $stmt = $mysqli->prepare("INSERT INTO addresses (uid, county, city, address) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $uid, $county, $city, $address);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    logMessage("آدرس جدید با id=$id ثبت شد", "ADDRESS");
    return $id;
}

/**
 * NEW: Manual address storage - stores raw address with city/county set to "نامشخص"
 *
 * @param mysqli $mysqli
 * @param int $userId
 * @param string $rawAddress
 * @return int Address ID
 *
 * Example:
 *   $addressId = getOrCreateManualAddress($mysqli, $userId, $rawAddress);
 */
function getOrCreateManualAddress(mysqli $mysqli, int $userId, string $rawAddress): int {
    $county = "نامشخص";
    $city = "نامشخص";
    $addressText = trim($rawAddress);
    
    return getOrCreateAddress($mysqli, $userId, $county, $city, $addressText);
}

/**
 * DEPRECATED: Use getOrCreateManualAddress instead
 * Unified function: normalizes address and finds or creates it in DB.
 *
 * @param mysqli $mysqli
 * @param int $userId
 * @param string $rawAddress
 * @param string|null $county (optional)
 * @param string|null $city (optional)
 * @return int Address ID
 *
 * Example:
 *   $addressId = getOrCreateNormalizedAddress($mysqli, $userId, $rawAddress, $county, $city);
 */
function getOrCreateNormalizedAddress(mysqli $mysqli, int $userId, string $rawAddress, ?string $county = null, ?string $city = null): int {
    // Use manual address storage instead
    return getOrCreateManualAddress($mysqli, $userId, $rawAddress);
}

function getSingleValue(mysqli $conn, string $table, string $column, string $whereClause, array $params = [], string $paramTypes = '') {
    $sql = "SELECT `$column` FROM `$table` WHERE $whereClause LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    if ($params && $paramTypes) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return ($result && $row = $result->fetch_assoc()) ? $row[$column] : null;
}

function buildImageUrl(?string $fileName): string {
    return $fileName ? "https://abanfruit.com/content/" . $fileName : "";
}

function sendToPinketApi(string $url, array $data, bool $useBasicAuth = false, bool $useTokenAuth = false): array {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    if ($useBasicAuth) {
        // استفاده از تنظیمات پیکربندی
        require_once __DIR__ . '/pinket-config.php';
        $username = PINKET_BASIC_AUTH_USERNAME;
        $password = PINKET_BASIC_AUTH_PASSWORD;
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    }

    if ($useTokenAuth) {
        // استفاده از توکن احراز هویت
        require_once __DIR__ . '/pinket-config.php';
        $token = PINKET_AUTH_TOKEN;
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: ' . $token
        ]);
    }

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new Exception('خطای اتصال: ' . $err, 500);
    }

    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        return json_decode($response, true);
    }

    if ($httpCode >= 500) {
        throw new Exception("خطای سمت سرور. کد: $httpCode", $httpCode);
    }

    if ($httpCode >= 400) {
        $error = json_decode($response, true);
        throw new Exception($error['error'] ?? 'خطای نامشخص', $httpCode);
    }

    throw new Exception("خطای ناشناخته", $httpCode);
}

// --- Logging Helper ---
function logMessage($message, $section = null) {
    $datetime = date("Y-m-d H:i:s");
    $prefix = $section ? "[$datetime][$section]" : "[$datetime]";
    file_put_contents('debug_api_send.log', "$prefix $message\n", FILE_APPEND);
}

// --- DB Row Fetch Helpers ---
function getRow(mysqli $conn, string $table, string $whereClause, array $params = [], string $paramTypes = '') {
    $sql = "SELECT * FROM `$table` WHERE $whereClause LIMIT 1";
    $stmt = $conn->prepare($sql);
    if ($params && $paramTypes) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return ($result && $row = $result->fetch_assoc()) ? $row : null;
}
function getRows(mysqli $conn, string $table, string $whereClause, array $params = [], string $paramTypes = '') {
    $sql = "SELECT * FROM `$table` WHERE $whereClause";
    $stmt = $conn->prepare($sql);
    if ($params && $paramTypes) {
        $stmt->bind_param($paramTypes, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

// --- User/Address/Product Helpers ---
function getOrCreateUser(mysqli $mysqli, string $name, string $tel): int {
    $user = getRow($mysqli, "users", "tel = ?", [$tel], "s");
    if ($user) return (int)$user['id'];
    logMessage("کاربر یافت نشد، در حال ساخت کاربر جدید", "USER");
    $stmt = $mysqli->prepare("INSERT INTO users (name, tel) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $tel);
    $stmt->execute();
    $id = $stmt->insert_id;
    $stmt->close();
    logMessage("کاربر جدید با id=$id ساخته شد", "USER");
    return $id;
}
function getProductIdByName(mysqli $mysqli, string $name): ?int {
    $original = $name;
    $name = preg_replace('/\s\d+\s?گرمی/', '', $name); // حذف وزن از انتهای نام
    $stmt = $mysqli->prepare("SELECT id FROM products WHERE name = ? LIMIT 1");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        return (int)$row['id'];
    }
    logMessage("شناسه برای '$original' پیدا نشد", "PRODUCT");
    return null;
}
