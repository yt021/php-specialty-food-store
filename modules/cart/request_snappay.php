<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

require_once($bu."modules/snappay/snappay_client.php");
require_once($bu."modules/snappay/snappay_db.php");
require_once($bu."modules/snappay/snappay_helpers.php");
include_once($bu."modules/cart/cart_funcs.php");

if (!snappay_checkout_enabled()) {
    $_SESSION['error'] = 'Eروش پرداخت اقساطی اسنپ‌پی در حال حاضر غیرفعال است.';
    header("Location:$s"."cart/");
    die;
}

if (!snappay_backend_enabled()) {
    $_SESSION['error'] = 'ESnappPay backend is disabled by admin.';
    header("Location:$s"."cart/");
    die;
}

if (!isset($_SESSION['oid'])) {
    $_SESSION['error'] = 'Eسفارش نامعتبر است.';
    header("Location:$s"."cart/");
    die;
}

$oid = (int)$_SESSION['oid'];

$tb = "orders";
$amount_toman = (int)getVarFromDB($tb, 'pay_price', 'id', $oid);
$amount_irr = snappay_amount_to_irr($amount_toman);

// Eligible check (required): method selection comes from SNAPPAY_ELIGIBLE_PAYMENT_METHOD_TYPES.
$eligible_res = snappay_api_eligible($amount_irr);
if (!$eligible_res['ok']) {
    snappay_error_insert($oid, null, 'eligible', (string)($eligible_res['http_status'] ?? 0), null, 'eligible_failed', substr(snappay_mask_raw_response((string)$eligible_res['raw']), 0, 500));
    $_SESSION['error'] = 'Eدر حال حاضر امکان استفاده از اسنپ‌پی برای این سفارش وجود ندارد.';
    header("Location:$s"."cart/");
    die;
}

$eligible = (bool)($eligible_res['json']['response']['eligible'] ?? false);
if (!$eligible) {
    $_SESSION['error'] = 'Eدر حال حاضر امکان استفاده از اسنپ‌پی برای این سفارش وجود ندارد.';
    header("Location:$s"."cart/");
    die;
}

$uid = (int)getVarFromDB($tb, 'uid', 'id', $oid);
$tel = (string)getVarFromDB('users', 'tel', 'id', $uid);
$mobile_e164 = snappay_tel_to_e164($tel);
if (!$mobile_e164) {
    snappay_error_insert($oid, null, 'mobile', 0, null, 'mobile_missing', 'user_tel_missing_or_invalid');
    $_SESSION['error'] = 'Eشماره موبایل برای پرداخت اقساطی معتبر نیست.';
    header("Location:$s"."cart/");
    die;
}

$attempt_no = null;
$transaction_id = null;
$tx_id = null;

// Insert transaction row first for idempotency (retry-safe for MyISAM + unique constraints)
for ($i = 0; $i < 3; $i++) {
    $attempt_no = snappay_tx_next_attempt_no($oid);
    $transaction_id = 'o' . $oid . 'a' . $attempt_no;
    $tx_id = snappay_tx_insert($oid, $attempt_no, $transaction_id, $amount_toman, $amount_irr);
    if ($tx_id) break;
    usleep(100000);
}
if (!$tx_id) {
    $_SESSION['error'] = 'Eخطا در ایجاد تراکنش اسنپ‌پی.';
    header("Location:$s"."cart/");
    die;
}

[$payload_ok, $payload_or_err] = snappay_build_cart_payload_from_order($oid, $transaction_id, $mobile_e164);
if (!$payload_ok) {
    snappay_error_insert($oid, $tx_id, 'build_payload', 0, null, 'payload_failed', (string)$payload_or_err);
    $_SESSION['error'] = 'Eخطا در آماده‌سازی اطلاعات پرداخت اسنپ‌پی.';
    header("Location:$s"."cart/");
    die;
}
$payload = $payload_or_err;

$token_res = snappay_api_token($payload);
$err_code = snappay_extract_error_code($token_res['json'] ?? null);

if (!$token_res['ok'] && snappay_is_retryable_error_code($err_code)) {
    usleep(400000);
    $token_res = snappay_api_token($payload);
    $err_code = snappay_extract_error_code($token_res['json'] ?? null);
}

if (!$token_res['ok']) {
    snappay_error_insert($oid, $tx_id, 'token', (string)($token_res['http_status'] ?? 0), $err_code, 'token_failed', substr(snappay_mask_raw_response((string)$token_res['raw']), 0, 800));

    // If SnappPay reports duplicated transactionId (commonly errorCode=1009), retry once with a new attempt/transactionId.
    if ($err_code === '1009') {
        snappay_tx_set_final_status($tx_id, 'FAILED_DUPLICATE_TXID');

        $attempt_no_2 = null;
        $transaction_id_2 = null;
        $tx_id_2 = null;
        for ($i = 0; $i < 3; $i++) {
            $attempt_no_2 = snappay_tx_next_attempt_no($oid);
            $transaction_id_2 = 'o' . $oid . 'a' . $attempt_no_2;
            $tx_id_2 = snappay_tx_insert($oid, $attempt_no_2, $transaction_id_2, $amount_toman, $amount_irr);
            if ($tx_id_2) break;
            usleep(100000);
        }
        if ($tx_id_2) {
            [$payload2_ok, $payload2_or_err] = snappay_build_cart_payload_from_order($oid, $transaction_id_2, $mobile_e164);
            if ($payload2_ok) {
                $token_res_2 = snappay_api_token($payload2_or_err);
                $err_code_2 = snappay_extract_error_code($token_res_2['json'] ?? null);
                if (!$token_res_2['ok'] && snappay_is_retryable_error_code($err_code_2)) {
                    usleep(400000);
                    $token_res_2 = snappay_api_token($payload2_or_err);
                    $err_code_2 = snappay_extract_error_code($token_res_2['json'] ?? null);
                }
                if ($token_res_2['ok']) {
                    $tx_id = $tx_id_2;
                    $token_res = $token_res_2;
                } else {
                    snappay_error_insert($oid, $tx_id_2, 'token_retry', (string)($token_res_2['http_status'] ?? 0), $err_code_2, 'token_failed', substr(snappay_mask_raw_response((string)$token_res_2['raw']), 0, 800));
                }
            } else {
                snappay_error_insert($oid, $tx_id_2, 'build_payload_retry', 0, null, 'payload_failed', (string)$payload2_or_err);
            }
        }
    }

    if (!$token_res['ok']) {
        $_SESSION['error'] = 'Eخطا در ایجاد توکن پرداخت اسنپ‌پی. لطفاً مجدد تلاش کنید.';
        header("Location:$s"."cart/");
        die;
    }
}

$payment_token = (string)($token_res['json']['response']['paymentToken'] ?? '');
$payment_page_url = (string)($token_res['json']['response']['paymentPageUrl'] ?? '');

if ($payment_token === '' || $payment_page_url === '') {
    snappay_error_insert($oid, $tx_id, 'token_parse', (string)($token_res['http_status'] ?? 0), null, 'token_parse_failed', substr(snappay_mask_raw_response((string)$token_res['raw']), 0, 800));
    $_SESSION['error'] = 'Eپاسخ نامعتبر از اسنپ‌پی دریافت شد.';
    header("Location:$s"."cart/");
    die;
}

snappay_tx_set_payment_token($tx_id, $payment_token);

header("Location: $payment_page_url");
die;

?>
<?php
        }
    }
?>

