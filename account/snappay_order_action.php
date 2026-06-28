<?php

require_once $bu . "modules/snappay/snappay_client.php";
require_once $bu . "modules/snappay/snappay_db.php";
require_once $bu . "modules/snappay/snappay_helpers.php";
include_once $bu . "modules/cart/cart_funcs.php";

if (!function_exists('account_snappay_tx_is_settled')) {
    function account_snappay_tx_is_settled($tx)
    {
        if (!is_array($tx)) return false;
        $sn = strtoupper(trim((string)($tx['snappay_status'] ?? '')));
        $final = strtoupper(trim((string)($tx['final_status'] ?? '')));
        return ($sn === 'SETTLE' || $final === 'SETTLE_OK');
    }
}

if (!function_exists('account_snappay_refresh_tx_status')) {
    function account_snappay_refresh_tx_status($tx)
    {
        if (!is_array($tx)) return $tx;
        $payment_token = (string)($tx['payment_token'] ?? '');
        if ($payment_token === '') return $tx;

        $status_res = snappay_api_status($payment_token);
        if ($status_res['ok']) {
            $status = snappay_extract_transaction_status($status_res['json'] ?? null);
            $amount = $status_res['json']['response']['amount'] ?? null;
            if ($status !== null) {
                snappay_tx_set_snappay_status($tx['id'], $status, $amount);
            }
        }
        return snappay_tx_get_by_id($tx['id']) ?: $tx;
    }
}

if (!function_exists('account_snappay_apply_cancel')) {
    function account_snappay_apply_cancel($oid, $uid, $tx, $state_before)
    {
        $res = snappay_api_cancel((string)$tx['payment_token']);
        $err_code = snappay_extract_error_code($res['json'] ?? null);

        if (!$res['ok'] && snappay_is_retryable_error_code($err_code)) {
            usleep(400000);
            $res = snappay_api_cancel((string)$tx['payment_token']);
            $err_code = snappay_extract_error_code($res['json'] ?? null);
        }

        if (!$res['ok']) {
            snappay_error_insert(
                (string)$oid,
                (string)$tx['id'],
                'user_cancel',
                (string)($res['http_status'] ?? 0),
                $err_code,
                'cancel_failed',
                substr(snappay_mask_raw_response((string)$res['raw']), 0, 800)
            );
            snappay_event_insert($oid, $tx['id'], 'CANCEL', $state_before, $state_before, $uid, 0, $err_code);
            $_SESSION["code_error"] = "Eلغو سفارش اسنپ‌پی ناموفق بود.";
            return false;
        }

        $tx = account_snappay_refresh_tx_status($tx);
        snappay_tx_set_snappay_status($tx['id'], 'CANCEL');
        snappay_tx_set_final_status($tx['id'], 'CANCELLED');

        $cancel_apply_ok = true;
        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
        $mysqli->begin_transaction();
        try{
            $st_cancel_items = $mysqli->prepare("UPDATE sub_orders SET del_flag = 1 WHERE oid = ?");
            if(!$st_cancel_items) throw new Exception('cancel_sub_orders_prepare');
            $oid_sql = (string)$oid;
            $st_cancel_items->bind_param('s',$oid_sql);
            if(!$st_cancel_items->execute()) throw new Exception('cancel_sub_orders_execute');

            $st_zero_totals = $mysqli->prepare("UPDATE orders SET cart_price = 0, cart_pure = 0, sale_total = 0, pay_price = 0 WHERE id = ?");
            if(!$st_zero_totals) throw new Exception('cancel_order_totals_prepare');
            $st_zero_totals->bind_param('s',$oid_sql);
            if(!$st_zero_totals->execute()) throw new Exception('cancel_order_totals_execute');

            $mysqli->commit();
        }catch(Exception $e){
            $mysqli->rollback();
            $cancel_apply_ok = false;
            $cancel_apply_error = (string)$e->getMessage();
        }
        if(!$cancel_apply_ok){
            snappay_event_insert($oid, $tx['id'], 'CANCEL', $state_before, $state_before, $uid, 0, $cancel_apply_error);
            $_SESSION["code_error"] = "Eلغو اسنپ‌پی انجام شد اما ثبت داخلی لغو سفارش ناموفق بود.";
            return false;
        }

        $state_after = $state_before;
        if (defined('SNAPPAY_ORDER_STATE_CANCELLED')) {
            $state_after = (int)SNAPPAY_ORDER_STATE_CANCELLED;
            updateInDB("orders", "state", $state_after, "id", $oid);
        }

        snappay_event_insert($oid, $tx['id'], 'CANCEL', $state_before, $state_after, $uid, 1, null);
        $_SESSION["code_error"] = "Dسفارش اسنپ‌پی با موفقیت لغو شد.";
        return true;
    }
}

if (!function_exists('account_snappay_build_update_payload')) {
    function account_snappay_build_update_payload($oid, $tx, $new_qty_map, &$financial_out = null, &$apply_items_out = null, &$error_out = null)
    {
        $order_create_date = (string)getVarFromDB("orders", "create_date", "id", $oid);
        $old_cart_price = (int)getVarFromDB("orders", "cart_price", "id", $oid);
        $old_cart_pure = (int)getVarFromDB("orders", "cart_pure", "id", $oid);
        $old_sale_total = (int)getVarFromDB("orders", "sale_total", "id", $oid);
        $old_pay_price = (int)getVarFromDB("orders", "pay_price", "id", $oid);

        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
        $st = "SELECT id,pid,weight,number,type FROM sub_orders WHERE oid = ? AND del_flag = 0 AND (type = 'pack' OR type = 'box')";
        $st = $mysqli->prepare($st);
        if (!$st) {
            $error_out = "failed_to_load_sub_orders";
            return false;
        }
        $oid_sql = (string)$oid;
        $st->bind_param('s', $oid_sql);
        if (!$st->execute()) {
            $error_out = "failed_to_load_sub_orders";
            return false;
        }
        $res = $st->get_result();
        $current_items = [];
        while ($row = $res->fetch_assoc()) {
            $current_items[(int)$row['id']] = $row;
        }
        if (count($current_items) === 0) {
            $error_out = "no_items";
            return false;
        }

        $changed = false;
        $all_zero = true;
        $apply_items = [];
        foreach ($current_items as $soid => $row) {
            $current_no = (int)$row['number'];
            $new_no = array_key_exists($soid, $new_qty_map) ? (int)$new_qty_map[$soid] : $current_no;
            if ($new_no < 0) $new_no = 0;
            if ($new_no > $current_no) {
                $error_out = "qty_increase_not_allowed";
                return false;
            }
            if ($new_no != $current_no) $changed = true;
            if ($new_no > 0) $all_zero = false;
            $row['new_number'] = $new_no;
            $apply_items[$soid] = $row;
        }

        if (!$changed) {
            $error_out = "no_change";
            return false;
        }
        if ($all_zero) {
            $error_out = "all_zero";
            $apply_items_out = $apply_items;
            return false;
        }

        $new_cart_price = 0;
        $cart_items = [];
        $idx = 1;
        foreach ($apply_items as $soid => $row) {
            $new_no = (int)$row['new_number'];
            if ($new_no < 1) continue;

            $pid = (int)$row['pid'];
            $weight = (int)$row['weight'];
            $pf = product_finance($pid, $weight, $order_create_date);
            $unit_price = (int)($pf['price'] ?? 0);

            $new_cart_price += ($unit_price * $new_no);

            $category = getVarFromDB('products', 'category', 'id', $pid);
            if (!$category) $category = 'default';
            $commissionType = (int)SNAPPAY_COMMISSION_TYPE_DEFAULT;
            $map = SNAPPAY_COMMISSION_TYPE_MAP;
            if (is_array($map) && array_key_exists($category, $map)) {
                $commissionType = (int)$map[$category];
            }

            $cart_items[] = [
                "amount" => snappay_amount_to_irr($unit_price),
                "category" => (string)$category,
                "count" => $new_no,
                "id" => $idx,
                "name" => (string)getVarFromDB('products', 'name', 'id', $pid),
                "commissionType" => $commissionType
            ];
            $idx++;
        }

        if (count($cart_items) === 0) {
            $error_out = "all_zero";
            $apply_items_out = $apply_items;
            return false;
        }

        $old_cart_sale = max($old_cart_price - $old_cart_pure, 0);
        $shipping_toman = max($old_pay_price + $old_sale_total - $old_cart_price, 0);
        $send_sale = max($old_sale_total - $old_cart_sale, 0);
        $discount_ratio = (($old_pay_price + $old_sale_total) > 0) ? ($old_sale_total / ($old_pay_price + $old_sale_total)) : 0;

        $new_cart_sale = (int)floor($new_cart_price * $discount_ratio);
        $new_sale_total = (int)max($new_cart_price + $shipping_toman, 0) * $discount_ratio;
        $new_total_before_discount = (int)($new_cart_price + $shipping_toman);
        if ($new_sale_total > $new_total_before_discount) {
            $new_sale_total = $new_total_before_discount;
        }
        $new_cart_pure = (int)max($new_cart_price - $new_cart_sale, 0);
        $new_pay_price = (int)max($new_total_before_discount - $new_sale_total, 0);

        if ($new_pay_price <= 0) {
            $error_out = "all_zero";
            $apply_items_out = $apply_items;
            return false;
        }

        if ($new_pay_price >= $old_pay_price || $new_pay_price >= (int)$tx['amount_toman']) {
            $error_out = "amount_not_lower";
            return false;
        }

        $shipping_irr = snappay_amount_to_irr($shipping_toman);
        $tax_irr = (int)SNAPPAY_TAX_AMOUNT_FIXED_IRR;
        $shipment_included = (bool)SNAPPAY_IS_SHIPMENT_INCLUDED;
        $tax_included = (bool)SNAPPAY_IS_TAX_INCLUDED;

        $cart_items_total_irr = 0;
        foreach ($cart_items as $ci) {
            $cart_items_total_irr += ((int)$ci['amount'] * (int)$ci['count']);
        }
        $cart_total_irr = $cart_items_total_irr;
        if (!$shipment_included) $cart_total_irr += $shipping_irr;
        if (!$tax_included) $cart_total_irr += $tax_irr;

        $payload = [
            "amount" => snappay_amount_to_irr($new_pay_price),
            "cartList" => [[
                "cartId" => 1,
                "cartItems" => $cart_items,
                "isShipmentIncluded" => $shipment_included,
                "isTaxIncluded" => $tax_included,
                "shippingAmount" => $shipping_irr,
                "taxAmount" => $tax_irr,
                "totalAmount" => $cart_total_irr
            ]],
            "discountAmount" => snappay_amount_to_irr($new_sale_total),
            "externalSourceAmount" => 0,
            "paymentToken" => (string)$tx['payment_token']
        ];

        $financial_out = [
            'cart_price' => $new_cart_price,
            'cart_pure' => $new_cart_pure,
            'sale_total' => $new_sale_total,
            'pay_price' => $new_pay_price
        ];
        $apply_items_out = $apply_items;
        return $payload;
    }
}

if (!isset($_SESSION["logged"]) || !isset($_SESSION["logged"]->uid)) {
    $_SESSION["code_error"] = "Eدسترسی مجاز نیست.";
    return;
}

$uid = (int)$_SESSION["logged"]->uid;
$oid = (int)($_SESSION[$cf]->id ?? 0);
if ($oid < 1 || (int)getVarFromDB("orders", "uid", "id", $oid) !== $uid) {
    $_SESSION["code_error"] = "Eسفارش معتبر نیست.";
    return;
}

if (
    !isset($_POST['snappay_user_csrf']) ||
    !isset($_SESSION['snappay_user_csrf']) ||
    !is_string($_POST['snappay_user_csrf']) ||
    !is_string($_SESSION['snappay_user_csrf']) ||
    !hash_equals($_SESSION['snappay_user_csrf'], $_POST['snappay_user_csrf'])
) {
    $_SESSION["code_error"] = "Eدرخواست نامعتبر است.";
    return;
}

$order_state = (int)getVarFromDB("orders", "state", "id", $oid);
if ($order_state < 0 || $order_state > 2) {
    $_SESSION["code_error"] = "Eامکان تغییر سفارش در وضعیت فعلی وجود ندارد.";
    return;
}

$tx = snappay_tx_get_latest_for_order($oid);
if (!$tx || !isset($tx['payment_token']) || (string)$tx['payment_token'] === '') {
    $_SESSION["code_error"] = "Eتراکنش اسنپ‌پی برای این سفارش یافت نشد.";
    return;
}
$tx = account_snappay_refresh_tx_status($tx);
if (!account_snappay_tx_is_settled($tx)) {
    $_SESSION["code_error"] = "Eتراکنش هنوز در وضعیت تسویه نیست.";
    return;
}

$action = strtolower(trim((string)($_POST['snappay_user_action'] ?? '')));
if ($action !== 'update' && $action !== 'cancel') {
    $_SESSION["code_error"] = "Eعملیات نامعتبر است.";
    return;
}

if ($action === 'cancel') {
    account_snappay_apply_cancel($oid, $uid, $tx, $order_state);
    return;
}

$new_qty_map = [];
if (isset($_POST['snappay_qty']) && is_array($_POST['snappay_qty'])) {
    foreach ($_POST['snappay_qty'] as $soid => $qty) {
        $new_qty_map[(int)$soid] = (int)$qty;
    }
}

$financial = null;
$apply_items = null;
$build_error = null;
$payload = account_snappay_build_update_payload($oid, $tx, $new_qty_map, $financial, $apply_items, $build_error);

if ($payload === false) {
    if ($build_error === 'all_zero') {
        account_snappay_apply_cancel($oid, $uid, $tx, $order_state);
        return;
    }
    if ($build_error === 'no_change') {
        $_SESSION["code_error"] = "Eتغییری در سفارش ثبت نشده است.";
    } elseif ($build_error === 'qty_increase_not_allowed') {
        $_SESSION["code_error"] = "Eافزایش تعداد آیتم‌ها مجاز نیست.";
    } elseif ($build_error === 'amount_not_lower') {
        $_SESSION["code_error"] = "Eمبلغ جدید باید کمتر از مبلغ فعلی سفارش باشد.";
    } else {
        $_SESSION["code_error"] = "Eامکان آماده‌سازی درخواست به‌روزرسانی وجود ندارد.";
    }
    return;
}

$res = snappay_api_update($payload);
$err_code = snappay_extract_error_code($res['json'] ?? null);
if (!$res['ok'] && snappay_is_retryable_error_code($err_code)) {
    usleep(400000);
    $res = snappay_api_update($payload);
    $err_code = snappay_extract_error_code($res['json'] ?? null);
}

if (!$res['ok']) {
    snappay_error_insert(
        (string)$oid,
        (string)$tx['id'],
        'user_update',
        (string)($res['http_status'] ?? 0),
        $err_code,
        'update_failed',
        substr(snappay_mask_raw_response((string)$res['raw']), 0, 800)
    );
    snappay_event_insert($oid, $tx['id'], 'UPDATE', $order_state, $order_state, $uid, 0, $err_code);
    $_SESSION["code_error"] = "Eبه‌روزرسانی سفارش اسنپ‌پی ناموفق بود.";
    return;
}

include $bu . $dbc_adrs;
$mysqli->begin_transaction();
try {
    foreach ($apply_items as $soid => $row) {
        $new_no = (int)$row['new_number'];
        $item_type = (string)$row['type'];

        if ($new_no <= 0) {
            $st = $mysqli->prepare("UPDATE sub_orders SET del_flag = 1 WHERE id = ?");
            if (!$st) throw new Exception('sub_orders_delete_prepare');
            $soid_sql = (string)$soid;
            $st->bind_param('s', $soid_sql);
            if (!$st->execute()) throw new Exception('sub_orders_delete_execute');

            if ($item_type === 'box') {
                $st2 = $mysqli->prepare("UPDATE sub_orders SET del_flag = 1 WHERE so_id = ?");
                if (!$st2) throw new Exception('sub_orders_piece_delete_prepare');
                $st2->bind_param('s', $soid_sql);
                if (!$st2->execute()) throw new Exception('sub_orders_piece_delete_execute');
            }
        } else {
            $st = $mysqli->prepare("UPDATE sub_orders SET number = ?, del_flag = 0 WHERE id = ?");
            if (!$st) throw new Exception('sub_orders_update_prepare');
            $new_no_sql = (string)$new_no;
            $soid_sql = (string)$soid;
            $st->bind_param('ss', $new_no_sql, $soid_sql);
            if (!$st->execute()) throw new Exception('sub_orders_update_execute');
        }
    }

    $st = $mysqli->prepare("UPDATE orders SET cart_price = ?, cart_pure = ?, sale_total = ?, pay_price = ? WHERE id = ?");
    if (!$st) throw new Exception('orders_update_prepare');
    $cart_price_sql = (string)$financial['cart_price'];
    $cart_pure_sql = (string)$financial['cart_pure'];
    $sale_total_sql = (string)$financial['sale_total'];
    $pay_price_sql = (string)$financial['pay_price'];
    $oid_sql = (string)$oid;
    $st->bind_param('sssss', $cart_price_sql, $cart_pure_sql, $sale_total_sql, $pay_price_sql, $oid_sql);
    if (!$st->execute()) throw new Exception('orders_update_execute');

    $mysqli->commit();
} catch (Exception $e) {
    $mysqli->rollback();
    snappay_event_insert($oid, $tx['id'], 'UPDATE', $order_state, $order_state, $uid, 0, (string)$e->getMessage());
    $_SESSION["code_error"] = "Eبه‌روزرسانی سفارش انجام شد اما ثبت تغییرات داخلی ناموفق بود.";
    return;
}

$tx = account_snappay_refresh_tx_status($tx);
snappay_event_insert($oid, $tx['id'], 'UPDATE', $order_state, (int)getVarFromDB("orders","state","id",$oid), $uid, 1, null);
$_SESSION["code_error"] = "Dسفارش اسنپ‌پی با موفقیت به‌روزرسانی شد.";
