<?php

require_once $bu . "modules/snappay/snappay_client.php";
require_once $bu . "modules/snappay/snappay_db.php";
require_once $bu . "modules/snappay/snappay_helpers.php";
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
    $_SESSION["code_error"] = "EPlease confirm cancellation first.";
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
    $_SESSION["code_error"] = "ETransaction is not in a cancellable state.";
    return;
}

$direct_request_snapshot = null;
if (function_exists('snappay_capture_order_snapshot')) {
    $direct_request_snapshot = snappay_capture_order_snapshot((int)$oid, 'cancel');
    if (is_array($direct_request_snapshot)) {
        $direct_request_snapshot['source'] = 'admin_direct';
    }
}

snappay_log('INFO', 'admin.cancel.request', [
    'admin_actor' => $admin_actor,
    'oid' => (int)$oid,
    'tx_id' => (int)$tx['id'],
    'transaction_id' => (string)$tx['transaction_id'],
]);

$res = snappay_api_cancel($tx['payment_token']);
$err_code = snappay_extract_error_code($res['json'] ?? null);

if (!$res['ok'] && $err_code === '1053') {
    usleep(400000);
    $res = snappay_api_cancel($tx['payment_token']);
    $err_code = snappay_extract_error_code($res['json'] ?? null);
}

if (!$res['ok'] && $err_code === '1053') {
    $status_res = snappay_api_status($tx['payment_token']);
    if ($status_res['ok']) {
        $status = snappay_extract_transaction_status($status_res['json'] ?? null);
        $amount = $status_res['json']['response']['amount'] ?? null;
        if ($status !== null) {
            snappay_tx_set_snappay_status($tx['id'], $status, $amount);
        }
        if ($status === 'CANCEL') {
            $res['ok'] = true;
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
        if ($status === 'CANCEL') {
            $res['ok'] = true;
        } elseif ($status === 'SETTLE') {
            $_SESSION["code_error"] = "ECancel failed. Transaction is still SETTLE.";
            return;
        } elseif ($status === 'VERIFY') {
            $_SESSION["code_error"] = "ECancel failed. Transaction is VERIFY and must be settled first.";
            return;
        } elseif ($status === 'REVERT') {
            $_SESSION["code_error"] = "ETransaction is already REVERT and cannot be cancelled.";
            return;
        }
    }
}

if (!$res['ok']) {
    snappay_error_insert(
        (string)$oid,
        (string)$tx['id'],
        'admin_cancel',
        (string)($res['http_status'] ?? 0),
        $err_code,
        'cancel_failed',
        substr(snappay_mask_raw_response((string)$res['raw']), 0, 800)
    );

    if (snappay_is_immediate_fail_error_code($err_code)) {
        $_SESSION["code_error"] = "ECancel failed with non-retryable error code: " . $err_code;
    } elseif ($err_code === '1000') {
        $_SESSION["code_error"] = "ECancel failed with temporary gateway error (1000). Please retry manually.";
    } else {
        $_SESSION["code_error"] = "ECancel request to SnappPay failed.";
    }
    snappay_log('WARN', 'admin.cancel.failed', [
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
    } else {
        snappay_tx_set_snappay_status($tx['id'], 'CANCEL');
    }
} else {
    snappay_tx_set_snappay_status($tx['id'], 'CANCEL');
}
snappay_tx_set_final_status($tx['id'], 'CANCELLED');

if (SNAPPAY_ORDER_STATE_CANCELLED !== null) {
    updateInDB("orders", "state", (int)SNAPPAY_ORDER_STATE_CANCELLED, "id", $oid);
} else {
    $_SESSION["code_error"] = "DCancel succeeded but SNAPPAY_ORDER_STATE_CANCELLED is not configured.";
    return;
}

snappay_log('INFO', 'admin.cancel.success', [
    'admin_actor' => $admin_actor,
    'oid' => (int)$oid,
    'tx_id' => (int)$tx['id'],
    'transaction_id' => (string)$tx['transaction_id'],
]);

if (function_exists('snappay_insert_processed_request')) {
    snappay_insert_processed_request((int)$oid, 'cancel', $direct_request_snapshot, 'approved', 'Direct admin action without user request.');
}

$_SESSION["code_error"] = "DSnappPay cancellation completed successfully.";
