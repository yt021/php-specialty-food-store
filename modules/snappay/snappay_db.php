<?php

require_once __DIR__ . '/snappay_config.php';
require_once __DIR__ . '/snappay_helpers.php';

// Ensure DB helpers are available (defines $dbc_adrs, getVarFromDB, updateInDB, ...)
if(isset($GLOBALS['bu'])){
    include_once $GLOBALS['bu'] . "modules/wdb/db_funcs.php";
}

function snappay_db_now()
{
    return date("Y-m-d H:i:s");
}

function snappay_tx_get_latest_for_order($oid)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $st = "SELECT * FROM snappay_transactions WHERE oid = ? ORDER BY id DESC LIMIT 1";
    $st = $mysqli->prepare($st);
    if(!$st) return null;
    $st->bind_param('s', $oid);
    if (!$st->execute()) return null;
    $res = $st->get_result();
    return $res->fetch_assoc() ?: null;
}

function snappay_tx_oids_set($oids)
{
    if (!is_array($oids) || count($oids) === 0) return [];

    $oids = array_values(array_unique(array_map(function ($v) {
        return (int)$v;
    }, $oids)));
    $oids = array_values(array_filter($oids, function ($v) {
        return $v > 0;
    }));
    if (count($oids) === 0) return [];

    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $in = implode(',', $oids);
    $st = "SELECT DISTINCT oid FROM snappay_transactions WHERE oid IN ($in)";
    $st = $mysqli->prepare($st);
    if (!$st) return [];
    if (!$st->execute()) return [];
    $res = $st->get_result();

    $set = [];
    while ($row = $res->fetch_assoc()) {
        $set[(int)$row['oid']] = true;
    }
    return $set;
}

function snappay_tx_get_by_transaction_id($transaction_id)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $st = "SELECT * FROM snappay_transactions WHERE transaction_id = ? LIMIT 1";
    $st = $mysqli->prepare($st);
    if(!$st) return null;
    $st->bind_param('s', $transaction_id);
    if (!$st->execute()) return null;
    $res = $st->get_result();
    return $res->fetch_assoc() ?: null;
}

function snappay_tx_list_pending_for_reconcile($lookback_minutes = 180, $limit = 200)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $lookback_minutes = (int)$lookback_minutes;
    $limit = (int)$limit;
    if ($lookback_minutes < 1) $lookback_minutes = 180;
    if ($limit < 1) $limit = 200;
    if ($limit > 1000) $limit = 1000;

    $since = date("Y-m-d H:i:s", time() - ($lookback_minutes * 60));
    $st = "SELECT * FROM snappay_transactions
           WHERE final_status = 'PENDING'
             AND payment_token IS NOT NULL
             AND payment_token <> ''
             AND created_at >= ?
           ORDER BY id ASC
           LIMIT ?";
    $st = $mysqli->prepare($st);
    if (!$st) return [];
    $st->bind_param('si', $since, $limit);
    if (!$st->execute()) return [];

    $res = $st->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function snappay_tx_get_by_id($id)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $st = "SELECT * FROM snappay_transactions WHERE id = ? LIMIT 1";
    $st = $mysqli->prepare($st);
    if(!$st) return null;
    $st->bind_param('s', $id);
    if (!$st->execute()) return null;
    $res = $st->get_result();
    return $res->fetch_assoc() ?: null;
}

function snappay_tx_next_attempt_no($oid)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $st = "SELECT MAX(attempt_no) FROM snappay_transactions WHERE oid = ?";
    $st = $mysqli->prepare($st);
    if(!$st) return 1;
    $st->bind_param('s', $oid);
    if (!$st->execute()) return 1;
    $st->store_result();
    $st->bind_result($max);
    $st->fetch();
    $max = (int)$max;
    return max($max + 1, 1);
}

function snappay_tx_insert($oid, $attempt_no, $transaction_id, $amount_toman, $amount_irr)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $st = "INSERT INTO snappay_transactions (oid,attempt_no,transaction_id,amount_toman,amount_irr,final_status,created_at,updated_at) VALUES (?,?,?,?,?,'PENDING',NOW(),NOW())";
    $st = $mysqli->prepare($st);
    if(!$st) return false;
    $st->bind_param('sssss', $oid, $attempt_no, $transaction_id, $amount_toman, $amount_irr);
    if (!$st->execute()) return false;
    return $mysqli->insert_id;
}

function snappay_tx_set_payment_token($id, $payment_token)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $st = "UPDATE snappay_transactions SET payment_token = ?, updated_at = NOW() WHERE id = ?";
    $st = $mysqli->prepare($st);
    if(!$st) return false;
    $st->bind_param('ss', $payment_token, $id);
    return (bool)$st->execute();
}

function snappay_tx_record_callback($id, $callback_state, $callback_amount_irr)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $st = "UPDATE snappay_transactions SET callback_state = ?, callback_amount_irr = ?, callback_received_at = NOW(), updated_at = NOW() WHERE id = ?";
    $st = $mysqli->prepare($st);
    if(!$st) return false;
    $st->bind_param('sss', $callback_state, $callback_amount_irr, $id);
    return (bool)$st->execute();
}

function snappay_tx_set_snappay_status($id, $status, $amount_irr = null)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    if ($amount_irr === null) {
        $st = "UPDATE snappay_transactions SET snappay_status = ?, updated_at = NOW() WHERE id = ?";
        $st = $mysqli->prepare($st);
        if(!$st) return false;
        $st->bind_param('ss', $status, $id);
        return (bool)$st->execute();
    }
    $st = "UPDATE snappay_transactions SET snappay_status = ?, status_amount_irr = ?, updated_at = NOW() WHERE id = ?";
    $st = $mysqli->prepare($st);
    if(!$st) return false;
    $st->bind_param('sss', $status, $amount_irr, $id);
    return (bool)$st->execute();
}

function snappay_tx_claim_verify($id)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $ttl = defined('SNAPPAY_VERIFY_CLAIM_TTL_SECONDS') ? (int)SNAPPAY_VERIFY_CLAIM_TTL_SECONDS : 120;
    if ($ttl < 1) $ttl = 120;
    $stale_before = date("Y-m-d H:i:s", time() - $ttl);
    $st = "UPDATE snappay_transactions
           SET verify_attempts = verify_attempts + 1, verify_status = 'IN_PROGRESS', updated_at = NOW()
           WHERE id = ?
             AND (
               verify_attempts = 0
               OR verify_status IN ('TIMEOUT','FAILED')
               OR (verify_status = 'IN_PROGRESS' AND updated_at <= ?)
             )";
    $st = $mysqli->prepare($st);
    if(!$st) return false;
    $st->bind_param('ss', $id, $stale_before);
    if (!$st->execute()) return false;
    return ($st->affected_rows === 1);
}

function snappay_tx_set_verify_result($id, $verify_status)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $st = "UPDATE snappay_transactions SET verify_status = ?, updated_at = NOW() WHERE id = ?";
    $st = $mysqli->prepare($st);
    if(!$st) return false;
    $st->bind_param('ss', $verify_status, $id);
    return (bool)$st->execute();
}

function snappay_tx_claim_settle($id)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $ttl = defined('SNAPPAY_SETTLE_CLAIM_TTL_SECONDS') ? (int)SNAPPAY_SETTLE_CLAIM_TTL_SECONDS : 120;
    if ($ttl < 1) $ttl = 120;
    $stale_before = date("Y-m-d H:i:s", time() - $ttl);
    $st = "UPDATE snappay_transactions
           SET settle_attempts = settle_attempts + 1, settle_status = 'IN_PROGRESS', updated_at = NOW()
           WHERE id = ?
             AND (
               settle_attempts = 0
               OR settle_status = 'RETRY'
               OR (settle_status = 'IN_PROGRESS' AND updated_at <= ?)
             )";
    $st = $mysqli->prepare($st);
    if(!$st) return false;
    $st->bind_param('ss', $id, $stale_before);
    if (!$st->execute()) return false;
    return ($st->affected_rows === 1);
}

function snappay_tx_set_settle_result($id, $settle_status)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $st = "UPDATE snappay_transactions SET settle_status = ?, updated_at = NOW() WHERE id = ?";
    $st = $mysqli->prepare($st);
    if(!$st) return false;
    $st->bind_param('ss', $settle_status, $id);
    return (bool)$st->execute();
}

function snappay_tx_set_final_status($id, $final_status)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $st = "UPDATE snappay_transactions SET final_status = ?, updated_at = NOW() WHERE id = ?";
    $st = $mysqli->prepare($st);
    if(!$st) return false;
    $st->bind_param('ss', $final_status, $id);
    return (bool)$st->execute();
}

function snappay_event_insert($oid, $tx_id, $action_type, $state_before, $state_after, $actor_uid, $success = 1, $error_code = null)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $st = "INSERT INTO snappay_order_events
           (oid, tx_id, action_type, state_before, state_after, actor_uid, success, error_code, created_at)
           VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $st = $mysqli->prepare($st);
    if (!$st) return false;

    $oid = (string)$oid;
    $tx_id = ($tx_id === null) ? null : (string)$tx_id;
    $action_type = strtoupper(trim((string)$action_type));
    $state_before = ($state_before === null) ? null : (string)$state_before;
    $state_after = ($state_after === null) ? null : (string)$state_after;
    $actor_uid = ($actor_uid === null) ? null : (string)$actor_uid;
    $success = (string)((int)$success ? 1 : 0);
    $error_code = ($error_code === null || $error_code === '') ? null : (string)$error_code;

    $st->bind_param('ssssssss', $oid, $tx_id, $action_type, $state_before, $state_after, $actor_uid, $success, $error_code);
    return (bool)$st->execute();
}

function snappay_event_count_for_order_state($order_state)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $st = "SELECT COUNT(DISTINCT e.oid) AS c
           FROM snappay_order_events e
           INNER JOIN orders o ON o.id = e.oid
           WHERE o.del_flag = 0
             AND o.state = ?
             AND e.success = 1
             AND e.action_type IN ('UPDATE','CANCEL')";
    $st = $mysqli->prepare($st);
    if (!$st) return 0;
    $order_state = (string)((int)$order_state);
    $st->bind_param('s', $order_state);
    if (!$st->execute()) return 0;
    $res = $st->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    return (int)($row['c'] ?? 0);
}

function snappay_event_oids_set($oids)
{
    if (!is_array($oids) || count($oids) === 0) return [];

    $oids = array_values(array_unique(array_map(function ($v) {
        return (int)$v;
    }, $oids)));
    $oids = array_values(array_filter($oids, function ($v) {
        return $v > 0;
    }));
    if (count($oids) === 0) return [];

    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $in = implode(',', $oids);
    $st = "SELECT DISTINCT oid
           FROM snappay_order_events
           WHERE success = 1
             AND action_type IN ('UPDATE','CANCEL')
             AND oid IN ($in)";
    $st = $mysqli->prepare($st);
    if (!$st) return [];
    if (!$st->execute()) return [];
    $res = $st->get_result();

    $set = [];
    while ($row = $res->fetch_assoc()) {
        $set[(int)$row['oid']] = true;
    }
    return $set;
}

function snappay_error_insert($oid, $snappay_tx_id, $stage, $http_status, $error_code, $message, $raw_response_masked)
{
    include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
    if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
        $parts = explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR']);
        $ipAddress = trim((string)($parts[0] ?? $ipAddress));
    }
    $browser = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $st = "INSERT INTO snappay_errors (oid,snappay_tx_id,stage,http_status,error_code,message,raw_response_masked,ip,browser,created_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())";
    $st = $mysqli->prepare($st);
    if(!$st) return false;
    $st->bind_param('sssssssss', $oid, $snappay_tx_id, $stage, $http_status, $error_code, $message, $raw_response_masked, $ipAddress, $browser);
    return (bool)$st->execute();
}
