<?php

require_once __DIR__ . '/snappay_logger.php';

if (!function_exists('snappay_finalize_paid_order_once')) {
    function snappay_finalize_paid_order_once($oid, $transaction_id, $clear_session_cart = true)
    {
        $tb = "orders";
        $now = date("Y-m-d H:i:s", time());
        $did_finalize = false;
        if (!function_exists('cart_draft_clear_for_uid') && isset($GLOBALS['bu'])) {
            @include_once $GLOBALS['bu'] . "modules/cart/cart_funcs.php";
        }

        if (isset($GLOBALS['bu']) && isset($GLOBALS['dbc_adrs'])) {
            include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
            if (isset($mysqli)) {
                $st = "UPDATE orders SET payment_date = ?, state = 1, pay_id = ? WHERE id = ? AND state < 1";
                $st = $mysqli->prepare($st);
                if ($st) {
                    $oid_s = (string)$oid;
                    $txid_s = (string)$transaction_id;
                    $st->bind_param('sss', $now, $txid_s, $oid_s);
                    if ($st->execute() && $st->affected_rows === 1) {
                        $did_finalize = true;
                    }
                }
            }
        }

        if (!$did_finalize) {
            $order_state = (int)getVarFromDB($tb, 'state', 'id', $oid);
            if ($order_state >= 1) return false;
            updateInDB($tb, 'payment_date', $now, 'id', $oid);
            updateInDB($tb, 'state', 1, 'id', $oid);
            updateInDB($tb, 'pay_id', $transaction_id, 'id', $oid);
        }

        $p_send_date = getVarFromDB($tb, 'p_send_date', 'id', $oid);
        $day_id = 0;
        if ($p_send_date && var_exist($p_send_date, "send_date", "date")) {
            $day_id = (int)getVarFromDB("send_date", "id", "date", $p_send_date);
        }
        if ($day_id > 0 && var_exist($day_id, "send_date", "id")) {
            $on = (int)getVarFromDB("send_date", "order_no", "id", $day_id);
            $on++;
            updateInDB("send_date", "order_no", $on, "id", $day_id);
        } elseif ($p_send_date) {
            snappay_log('WARN', 'finalize.send_date_not_found', [
                'oid' => (int)$oid,
                'p_send_date' => (string)$p_send_date,
            ]);
        }

        if ($clear_session_cart && isset($_SESSION)) {
            include_once $GLOBALS['bu'] . "modules/cart/cart_funcs.php";
            $_SESSION["cart"] = new cart();
            if (function_exists('cart_draft_clear_for_uid') && isset($_SESSION["logged"]) && isset($_SESSION["logged"]->uid)) {
                cart_draft_clear_for_uid((int)$_SESSION["logged"]->uid);
            }
            if (isset($_SESSION["admin_order_set"])) unset($_SESSION["admin_order_set"]);
        }

        include_once $GLOBALS['bu'] . "modules/wdb/log_funcs.php";
        $uid = (int)getVarFromDB($tb, 'uid', 'id', $oid);
        if (function_exists('cart_draft_clear_for_uid')) {
            cart_draft_clear_for_uid($uid);
        }
        $name = getVarFromDB('users', 'name', 'id', $uid);
        $tel = getVarFromDB('users', 'tel', 'id', $uid);
        if ($tel && function_exists('send_sms_temp')) {
            @send_sms_temp($tel, 'Sabt', $name, $oid);
        }

        snappay_log('INFO', 'finalize.success', [
            'oid' => (int)$oid,
            'transaction_id' => (string)$transaction_id,
            'clear_session_cart' => (bool)$clear_session_cart,
        ]);
        return true;
    }
}
