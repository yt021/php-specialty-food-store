<?php

require_once $bu . "modules/snappay/snappay_client.php";
require_once $bu . "modules/snappay/snappay_db.php";
require_once $bu . "modules/snappay/snappay_helpers.php";
include_once $bu . "modules/cart/cart_funcs.php";
if (file_exists($bu . "modules/snappay/snappay_request_handler.php")) {
    require_once $bu . "modules/snappay/snappay_request_handler.php";
}

if (!isset($_SESSION["a_logged"]) || !is_object($_SESSION["a_logged"]) || !method_exists($_SESSION["a_logged"], 'get_level') || $_SESSION["a_logged"]->get_level() < 2) {
    $_SESSION["code_error"] = "EAccess denied.";
    return;
}

if (!snappay_backend_enabled()) {
    $_SESSION["code_error"] = "EBackend SnappPay is disabled by admin.";
    return;
}

$oid = $_SESSION[$cf]->id ?? null;
if (!$oid) {
    $_SESSION["code_error"] = "EOrder is invalid.";
    return;
}

$admin_actor = 'unknown';
if (isset($_SESSION["a_logged"]) && is_object($_SESSION["a_logged"])) {
    if (method_exists($_SESSION["a_logged"], 'get_uid')) {
        $admin_actor = (string)$_SESSION["a_logged"]->get_uid();
    } elseif (property_exists($_SESSION["a_logged"], 'uid')) {
        $admin_actor = (string)$_SESSION["a_logged"]->uid;
    }
}

if (
    !isset($_POST['snappay_csrf']) ||
    !isset($_SESSION['snappay_csrf']) ||
    !is_string($_POST['snappay_csrf']) ||
    !is_string($_SESSION['snappay_csrf']) ||
    !hash_equals($_SESSION['snappay_csrf'], $_POST['snappay_csrf'])
) {
    $_SESSION["code_error"] = "EUnsafe request (CSRF). Please retry.";
    return;
}

if (!isset($_POST['snappay_confirm']) || $_POST['snappay_confirm'] !== 'yes') {
    $_SESSION["code_error"] = "EPlease confirm update first.";
    return;
}

$tx = snappay_tx_get_latest_for_order($oid);
if (!$tx || !$tx['payment_token']) {
    $_SESSION["code_error"] = "ESnappPay transaction not found for this order.";
    return;
}

$snappay_status_norm = strtoupper(trim((string)($tx['snappay_status'] ?? '')));
$final_status_norm = strtoupper(trim((string)($tx['final_status'] ?? '')));
if ($snappay_status_norm !== 'SETTLE' && $final_status_norm !== 'SETTLE_OK') {
    $_SESSION["code_error"] = "ETransaction is not in an updatable state.";
    return;
}

$direct_request_snapshot = null;
if (function_exists('snappay_capture_current_update_snapshot')) {
    $direct_request_snapshot = snappay_capture_current_update_snapshot((int)$oid);
    if (is_array($direct_request_snapshot)) {
        $direct_request_snapshot['source'] = 'admin_direct';
    }
}

snappay_log('INFO', 'admin.update.request', [
    'admin_actor' => $admin_actor,
    'oid' => (int)$oid,
    'tx_id' => (int)$tx['id'],
    'transaction_id' => (string)$tx['transaction_id'],
]);

$new_amount_toman = (int)getVarFromDB("orders", "pay_price", "id", $oid);
if ($new_amount_toman <= 0) {
    $_SESSION["code_error"] = "EUpdated order amount is invalid.";
    return;
}

if ($new_amount_toman > (int)$tx['amount_toman']) {
    $_SESSION["code_error"] = "EUpdated amount must be lower than or equal to original amount.";
    return;
}

$uid = (int)getVarFromDB("orders", "uid", "id", $oid);
$tel = (string)getVarFromDB("users", "tel", "id", $uid);
$mobile_e164 = snappay_tel_to_e164($tel);

[$payload_ok, $payload_or_err] = snappay_build_cart_payload_from_order($oid, $tx['transaction_id'], $mobile_e164);
if (!$payload_ok) {
    snappay_error_insert((string)$oid, (string)$tx['id'], 'admin_update_build', 0, null, 'payload_failed', (string)$payload_or_err);
    $_SESSION["code_error"] = "EFailed to build SnappPay update payload.";
    return;
}

$payload = $payload_or_err;
$allowed_keys = ['amount', 'discountAmount', 'externalSourceAmount', 'cartList'];
$update_payload = [];
foreach ($allowed_keys as $k) {
    if (array_key_exists($k, $payload)) {
        $update_payload[$k] = $payload[$k];
    }
}
$update_payload['paymentToken'] = (string)$tx['payment_token'];

$res = snappay_api_update($update_payload);
$err_code = snappay_extract_error_code($res['json'] ?? null);

if (!$res['ok'] && snappay_is_retryable_error_code($err_code)) {
    usleep(400000);
    $res = snappay_api_update($update_payload);
    $err_code = snappay_extract_error_code($res['json'] ?? null);
}

if (!$res['ok'] && snappay_is_retryable_error_code($err_code)) {
    $status_res = snappay_api_status($tx['payment_token']);
    if ($status_res['ok']) {
        $status = snappay_extract_transaction_status($status_res['json'] ?? null);
        $amount = $status_res['json']['response']['amount'] ?? null;
        if ($status !== null) {
            snappay_tx_set_snappay_status($tx['id'], $status, $amount);
        }
    }
}

if (!$res['ok'] && $err_code === '1011') {
    $status_res = snappay_api_status($tx['payment_token']);
    if ($status_res['ok']) {
        $status = snappay_extract_transaction_status($status_res['json'] ?? null);
        $amount = $status_res['json']['response']['amount'] ?? null;
        if ($status !== null) {
            snappay_tx_set_snappay_status($tx['id'], $status, $amount);
        }
        if ($status === 'SETTLE') {
            $_SESSION["code_error"] = "EUpdate failed due to transition state. Retry the update once.";
            return;
        }
        if ($status === 'VERIFY') {
            $_SESSION["code_error"] = "EUpdate blocked because transaction status is VERIFY.";
            return;
        }
        if ($status === 'CANCEL' || $status === 'REVERT') {
            $_SESSION["code_error"] = "EUpdate is not allowed after CANCEL/REVERT.";
            return;
        }
    }
}

if (!$res['ok']) {
    snappay_error_insert(
        (string)$oid,
        (string)$tx['id'],
        'admin_update',
        (string)($res['http_status'] ?? 0),
        $err_code,
        'update_failed',
        substr(snappay_mask_raw_response((string)$res['raw']), 0, 800)
    );

    if (snappay_is_immediate_fail_error_code($err_code)) {
        $_SESSION["code_error"] = "EUpdate failed with non-retryable error code: " . $err_code;
    } else {
        $_SESSION["code_error"] = "ESnappPay update failed.";
    }
    snappay_log('WARN', 'admin.update.failed', [
        'admin_actor' => $admin_actor,
        'oid' => (int)$oid,
        'tx_id' => (int)$tx['id'],
        'error_code' => (string)$err_code,
        'http_status' => (int)($res['http_status'] ?? 0),
    ]);
    return;
}

$status_res = snappay_api_status($tx['payment_token']);
if ($status_res['ok']) {
    $status = snappay_extract_transaction_status($status_res['json'] ?? null);
    $amount = $status_res['json']['response']['amount'] ?? null;
    if ($status !== null) {
        snappay_tx_set_snappay_status($tx['id'], $status, $amount);
    }
}

snappay_log('INFO', 'admin.update.success', [
    'admin_actor' => $admin_actor,
    'oid' => (int)$oid,
    'tx_id' => (int)$tx['id'],
    'transaction_id' => (string)$tx['transaction_id'],
]);

if (function_exists('snappay_insert_processed_request')) {
    snappay_insert_processed_request((int)$oid, 'update', $direct_request_snapshot, 'approved', 'Direct admin action without user request.');
}

$_SESSION["code_error"] = "DSnappPay update completed successfully.";
