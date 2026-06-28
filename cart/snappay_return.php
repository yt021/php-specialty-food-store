<?php
$indexed = 1;
include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
include $bu."modules/cart/session_start.php";

require_once($bu."modules/snappay/snappay_client.php");
require_once($bu."modules/snappay/snappay_db.php");
require_once($bu."modules/snappay/snappay_helpers.php");
require_once($bu."modules/snappay/snappay_finalize.php");

$transactionId_raw = null;
$state_raw = null;
$amount_raw = null;
if(isset($_POST['transactionId'])) $transactionId_raw = $_POST['transactionId'];
elseif(isset($_GET['transactionId'])) $transactionId_raw = $_GET['transactionId'];
if(isset($_POST['state'])) $state_raw = $_POST['state'];
elseif(isset($_GET['state'])) $state_raw = $_GET['state'];
if(isset($_POST['amount'])) $amount_raw = $_POST['amount'];
elseif(isset($_GET['amount'])) $amount_raw = $_GET['amount'];

$transactionId = $transactionId_raw !== null ? check_value("text", $transactionId_raw) : null;
$state = $state_raw !== null ? check_value("text", $state_raw) : null;
$amount = $amount_raw !== null ? (int)$amount_raw : null;

$error = null;
$done = false;
$success = false;
$tx = null;
$oid = null;
$last_error_code = null;
$amount_mismatch_detected = false;

if (!snappay_backend_enabled()) {
    $error = "ESnappPay backend is disabled by admin.";
    $done = true;
}

function snappay_refresh_status($tx, $payment_token, $stage)
{
    $status_res = snappay_api_status($payment_token);
    if ($status_res['ok']) {
        $st = snappay_extract_transaction_status($status_res['json'] ?? null);
        $amt = $status_res['json']['response']['amount'] ?? null;
        if ($st !== null) {
            snappay_tx_set_snappay_status($tx['id'], $st, $amt);
        }
        return [$st, $status_res];
    }
    $err_code = snappay_extract_error_code($status_res['json'] ?? null);
    snappay_error_insert(
        (string)$tx['oid'],
        (string)$tx['id'],
        $stage . '_status',
        (string)($status_res['http_status'] ?? 0),
        $err_code,
        'status_failed',
        substr(snappay_mask_raw_response((string)$status_res['raw']), 0, 800)
    );
    return [null, $status_res];
}

if (!$transactionId || !$state) {
    $error = "Eدرخواست نامعتبر است.";
} elseif (!snappay_is_valid_transaction_id((string)$transactionId)) {
    $error = "EInvalid transaction reference.";
}

if ($error === null && is_array(SNAPPAY_ALLOWED_CALLBACK_IPS) && count(SNAPPAY_ALLOWED_CALLBACK_IPS) > 0) {
    $ipAddress = snappay_get_request_ip(defined('SNAPPAY_CALLBACK_IP_USE_XFF') ? (bool)SNAPPAY_CALLBACK_IP_USE_XFF : false);
    if (!snappay_ip_in_allowlist($ipAddress, SNAPPAY_ALLOWED_CALLBACK_IPS)) {
        $error = "Eدسترسی غیرمجاز.";
    }
}

if ($error === null) {
    $tx = snappay_tx_get_by_transaction_id($transactionId);
    if (!$tx) {
        $error = "Eتراکنش اسنپ‌پی یافت نشد.";
    } else {
        $oid = (int)$tx['oid'];
    }
}

if ($error === null && $tx) {
    if ($amount !== null && isset($tx['amount_irr']) && (int)$tx['amount_irr'] > 0 && (int)$amount !== (int)$tx['amount_irr']) {
        $amount_mismatch_detected = true;
        snappay_error_insert((string)$tx['oid'], (string)$tx['id'], 'callback_amount_mismatch', 0, null, 'amount_mismatch', 'callback_amount=' . (int)$amount . ' expected=' . (int)$tx['amount_irr']);
        snappay_log('WARN', 'callback.amount_mismatch', [
            'oid' => (int)$tx['oid'],
            'tx_id' => (int)$tx['id'],
            'callback_amount' => (int)$amount,
            'expected_amount' => (int)$tx['amount_irr'],
        ]);
    }
}

if ($error === null && $tx) {
    snappay_tx_record_callback($tx['id'], $state, $amount);
    $tx = snappay_tx_get_by_id($tx['id']) ?: $tx;

    if (in_array((string)($tx['final_status'] ?? ''), ['SETTLE_OK', 'CANCELLED', 'FAILED'], true)) {
        if ((string)$tx['final_status'] === 'SETTLE_OK') {
            snappay_finalize_paid_order_once($oid, $transactionId);
            $success = true;
        } elseif ((string)$tx['final_status'] === 'CANCELLED') {
            $error = "Eپرداخت لغو شده است.";
        } else {
            $error = "E" . snappay_user_message_for_error_code($last_error_code);
        }
        $done = true;
    } elseif ($state === 'FAILED') {
        snappay_tx_set_final_status($tx['id'], 'FAILED');
        $error = "E" . snappay_user_message_for_error_code($last_error_code);
        $done = true;
    } elseif ($state !== 'OK') {
        $error = "Eوضعیت نامعتبر از اسنپ‌پی دریافت شد.";
    }
}

if ($error === null && !$done && $tx) {
    $payment_token = (string)$tx['payment_token'];
    if ($payment_token === '') {
        $error = "Eتوکن پرداخت موجود نیست.";
    } elseif (
        (string)($tx['final_status'] ?? '') === 'SETTLE_OK' ||
        (string)($tx['settle_status'] ?? '') === 'SUCCESS' ||
        (string)($tx['snappay_status'] ?? '') === 'SETTLE'
    ) {
        if ($amount_mismatch_detected) {
            [$st, $status_res] = snappay_refresh_status($tx, $payment_token, 'amount_guard');
            $status_amount = $status_res['json']['response']['amount'] ?? null;
            if (!$status_res['ok'] || $st !== 'SETTLE' || ((int)$status_amount > 0 && (int)$status_amount !== (int)$tx['amount_irr'])) {
                $error = "EPayment validation failed.";
            }
        }
    }
}

if ($error === null && !$done && $tx && (
    (string)($tx['final_status'] ?? '') === 'SETTLE_OK' ||
    (string)($tx['settle_status'] ?? '') === 'SUCCESS' ||
    (string)($tx['snappay_status'] ?? '') === 'SETTLE'
)) {
        snappay_tx_set_final_status($tx['id'], 'SETTLE_OK');
        snappay_finalize_paid_order_once($oid, $transactionId);
        $success = true;
        $done = true;
}

if ($error === null && !$done && $tx) {
    $payment_token = (string)$tx['payment_token'];
    $verify_ok = false;
    $already_settled = false;
    $verify_attempts = 0;

    if ((string)($tx['verify_status'] ?? '') === 'SUCCESS') {
        $verify_ok = true;
    }

    while ($verify_attempts < 3 && !$verify_ok && $error === null) {
        $verify_attempts++;
        $claimed = snappay_tx_claim_verify($tx['id']);
        if (!$claimed) {
            $tx_latest = snappay_tx_get_by_id($tx['id']) ?: $tx;
            if (
                (string)($tx_latest['final_status'] ?? '') === 'SETTLE_OK' ||
                (string)($tx_latest['settle_status'] ?? '') === 'SUCCESS' ||
                (string)($tx_latest['snappay_status'] ?? '') === 'SETTLE'
            ) {
                $verify_ok = true;
                $already_settled = true;
                break;
            }

            [$st, $status_res] = snappay_refresh_status($tx_latest, $payment_token, 'verify_claim');
            if ($st === 'VERIFY') {
                $verify_ok = true;
                break;
            }
            if ($st === 'SETTLE') {
                $verify_ok = true;
                $already_settled = true;
                break;
            }
            if ($st === 'PENDING') {
                usleep(250000);
                continue;
            }
            if ($st === 'REVERT' || $st === 'CANCEL') {
                snappay_tx_set_final_status($tx['id'], 'FAILED');
                $error = "E" . snappay_user_message_for_error_code($last_error_code);
                break;
            }
            usleep(250000);
            continue;
        }

        $verify_res = snappay_api_verify($payment_token, 30);
        if ($verify_res['ok']) {
            snappay_tx_set_verify_result($tx['id'], 'SUCCESS');
            $verify_ok = true;
            break;
        }

        $verify_err_code = snappay_extract_error_code($verify_res['json'] ?? null);
        $last_error_code = $verify_err_code;
        if ($verify_res['timeout']) {
            snappay_tx_set_verify_result($tx['id'], 'TIMEOUT');
        } else {
            snappay_tx_set_verify_result($tx['id'], 'FAILED');
        }
        snappay_error_insert((string)$tx['oid'], (string)$tx['id'], 'verify', (string)($verify_res['http_status'] ?? 0), $verify_err_code, 'verify_failed', substr(snappay_mask_raw_response((string)$verify_res['raw']), 0, 800));

        if (snappay_is_retryable_error_code($verify_err_code)) {
            usleep(400000);
        }

        [$st, $status_res] = snappay_refresh_status($tx, $payment_token, 'verify');
        if ($st === 'VERIFY') {
            $verify_ok = true;
            break;
        }
        if ($st === 'SETTLE') {
            $verify_ok = true;
            $already_settled = true;
            break;
        }
        if ($st === 'PENDING') {
            continue;
        }
        if ($st === 'REVERT' || $st === 'CANCEL' || snappay_is_immediate_fail_error_code($verify_err_code)) {
            snappay_tx_set_final_status($tx['id'], 'FAILED');
            $error = "E" . snappay_user_message_for_error_code($verify_err_code);
            break;
        }
    }

    if ($error === null && $verify_ok && $already_settled) {
        snappay_tx_set_settle_result($tx['id'], 'SUCCESS');
        snappay_tx_set_final_status($tx['id'], 'SETTLE_OK');
        snappay_finalize_paid_order_once($oid, $transactionId);
        $success = true;
        $done = true;
    }

    if ($error === null && $verify_ok && !$done) {
        $settle_ok = false;
        $settle_attempts = 0;
        while ($settle_attempts < 3 && !$settle_ok && $error === null) {
            $settle_attempts++;
            $claimed = snappay_tx_claim_settle($tx['id']);
            if (!$claimed) {
                $tx_latest = snappay_tx_get_by_id($tx['id']) ?: $tx;
                if (
                    (string)($tx_latest['final_status'] ?? '') === 'SETTLE_OK' ||
                    (string)($tx_latest['settle_status'] ?? '') === 'SUCCESS' ||
                    (string)($tx_latest['snappay_status'] ?? '') === 'SETTLE'
                ) {
                    $settle_ok = true;
                    break;
                }

                [$st, $status_res] = snappay_refresh_status($tx_latest, $payment_token, 'settle_claim');
                if ($st === 'SETTLE') {
                    $settle_ok = true;
                    break;
                }
                if ($st === 'VERIFY' || $st === 'PENDING') {
                    usleep(250000);
                    continue;
                }
                if ($st === 'REVERT' || $st === 'CANCEL') {
                    snappay_tx_set_final_status($tx['id'], 'FAILED');
                    $error = "E" . snappay_user_message_for_error_code($last_error_code);
                    break;
                }
                usleep(250000);
                continue;
            }

            $settle_res = snappay_api_settle($payment_token, 30);
            if ($settle_res['ok']) {
                snappay_tx_set_settle_result($tx['id'], 'SUCCESS');
                $settle_ok = true;
                break;
            }

            $settle_err_code = snappay_extract_error_code($settle_res['json'] ?? null);
            $last_error_code = $settle_err_code;
            snappay_tx_set_settle_result($tx['id'], 'RETRY');
            snappay_error_insert((string)$tx['oid'], (string)$tx['id'], 'settle', (string)($settle_res['http_status'] ?? 0), $settle_err_code, 'settle_failed', substr(snappay_mask_raw_response((string)$settle_res['raw']), 0, 800));

            if (snappay_is_retryable_error_code($settle_err_code)) {
                usleep(400000);
            }

            [$st, $status_res] = snappay_refresh_status($tx, $payment_token, 'settle');
            if ($st === 'SETTLE') {
                $settle_ok = true;
                break;
            }
            if ($st === 'VERIFY' || $st === 'PENDING') {
                continue;
            }
            if ($st === 'REVERT' || $st === 'CANCEL' || snappay_is_immediate_fail_error_code($settle_err_code)) {
                snappay_tx_set_final_status($tx['id'], 'FAILED');
                $error = "E" . snappay_user_message_for_error_code($settle_err_code);
                break;
            }
        }

        if ($error === null && $settle_ok) {
            snappay_tx_set_final_status($tx['id'], 'SETTLE_OK');
            snappay_finalize_paid_order_once($oid, $transactionId);
            $success = true;
            $done = true;
        }
    }
}

if ($error === null && $done && $tx && (string)($tx['final_status'] ?? '') === 'SETTLE_OK') {
    $success = true;
}

if ($error === null && $done && !$success) {
    $error = "E" . snappay_user_message_for_error_code($last_error_code);
}

if ($success) {
    $_SESSION["cart_notice"] = "Dپرداخت با موفقیت انجام شد.";
    if ($oid) {
        $_SESSION["oid"] = (int)$oid;
    }
    if ($oid) {
        $_SESSION["cart_notice"] .= "<br />شناسه سفارش: " . (int)$oid;
    }
    if ($transactionId) {
        $_SESSION["cart_notice"] .= "<br />شناسه تراکنش: " . htmlspecialchars((string)$transactionId, ENT_QUOTES, 'UTF-8');
    }
    if (!isset($_SESSION["cart_page"]) || !is_object($_SESSION["cart_page"])) {
        $_SESSION["cart_page"] = new where_u("cart_page");
    }
    $_SESSION["cart_return_locked"] = 1;
    $_SESSION["cart_page"]->level = 7;
    header("Location:" . $s . "cart/");
    die;
}

?>

<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" class="" lang="en">
<?php include $bu."modules/main/head.php"; ?>
<body>
<?php include $bu."modules/main/header.php"; ?>

<main>
    <div class="content checkout_flow_page">
        <style type="text/css">
            .payment_result_header{border:1px solid rgba(0,0,0,0.12);border-radius:18px;background:linear-gradient(135deg,#fff 0%,#f7f2ef 100%);padding:10px 14px;margin-bottom:12px;}
            .payment_result_kicker{margin:0 0 2px 0;font-size:22px;line-height:1.15;font-weight:800;color:#4a2d22;text-align:right;}
            .payment_result_title{margin:0;font-size:14px;line-height:1.35;font-weight:600;color:#8a6d5d;text-align:right;}
            .payment_result_panel{border:1px solid rgba(0,0,0,0.12);border-radius:14px;background:#fff;padding:14px 16px;box-sizing:border-box;}
            .payment_result_actions{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-top:14px;}
            .payment_result_actions .btn{min-width:220px;margin:0 !important;text-align:center;box-sizing:border-box;}
            @media (max-width:640px){.payment_result_header{padding:10px 12px;}.payment_result_kicker{font-size:18px;}.payment_result_title{font-size:13px;}.payment_result_actions .btn{min-width:0;width:min(360px,100%);}}
        </style>
        <div class="payment_result_header">
            <h1 class="payment_result_kicker">نتیجه پرداخت</h1>
            <p class="payment_result_title">وضعیت تراکنش اسنپ‌پی و دسترسی به اقدام بعدی</p>
        </div>
        <div class="payment_result_panel">
        <div class="payment_result_actions">
        <?php if ($error !== null) { ?>
            <div class="user_hint error"><?php echo substr($error, 1); ?></div>
            <a class="btn" href="<?php echo $s; ?>cart/">اقدام مجدد</a>
        <?php } else if ($success) { ?>
            <div class="user_hint">پرداخت با موفقیت انجام شد.</div>
            <a class="btn" href="<?php echo $s; ?>account/">حساب کاربری</a>
        <?php } else { ?>
            <div class="user_hint">در حال پردازش نتیجه پرداخت...</div>
            <a class="btn" href="<?php echo $s; ?>account/">پیگیری سفارش</a>
        <?php } ?>
        </div>
        </div>
    </div>
</main>

<?php include $bu."modules/main/footer.php"; ?>
</body>
</html>
