<?php

require_once __DIR__ . '/snappay_db.php';
require_once __DIR__ . '/snappay_helpers.php';
require_once __DIR__ . '/snappay_client.php';
if (isset($GLOBALS['bu'])) {
    include_once $GLOBALS['bu'] . 'modules/cart/cart_funcs.php';
}

if (!function_exists('snappay_request_message')) {
    function snappay_request_message($is_error, $message)
    {
        $notice_key = 'code_error';
        if (isset($GLOBALS['snappay_notice_key']) && is_string($GLOBALS['snappay_notice_key']) && trim($GLOBALS['snappay_notice_key']) !== '') {
            $notice_key = trim((string)$GLOBALS['snappay_notice_key']);
        }
        $_SESSION[$notice_key] = ($is_error ? 'E' : 'D') . (string)$message;
    }
}

if (!function_exists('snappay_debug_log')) {
    function snappay_debug_log($tag, $context = array())
    {
        $payload = array(
            'tag' => (string)$tag,
            'context' => is_array($context) ? $context : array('value' => $context)
        );
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (!is_string($json) || $json === '') {
            $json = '{"tag":"snappay_debug_log_encode_failed"}';
        }
        error_log('[snappay] ' . $json);
    }
}

if (!function_exists('snappay_is_valid_csrf')) {
    function snappay_is_valid_csrf($session_key, $input)
    {
        $token = (string)($_SESSION[$session_key] ?? '');
        $input = (string)$input;
        return ($token !== '' && $input !== '' && strlen($input) >= 32 && function_exists('hash_equals') && hash_equals($token, $input));
    }
}

if (!function_exists('snappay_tx_is_settled')) {
    function snappay_tx_is_settled($tx)
    {
        if (!is_array($tx)) return false;
        $sn = strtoupper(trim((string)($tx['snappay_status'] ?? '')));
        $final = strtoupper(trim((string)($tx['final_status'] ?? '')));
        return ($sn === 'SETTLE' || $final === 'SETTLE_OK');
    }
}

if (!function_exists('snappay_refresh_tx_status')) {
    function snappay_refresh_tx_status($tx)
    {
        if (!is_array($tx)) return $tx;
        $payment_token = (string)($tx['payment_token'] ?? '');
        if ($payment_token === '') return $tx;

        $status_res = snappay_api_status($payment_token);
        if (!empty($status_res['ok'])) {
            $status = snappay_extract_transaction_status($status_res['json'] ?? null);
            $amount = $status_res['json']['response']['amount'] ?? null;
            if ($status !== null) {
                snappay_tx_set_snappay_status($tx['id'], $status, $amount);
            }
        }

        return snappay_tx_get_by_id($tx['id']) ?: $tx;
    }
}

if (!function_exists('snappay_get_request_columns')) {
    function snappay_get_request_columns()
    {
        if (isset($GLOBALS['snappay_request_columns_cache']) && is_array($GLOBALS['snappay_request_columns_cache'])) {
            return $GLOBALS['snappay_request_columns_cache'];
        }

        $cols = [];
        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
        $res = $mysqli->query("SHOW COLUMNS FROM snappay_requests");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                if (!empty($row['Field'])) {
                    $cols[(string)$row['Field']] = true;
                }
            }
        }
        $GLOBALS['snappay_request_columns_cache'] = $cols;
        return $cols;
    }
}

if (!function_exists('snappay_reset_request_columns_cache')) {
    function snappay_reset_request_columns_cache()
    {
        unset($GLOBALS['snappay_request_columns_cache']);
    }
}

if (!function_exists('snappay_request_has_column')) {
    function snappay_request_has_column($name)
    {
        $cols = snappay_get_request_columns();
        return isset($cols[(string)$name]);
    }
}

if (!function_exists('snappay_request_ensure_column')) {
    function snappay_request_ensure_column($name, $sql_type)
    {
        $name = trim((string)$name);
        $sql_type = trim((string)$sql_type);
        if ($name === '' || $sql_type === '') return false;
        if (snappay_request_has_column($name)) return true;

        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
        $sql = "ALTER TABLE snappay_requests ADD COLUMN `$name` $sql_type NULL";
        if (!$mysqli->query($sql)) {
            return snappay_request_has_column($name);
        }
        snappay_reset_request_columns_cache();
        return snappay_request_has_column($name);
    }
}

if (!function_exists('snappay_request_snapshot_column')) {
    function snappay_request_snapshot_column()
    {
        if (snappay_request_has_column('request_snapshot')) return 'request_snapshot';
        if (snappay_request_has_column('request_payload')) return 'request_payload';
        if (snappay_request_ensure_column('request_snapshot', 'LONGTEXT')) return 'request_snapshot';
        return '';
    }
}

if (!function_exists('snappay_request_type_column')) {
    function snappay_request_type_column()
    {
        if (snappay_request_has_column('request_type')) return 'request_type';
        if (snappay_request_has_column('action_type')) return 'action_type';
        if (snappay_request_ensure_column('request_type', "VARCHAR(32)")) return 'request_type';
        return 'request_type';
    }
}

if (!function_exists('snappay_request_snapshot_encode')) {
    function snappay_request_snapshot_encode($payload)
    {
        if (!is_array($payload)) return '';
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
        return is_string($json) ? $json : '';
    }
}

if (!function_exists('snappay_request_snapshot_decode')) {
    function snappay_request_snapshot_decode($payload)
    {
        if (!is_string($payload) || trim($payload) === '') return null;
        $decoded = json_decode($payload, true);
        return is_array($decoded) ? $decoded : null;
    }
}

if (!function_exists('snappay_request_map_row')) {
    function snappay_request_map_row($row)
    {
        if (!is_array($row)) return null;
        if (!isset($row['request_date']) && isset($row['created_at'])) {
            $row['request_date'] = $row['created_at'];
        }
        if (!isset($row['action_date']) && isset($row['updated_at'])) {
            $row['action_date'] = $row['updated_at'];
        }
        if (!isset($row['admin_note']) && isset($row['note'])) {
            $row['admin_note'] = $row['note'];
        }
        if (!isset($row['request_type']) && isset($row['action_type'])) {
            $row['request_type'] = $row['action_type'];
        }
        $snapshot_col = '';
        if (isset($row['request_snapshot'])) {
            $snapshot_col = 'request_snapshot';
        } elseif (isset($row['request_payload'])) {
            $snapshot_col = 'request_payload';
        }
        if ($snapshot_col !== '') {
            $row['request_snapshot_data'] = snappay_request_snapshot_decode((string)$row[$snapshot_col]);
        } else {
            $row['request_snapshot_data'] = null;
        }
        return $row;
    }
}

if (!function_exists('snappay_get_request_for_order')) {
    function snappay_get_request_for_order($oid, $type = null, $status = null)
    {
        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];

        $oid = (int)$oid;
        if ($oid < 1) return null;

        $conds = ["order_id = ?"];
        $params = [$oid];
        $types = 'i';

        if ($type !== null && $type !== '') {
            $conds[] = snappay_request_type_column() . " = ?";
            $params[] = (string)$type;
            $types .= 's';
        }
        if ($status !== null && $status !== '') {
            $conds[] = "status = ?";
            $params[] = (string)$status;
            $types .= 's';
        }

        $sql = "SELECT * FROM snappay_requests WHERE " . implode(' AND ', $conds) . " ORDER BY id DESC LIMIT 1";
        $st = $mysqli->prepare($sql);
        if (!$st) return null;
        $st->bind_param($types, ...$params);
        if (!$st->execute()) return null;
        $res = $st->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        return snappay_request_map_row($row ?: null);
    }
}

if (!function_exists('snappay_list_requests_for_order')) {
    function snappay_list_requests_for_order($oid, $status = null)
    {
        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];

        $oid = (int)$oid;
        if ($oid < 1) return [];

        $conds = ["order_id = ?"];
        $params = [$oid];
        $types = 'i';

        if ($status !== null && $status !== '') {
            $conds[] = "status = ?";
            $params[] = (string)$status;
            $types .= 's';
        }

        $sql = "SELECT * FROM snappay_requests WHERE " . implode(' AND ', $conds) . " ORDER BY id DESC";
        $st = $mysqli->prepare($sql);
        if (!$st) return [];
        $st->bind_param($types, ...$params);
        if (!$st->execute()) return [];
        $res = $st->get_result();
        $rows = [];
        while ($res && ($row = $res->fetch_assoc())) {
            $rows[] = snappay_request_map_row($row);
        }
        return $rows;
    }
}

if (!function_exists('snappay_get_request_by_id')) {
    function snappay_get_request_by_id($request_id)
    {
        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];

        $request_id = (int)$request_id;
        if ($request_id < 1) return null;

        $sql = "SELECT * FROM snappay_requests WHERE id = ? LIMIT 1";
        $st = $mysqli->prepare($sql);
        if (!$st) return null;
        $st->bind_param('i', $request_id);
        if (!$st->execute()) return null;
        $res = $st->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        return snappay_request_map_row($row ?: null);
    }
}

if (!function_exists('snappay_request_drop_legacy_unique_indexes')) {
    function snappay_request_drop_legacy_unique_indexes()
    {
        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];

        $res = $mysqli->query("SHOW INDEX FROM snappay_requests");
        if (!$res) return false;

        $indexes = [];
        while ($row = $res->fetch_assoc()) {
            $key_name = (string)($row['Key_name'] ?? '');
            if ($key_name === '' || $key_name === 'PRIMARY') continue;

            if (!isset($indexes[$key_name])) {
                $indexes[$key_name] = [
                    'non_unique' => (int)($row['Non_unique'] ?? 1),
                    'columns' => []
                ];
            }
            $indexes[$key_name]['columns'][(int)($row['Seq_in_index'] ?? 0)] = (string)($row['Column_name'] ?? '');
        }

        $dropped = false;
        foreach ($indexes as $key_name => $meta) {
            if ((int)$meta['non_unique'] !== 0) continue;

            ksort($meta['columns']);
            $cols = array_values($meta['columns']);
            $has_order = in_array('order_id', $cols, true);
            $has_type = in_array('request_type', $cols, true) || in_array('action_type', $cols, true);
            if (!$has_order || !$has_type) continue;

            $sql = "ALTER TABLE snappay_requests DROP INDEX `" . $mysqli->real_escape_string($key_name) . "`";
            if ($mysqli->query($sql)) {
                $dropped = true;
            }
        }

        if ($dropped) {
            snappay_reset_request_columns_cache();
        }
        return $dropped;
    }
}

if (!function_exists('snappay_insert_user_request')) {
    function snappay_insert_user_request($oid, $uid, $type, $request_snapshot = null)
    {
        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];

        $cols = ['order_id', 'user_id', snappay_request_type_column(), 'status'];
        $vals = ['?', '?', '?', "'pending'"];
        $types = 'iis';
        $params = [$oid, $uid, $type];

        $snapshot_col = snappay_request_snapshot_column();
        $snapshot_json = snappay_request_snapshot_encode(is_array($request_snapshot) ? $request_snapshot : array());
        if ($snapshot_col !== '' && $snapshot_json !== '') {
            $cols[] = $snapshot_col;
            $vals[] = '?';
            $types .= 's';
            $params[] = $snapshot_json;
        }

        if (snappay_request_has_column('request_date')) {
            $cols[] = 'request_date';
            $vals[] = 'NOW()';
        } elseif (snappay_request_has_column('created_at')) {
            $cols[] = 'created_at';
            $vals[] = 'NOW()';
        }

        $sql = "INSERT INTO snappay_requests (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
        $st = $mysqli->prepare($sql);
        if (!$st) {
            snappay_debug_log('insert_request_prepare_failed', array(
                'oid' => (int)$oid,
                'uid' => (int)$uid,
                'type' => (string)$type,
                'sql' => $sql,
                'db_error' => (string)$mysqli->error
            ));
            return false;
        }
        $st->bind_param($types, ...$params);
        if (!$st->execute()) {
            snappay_debug_log('insert_request_execute_failed_first_try', array(
                'oid' => (int)$oid,
                'uid' => (int)$uid,
                'type' => (string)$type,
                'db_error' => (string)$st->error
            ));
            snappay_request_drop_legacy_unique_indexes();
            $st = $mysqli->prepare($sql);
            if (!$st) {
                snappay_debug_log('insert_request_prepare_failed_after_index_drop', array(
                    'oid' => (int)$oid,
                    'uid' => (int)$uid,
                    'type' => (string)$type,
                    'sql' => $sql,
                    'db_error' => (string)$mysqli->error
                ));
                return false;
            }
            $st->bind_param($types, ...$params);
            if (!$st->execute()) {
                snappay_debug_log('insert_request_execute_failed_second_try', array(
                    'oid' => (int)$oid,
                    'uid' => (int)$uid,
                    'type' => (string)$type,
                    'db_error' => (string)$st->error
                ));
                return false;
            }
        }
        return (int)$mysqli->insert_id;
    }
}

if (!function_exists('snappay_update_request_status')) {
    function snappay_update_request_status($request_id, $status, $admin_note = '')
    {
        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];

        $sets = ['status = ?'];
        $types = 's';
        $params = [(string)$status];

        if (snappay_request_has_column('admin_note')) {
            $sets[] = 'admin_note = ?';
            $types .= 's';
            $params[] = (string)$admin_note;
        } elseif (snappay_request_has_column('note')) {
            $sets[] = 'note = ?';
            $types .= 's';
            $params[] = (string)$admin_note;
        }

        if (snappay_request_has_column('action_date')) {
            $sets[] = 'action_date = NOW()';
        } elseif (snappay_request_has_column('updated_at')) {
            $sets[] = 'updated_at = NOW()';
        }

        $types .= 'i';
        $params[] = (int)$request_id;
        $sql = "UPDATE snappay_requests SET " . implode(', ', $sets) . " WHERE id = ?";
        $st = $mysqli->prepare($sql);
        if (!$st) return false;
        $st->bind_param($types, ...$params);
        return $st->execute();
    }
}

if (!function_exists('snappay_update_request_snapshot')) {
    function snappay_update_request_snapshot($request_id, $snapshot)
    {
        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];

        $snapshot_col = snappay_request_snapshot_column();
        $snapshot_json = snappay_request_snapshot_encode(is_array($snapshot) ? $snapshot : array());
        if ($snapshot_col === '' || $snapshot_json === '') {
            return false;
        }

        $sql = "UPDATE snappay_requests SET $snapshot_col = ? WHERE id = ?";
        $st = $mysqli->prepare($sql);
        if (!$st) return false;
        $request_id = (int)$request_id;
        $st->bind_param('si', $snapshot_json, $request_id);
        return $st->execute();
    }
}

if (!function_exists('snappay_request_snapshot_qty_map')) {
    function snappay_request_snapshot_qty_map($request)
    {
        $snapshot = null;
        if (is_array($request) && isset($request['request_snapshot_data']) && is_array($request['request_snapshot_data'])) {
            $snapshot = $request['request_snapshot_data'];
        } elseif (is_array($request)) {
            $col = isset($request['request_snapshot']) ? 'request_snapshot' : (isset($request['request_payload']) ? 'request_payload' : '');
            if ($col !== '') {
                $snapshot = snappay_request_snapshot_decode((string)$request[$col]);
            }
        }
        $map = array();
        if (!is_array($snapshot) || !isset($snapshot['requested_qty_map']) || !is_array($snapshot['requested_qty_map'])) {
            return $map;
        }
        foreach ($snapshot['requested_qty_map'] as $soid => $qty) {
            $soid = (int)$soid;
            if ($soid > 0) {
                $map[$soid] = max(0, (int)$qty);
            }
        }
        return $map;
    }
}

if (!function_exists('snappay_build_request_snapshot')) {
    function snappay_build_request_snapshot($oid, $type, $apply_items = array(), $financial = array())
    {
        $snapshot = array(
            'type' => (string)$type,
            'requested_qty_map' => array(),
            'changed_items' => array(),
            'financial' => is_array($financial) ? $financial : array()
        );

        if ($type !== 'update' || !is_array($apply_items)) {
            return $snapshot;
        }

        foreach ($apply_items as $soid => $row) {
            if (!is_array($row)) continue;
            $soid = (int)$soid;
            if ($soid < 1) continue;

            $old_qty = isset($row['number']) ? (int)$row['number'] : 0;
            $new_qty = isset($row['new_number']) ? (int)$row['new_number'] : $old_qty;
            if ($new_qty === $old_qty) continue;

            $snapshot['requested_qty_map'][(string)$soid] = $new_qty;
            $snapshot['changed_items'][] = array(
                'soid' => $soid,
                'pid' => isset($row['pid']) ? (int)$row['pid'] : 0,
                'name' => isset($row['name']) ? (string)$row['name'] : (string)getVarFromDB('products', 'name', 'id', (int)($row['pid'] ?? 0)),
                'weight' => isset($row['weight']) ? (string)$row['weight'] : '',
                'old_qty' => $old_qty,
                'new_qty' => $new_qty
            );
        }

        return $snapshot;
    }
}

if (!function_exists('snappay_capture_order_snapshot')) {
    function snappay_capture_order_snapshot($oid, $type = 'cancel')
    {
        $oid = (int)$oid;
        if ($oid < 1) return array(
            'type' => (string)$type,
            'requested_qty_map' => array(),
            'changed_items' => array(),
            'financial' => array()
        );

        $snapshot = array(
            'type' => (string)$type,
            'requested_qty_map' => array(),
            'changed_items' => array(),
            'financial' => array(
                'cart_price' => (int)getVarFromDB('orders', 'cart_price', 'id', $oid),
                'cart_pure' => (int)getVarFromDB('orders', 'cart_pure', 'id', $oid),
                'sale_total' => (int)getVarFromDB('orders', 'sale_total', 'id', $oid),
                'pay_price' => (int)getVarFromDB('orders', 'pay_price', 'id', $oid)
            )
        );

        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
        $st = $mysqli->prepare("SELECT id,pid,weight,number FROM sub_orders WHERE oid = ? AND del_flag = 0 AND (type = 'pack' OR type = 'box')");
        if (!$st) {
            return $snapshot;
        }

        $oid_sql = (string)$oid;
        $st->bind_param('s', $oid_sql);
        if (!$st->execute()) {
            return $snapshot;
        }

        $res = $st->get_result();
        while ($row = $res->fetch_assoc()) {
            $soid = (int)($row['id'] ?? 0);
            if ($soid < 1) continue;

            $qty = (int)($row['number'] ?? 0);
            $pid = (int)($row['pid'] ?? 0);
            $snapshot['requested_qty_map'][(string)$soid] = $qty;
            $snapshot['changed_items'][] = array(
                'soid' => $soid,
                'pid' => $pid,
                'name' => (string)getVarFromDB('products', 'name', 'id', $pid),
                'weight' => isset($row['weight']) ? (string)$row['weight'] : '',
                'old_qty' => $qty,
                'new_qty' => 0
            );
        }

        return $snapshot;
    }
}

if (!function_exists('snappay_capture_current_update_snapshot')) {
    function snappay_capture_current_update_snapshot($oid)
    {
        $oid = (int)$oid;
        if ($oid < 1) {
            return array(
                'type' => 'update',
                'requested_qty_map' => array(),
                'changed_items' => array(),
                'financial' => array()
            );
        }

        $snapshot = array(
            'type' => 'update',
            'requested_qty_map' => array(),
            'changed_items' => array(),
            'financial' => array(
                'cart_price' => (int)getVarFromDB('orders', 'cart_price', 'id', $oid),
                'cart_pure' => (int)getVarFromDB('orders', 'cart_pure', 'id', $oid),
                'sale_total' => (int)getVarFromDB('orders', 'sale_total', 'id', $oid),
                'pay_price' => (int)getVarFromDB('orders', 'pay_price', 'id', $oid)
            ),
            'source' => 'admin_direct'
        );

        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
        $st = $mysqli->prepare("SELECT id,pid,weight,number FROM sub_orders WHERE oid = ? AND del_flag = 0 AND (type = 'pack' OR type = 'box')");
        if (!$st) {
            return $snapshot;
        }

        $oid_sql = (string)$oid;
        $st->bind_param('s', $oid_sql);
        if (!$st->execute()) {
            return $snapshot;
        }

        $res = $st->get_result();
        while ($res && ($row = $res->fetch_assoc())) {
            $soid = (int)($row['id'] ?? 0);
            if ($soid < 1) continue;

            $qty = max(0, (int)($row['number'] ?? 0));
            $pid = (int)($row['pid'] ?? 0);
            $snapshot['requested_qty_map'][(string)$soid] = $qty;
            $snapshot['changed_items'][] = array(
                'soid' => $soid,
                'pid' => $pid,
                'name' => (string)getVarFromDB('products', 'name', 'id', $pid),
                'weight' => isset($row['weight']) ? (string)$row['weight'] : '',
                'old_qty' => $qty,
                'new_qty' => $qty
            );
        }

        return $snapshot;
    }
}

if (!function_exists('snappay_insert_processed_request')) {
    function snappay_insert_processed_request($oid, $type, $request_snapshot = null, $status = 'approved', $admin_note = '')
    {
        $oid = (int)$oid;
        $type = strtolower(trim((string)$type));
        $status = strtolower(trim((string)$status));
        $admin_note = trim((string)$admin_note);

        if ($oid < 1 || ($type !== 'update' && $type !== 'cancel')) {
            return false;
        }
        if ($status !== 'approved' && $status !== 'denied' && $status !== 'pending') {
            $status = 'approved';
        }

        $uid = (int)getVarFromDB('orders', 'uid', 'id', $oid);
        if ($uid < 1) {
            snappay_debug_log('processed_request_missing_order_user', array(
                'oid' => $oid,
                'type' => $type,
                'status' => $status
            ));
            return false;
        }

        if (!is_array($request_snapshot)) {
            $request_snapshot = ($type === 'cancel')
                ? snappay_capture_order_snapshot($oid, 'cancel')
                : snappay_capture_current_update_snapshot($oid);
        }

        $request_snapshot['type'] = $type;
        if (empty($request_snapshot['source'])) {
            $request_snapshot['source'] = 'admin_direct';
        }

        $request_id = snappay_insert_user_request($oid, $uid, $type, $request_snapshot);
        if (!$request_id) {
            snappay_debug_log('processed_request_insert_failed', array(
                'oid' => $oid,
                'uid' => $uid,
                'type' => $type,
                'status' => $status
            ));
            return false;
        }

        if ($status === 'pending') {
            return (int)$request_id;
        }

        if (!snappay_update_request_status((int)$request_id, $status, $admin_note)) {
            snappay_debug_log('processed_request_status_update_failed', array(
                'request_id' => (int)$request_id,
                'oid' => $oid,
                'type' => $type,
                'status' => $status
            ));
            return false;
        }

        return (int)$request_id;
    }
}

if (!function_exists('snappay_get_order_total_qty')) {
    function snappay_get_order_total_qty($oid)
    {
        $oid = (int)$oid;
        if ($oid < 1) return 0;

        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
        $st = $mysqli->prepare("SELECT COALESCE(SUM(number),0) AS total_qty FROM sub_orders WHERE oid = ? AND del_flag = 0 AND (type = 'pack' OR type = 'box')");
        if (!$st) {
            return 0;
        }

        $oid_sql = (string)$oid;
        $st->bind_param('s', $oid_sql);
        if (!$st->execute()) {
            return 0;
        }

        $res = $st->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        return max(0, (int)($row['total_qty'] ?? 0));
    }
}

if (!function_exists('snappay_build_update_payload')) {
    function snappay_build_update_payload($oid, $tx, $new_qty_map, &$financial_out = null, &$apply_items_out = null, &$error_out = null)
    {
        $order_create_date = (string)getVarFromDB('orders', 'create_date', 'id', $oid);
        $old_cart_price = (int)getVarFromDB('orders', 'cart_price', 'id', $oid);
        $old_cart_pure = (int)getVarFromDB('orders', 'cart_pure', 'id', $oid);
        $old_sale_total = (int)getVarFromDB('orders', 'sale_total', 'id', $oid);
        $old_pay_price = (int)getVarFromDB('orders', 'pay_price', 'id', $oid);

        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
        $st = $mysqli->prepare("SELECT id,pid,weight,number,type FROM sub_orders WHERE oid = ? AND del_flag = 0 AND (type = 'pack' OR type = 'box')");
        if (!$st) {
            $error_out = 'failed_to_load_sub_orders';
            return false;
        }
        $oid_sql = (string)$oid;
        $st->bind_param('s', $oid_sql);
        if (!$st->execute()) {
            $error_out = 'failed_to_load_sub_orders';
            return false;
        }
        $res = $st->get_result();
        $current_items = [];
        while ($row = $res->fetch_assoc()) {
            $current_items[(int)$row['id']] = $row;
        }
        if (count($current_items) === 0) {
            $error_out = 'no_items';
            return false;
        }

        $current_total_qty = 0;
        foreach ($current_items as $row) {
            $current_total_qty += max(0, (int)($row['number'] ?? 0));
        }
        if ($current_total_qty <= 1) {
            $error_out = 'single_qty_only_cancel';
            return false;
        }

        $changed = false;
        $next_total_qty = 0;
        $apply_items = [];
        foreach ($current_items as $soid => $row) {
            $current_no = (int)$row['number'];
            $new_no = array_key_exists($soid, $new_qty_map) ? (int)$new_qty_map[$soid] : $current_no;
            if ($new_no < 0) $new_no = 0;
            if ($new_no > $current_no) {
                $error_out = 'qty_increase_not_allowed';
                return false;
            }
            if ($new_no != $current_no) $changed = true;
            $next_total_qty += $new_no;
            $row['new_number'] = $new_no;
            $apply_items[$soid] = $row;
        }

        if (!$changed) {
            $error_out = 'no_change';
            return false;
        }
        if ($next_total_qty < 1) {
            $error_out = 'use_cancel_for_zero';
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

            $row['name'] = (string)getVarFromDB('products', 'name', 'id', $pid);
            $apply_items[$soid] = $row;

            $cart_items[] = [
                'amount' => snappay_amount_to_irr($unit_price),
                'category' => (string)$category,
                'count' => $new_no,
                'id' => $idx,
                'name' => (string)$row['name'],
                'commissionType' => $commissionType
            ];
            $idx++;
        }

        if (count($cart_items) === 0) {
            $error_out = 'use_cancel_for_zero';
            $apply_items_out = $apply_items;
            return false;
        }

        $old_cart_sale = max($old_cart_price - $old_cart_pure, 0);
        $shipping_toman = max($old_pay_price + $old_sale_total - $old_cart_price, 0);
        $discount_ratio = (($old_pay_price + $old_sale_total) > 0) ? ($old_sale_total / ($old_pay_price + $old_sale_total)) : 0;

        $new_cart_sale = (int)floor($new_cart_price * $discount_ratio);
        $new_sale_total = (int)(max($new_cart_price + $shipping_toman, 0) * $discount_ratio);
        $new_total_before_discount = (int)($new_cart_price + $shipping_toman);
        if ($new_sale_total > $new_total_before_discount) {
            $new_sale_total = $new_total_before_discount;
        }
        $new_cart_pure = (int)max($new_cart_price - $new_cart_sale, 0);
        $new_pay_price = (int)max($new_total_before_discount - $new_sale_total, 0);

        if ($new_pay_price <= 0) {
            $error_out = 'use_cancel_for_zero';
            $apply_items_out = $apply_items;
            return false;
        }
        if ($new_pay_price >= $old_pay_price || $new_pay_price >= (int)$tx['amount_toman']) {
            $error_out = 'amount_not_lower';
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
        // Keep totalAmount aligned with the store's existing payable math.
        $cart_total_irr = $cart_items_total_irr + $shipping_irr + $tax_irr;

        $payload = [
            'amount' => snappay_amount_to_irr($new_pay_price),
            'cartList' => [[
                'cartId' => 1,
                'cartItems' => $cart_items,
                'isShipmentIncluded' => $shipment_included,
                'isTaxIncluded' => $tax_included,
                'shippingAmount' => $shipping_irr,
                'taxAmount' => $tax_irr,
                'totalAmount' => $cart_total_irr
            ]],
            'discountAmount' => snappay_amount_to_irr($new_sale_total),
            'externalSourceAmount' => 0,
            'paymentToken' => (string)$tx['payment_token']
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

if (!function_exists('snappay_apply_cancel')) {
    function snappay_apply_cancel($oid, $uid, $tx, $state_before, $stage = 'admin_cancel')
    {
        $direct_request_snapshot = null;
        if ((string)$stage === 'admin_direct_cancel') {
            $direct_request_snapshot = snappay_capture_order_snapshot($oid, 'cancel');
            if (is_array($direct_request_snapshot)) {
                $direct_request_snapshot['source'] = 'admin_direct';
            }
        }

        $res = snappay_api_cancel((string)$tx['payment_token']);
        $err_code = snappay_extract_error_code($res['json'] ?? null);

        if (!$res['ok'] && snappay_is_retryable_error_code($err_code)) {
            usleep(400000);
            $res = snappay_api_cancel((string)$tx['payment_token']);
            $err_code = snappay_extract_error_code($res['json'] ?? null);
        }

        if (!$res['ok']) {
            snappay_error_insert((string)$oid, (string)$tx['id'], $stage, (string)($res['http_status'] ?? 0), $err_code, 'cancel_failed', substr(snappay_mask_raw_response((string)$res['raw']), 0, 800));
            snappay_event_insert($oid, $tx['id'], 'CANCEL', $state_before, $state_before, $uid, 0, $err_code);
            snappay_request_message(true, 'لغو سفارش اسنپ‌پی ناموفق بود.');
            return false;
        }

        $tx = snappay_refresh_tx_status($tx);
        snappay_tx_set_snappay_status($tx['id'], 'CANCEL');
        snappay_tx_set_final_status($tx['id'], 'CANCELLED');

        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
        $ok = true;
        $error = '';
        $mysqli->begin_transaction();
        try {
            $oid_sql = (string)$oid;
            $st_cancel_items = $mysqli->prepare('UPDATE sub_orders SET del_flag = 1 WHERE oid = ?');
            if (!$st_cancel_items) throw new Exception('cancel_sub_orders_prepare');
            $st_cancel_items->bind_param('s', $oid_sql);
            if (!$st_cancel_items->execute()) throw new Exception('cancel_sub_orders_execute');

            $st_zero_totals = $mysqli->prepare('UPDATE orders SET cart_price = 0, cart_pure = 0, sale_total = 0, pay_price = 0 WHERE id = ?');
            if (!$st_zero_totals) throw new Exception('cancel_order_totals_prepare');
            $st_zero_totals->bind_param('s', $oid_sql);
            if (!$st_zero_totals->execute()) throw new Exception('cancel_order_totals_execute');

            $mysqli->commit();
        } catch (Exception $e) {
            $mysqli->rollback();
            $ok = false;
            $error = (string)$e->getMessage();
        }

        if (!$ok) {
            snappay_event_insert($oid, $tx['id'], 'CANCEL', $state_before, $state_before, $uid, 0, $error);
            snappay_request_message(true, 'لغو اسنپ‌پی انجام شد اما ثبت داخلی لغو سفارش ناموفق بود.');
            return false;
        }

        $state_after = $state_before;
        if (defined('SNAPPAY_ORDER_STATE_CANCELLED')) {
            $state_after = (int)SNAPPAY_ORDER_STATE_CANCELLED;
            updateInDB('orders', 'state', $state_after, 'id', $oid);
        }

        snappay_event_insert($oid, $tx['id'], 'CANCEL', $state_before, $state_after, $uid, 1, null);
        if ((string)$stage === 'admin_direct_cancel') {
            snappay_insert_processed_request($oid, 'cancel', $direct_request_snapshot, 'approved', 'Direct admin action without user request.');
        }
        snappay_request_message(false, 'سفارش اسنپ‌پی با موفقیت لغو شد.');
        return true;
    }
}

if (!function_exists('snappay_apply_update')) {
    function snappay_apply_update($oid, $uid, $tx, $state_before, $new_qty_map, $stage = 'admin_update')
    {
        $financial = null;
        $apply_items = null;
        $build_error = null;
        $direct_request_snapshot = null;
        $payload = snappay_build_update_payload($oid, $tx, $new_qty_map, $financial, $apply_items, $build_error);

        if ($payload === false) {
            if ($build_error === 'no_change') {
                snappay_request_message(true, 'تغییری در سفارش ثبت نشده است.');
            } elseif ($build_error === 'qty_increase_not_allowed') {
                snappay_request_message(true, 'افزایش تعداد آیتم‌ها مجاز نیست.');
            } elseif ($build_error === 'single_qty_only_cancel') {
                snappay_request_message(true, 'وقتی مجموع تعداد سفارش ۱ است، فقط لغو سفارش مجاز است.');
            } elseif ($build_error === 'use_cancel_for_zero') {
                snappay_request_message(true, 'برای صفر کردن سفارش باید از دکمه لغو سفارش استفاده کنید.');
            } elseif ($build_error === 'amount_not_lower') {
                snappay_request_message(true, 'مبلغ جدید باید کمتر از مبلغ فعلی سفارش باشد.');
            } else {
                snappay_request_message(true, 'امکان آماده‌سازی درخواست به‌روزرسانی وجود ندارد.');
            }
            return false;
        }

        if ((string)$stage === 'admin_direct_update') {
            $direct_request_snapshot = snappay_build_request_snapshot($oid, 'update', $apply_items, $financial);
            if (is_array($direct_request_snapshot)) {
                $direct_request_snapshot['source'] = 'admin_direct';
            }
        }

        $res = snappay_api_update($payload);
        $err_code = snappay_extract_error_code($res['json'] ?? null);
        if (!$res['ok'] && snappay_is_retryable_error_code($err_code)) {
            usleep(400000);
            $res = snappay_api_update($payload);
            $err_code = snappay_extract_error_code($res['json'] ?? null);
        }

        if (!$res['ok']) {
            snappay_error_insert((string)$oid, (string)$tx['id'], $stage, (string)($res['http_status'] ?? 0), $err_code, 'update_failed', substr(snappay_mask_raw_response((string)$res['raw']), 0, 800));
            snappay_event_insert($oid, $tx['id'], 'UPDATE', $state_before, $state_before, $uid, 0, $err_code);
            snappay_request_message(true, 'به‌روزرسانی سفارش اسنپ‌پی ناموفق بود.');
            return false;
        }

        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
        $mysqli->begin_transaction();
        try {
            foreach ($apply_items as $soid => $row) {
                $new_no = (int)$row['new_number'];
                $item_type = (string)$row['type'];
                $soid_sql = (string)$soid;

                if ($new_no <= 0) {
                    $st = $mysqli->prepare('UPDATE sub_orders SET del_flag = 1 WHERE id = ?');
                    if (!$st) throw new Exception('sub_orders_delete_prepare');
                    $st->bind_param('s', $soid_sql);
                    if (!$st->execute()) throw new Exception('sub_orders_delete_execute');

                    if ($item_type === 'box') {
                        $st2 = $mysqli->prepare('UPDATE sub_orders SET del_flag = 1 WHERE so_id = ?');
                        if (!$st2) throw new Exception('sub_orders_piece_delete_prepare');
                        $st2->bind_param('s', $soid_sql);
                        if (!$st2->execute()) throw new Exception('sub_orders_piece_delete_execute');
                    }
                } else {
                    $st = $mysqli->prepare('UPDATE sub_orders SET number = ?, del_flag = 0 WHERE id = ?');
                    if (!$st) throw new Exception('sub_orders_update_prepare');
                    $new_no_sql = (string)$new_no;
                    $st->bind_param('ss', $new_no_sql, $soid_sql);
                    if (!$st->execute()) throw new Exception('sub_orders_update_execute');
                }
            }

            $st = $mysqli->prepare('UPDATE orders SET cart_price = ?, cart_pure = ?, sale_total = ?, pay_price = ? WHERE id = ?');
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
            snappay_event_insert($oid, $tx['id'], 'UPDATE', $state_before, $state_before, $uid, 0, (string)$e->getMessage());
            snappay_request_message(true, 'به‌روزرسانی سفارش انجام شد اما ثبت تغییرات داخلی ناموفق بود.');
            return false;
        }

        $tx = snappay_refresh_tx_status($tx);
        snappay_event_insert($oid, $tx['id'], 'UPDATE', $state_before, (int)getVarFromDB('orders', 'state', 'id', $oid), $uid, 1, null);
        if ((string)$stage === 'admin_direct_update') {
            snappay_insert_processed_request($oid, 'update', $direct_request_snapshot, 'approved', 'Direct admin action without user request.');
        }
        snappay_request_message(false, 'سفارش اسنپ‌پی با موفقیت به‌روزرسانی شد.');
        return true;
    }
}

if (!function_exists('snappay_handle_user_request')) {
    function snappay_handle_user_request($oid, $type, $csrf, $new_qty_map = array())
    {
        $oid = (int)$oid;
        $type = strtolower(trim((string)$type));

        if (!isset($_SESSION['logged']) || !isset($_SESSION['logged']->uid)) {
            snappay_request_message(true, 'دسترسی مجاز نیست.');
            return false;
        }
        if (!snappay_is_valid_csrf('snappay_user_csrf', $csrf)) {
            snappay_request_message(true, 'درخواست نامعتبر است.');
            return false;
        }
        if ($oid < 1 || ($type !== 'update' && $type !== 'cancel')) {
            snappay_request_message(true, 'عملیات نامعتبر است.');
            return false;
        }

        $uid = (int)$_SESSION['logged']->uid;
        if ((int)getVarFromDB('orders', 'uid', 'id', $oid) !== $uid) {
            snappay_request_message(true, 'سفارش معتبر نیست.');
            return false;
        }

        $order_state = (int)getVarFromDB('orders', 'state', 'id', $oid);
        if ($order_state < 0 || $order_state > 2) {
            snappay_request_message(true, 'امکان تغییر سفارش در وضعیت فعلی وجود ندارد.');
            return false;
        }

        $tx = snappay_tx_get_latest_for_order($oid);
        if (!$tx || (string)($tx['payment_token'] ?? '') === '') {
            snappay_request_message(true, 'تراکنش اسنپ‌پی برای این سفارش یافت نشد.');
            return false;
        }
        $tx = snappay_refresh_tx_status($tx);
        if (!snappay_tx_is_settled($tx)) {
            snappay_request_message(true, 'تراکنش هنوز در وضعیت تسویه نیست.');
            return false;
        }

        $pending_any = snappay_get_request_for_order($oid, null, 'pending');
        if ($pending_any) {
            snappay_request_message(false, 'درخواست قبلی شما هنوز در انتظار بررسی است و تا زمان پاسخ مدیر امکان ثبت درخواست جدید وجود ندارد.');
            return false;
        }

        $request_snapshot = array('type' => $type);
        if ($type === 'update') {
            $financial = null;
            $apply_items = null;
            $build_error = null;
            $payload = snappay_build_update_payload($oid, $tx, is_array($new_qty_map) ? $new_qty_map : array(), $financial, $apply_items, $build_error);
            if ($payload === false) {
                snappay_debug_log('user_update_payload_build_failed', array(
                    'oid' => (int)$oid,
                    'uid' => (int)$uid,
                    'build_error' => (string)$build_error,
                    'new_qty_map' => is_array($new_qty_map) ? $new_qty_map : array(),
                    'tx_amount_toman' => isset($tx['amount_toman']) ? (int)$tx['amount_toman'] : null
                ));
                if ($build_error === 'no_change') {
                    snappay_request_message(true, 'تغییری در سفارش ثبت نشده است.');
                } elseif ($build_error === 'qty_increase_not_allowed') {
                    snappay_request_message(true, 'افزایش تعداد آیتم‌ها مجاز نیست.');
                } elseif ($build_error === 'single_qty_only_cancel') {
                    snappay_request_message(true, 'وقتی مجموع تعداد سفارش ۱ است، فقط لغو سفارش مجاز است.');
                } elseif ($build_error === 'amount_not_lower') {
                    snappay_request_message(true, 'مبلغ جدید باید کمتر از مبلغ فعلی سفارش باشد.');
                } elseif ($build_error === 'use_cancel_for_zero') {
                    snappay_request_message(true, 'برای صفر کردن کل سفارش باید از دکمه لغو سفارش استفاده کنید.');
                } else {
                    snappay_request_message(true, 'امکان آماده‌سازی درخواست به‌روزرسانی وجود ندارد.');
                }
                return false;
            }
            $request_snapshot = snappay_build_request_snapshot($oid, $type, $apply_items, $financial);
        } elseif ($type === 'cancel') {
            $request_snapshot = snappay_capture_order_snapshot($oid, $type);
        }

        $request_id = snappay_insert_user_request($oid, $uid, $type, $request_snapshot);
        if (!$request_id) {
            snappay_debug_log('user_request_insert_failed', array(
                'oid' => (int)$oid,
                'uid' => (int)$uid,
                'type' => (string)$type,
                'has_pending_after_fail' => snappay_get_request_for_order($oid, null, 'pending') ? 1 : 0
            ));
            $pending = snappay_get_request_for_order($oid, null, 'pending');
            if ($pending) {
                snappay_request_message(false, 'درخواست قبلی شما هنوز در انتظار بررسی است و تا زمان پاسخ مدیر امکان ثبت درخواست جدید وجود ندارد.');
            } else {
                snappay_request_message(true, 'ثبت درخواست ناموفق بود.');
            }
            return false;
        }

        snappay_request_message(false, 'درخواست شما با موفقیت ثبت شد و پس از بررسی مدیر پاسخ داده می‌شود.');
        return true;
    }
}

if (!function_exists('snappay_handle_admin_request')) {
    function snappay_handle_admin_request($oid, $type, $action, $csrf, $admin_note = '', $new_qty_map = [], $request_id = 0)
    {
        $oid = (int)$oid;
        $type = strtolower(trim((string)$type));
        $action = strtolower(trim((string)$action));
        $admin_note = trim((string)$admin_note);
        $request_id = (int)$request_id;

        if (!snappay_is_valid_csrf('snappay_csrf', $csrf)) {
            snappay_request_message(true, 'درخواست نامعتبر است.');
            return false;
        }
        if ($oid < 1 || ($type !== 'update' && $type !== 'cancel') || ($action !== 'approve' && $action !== 'deny')) {
            snappay_request_message(true, 'عملیات نامعتبر است.');
            return false;
        }

        if ($request_id > 0) {
            $request = snappay_get_request_by_id($request_id);
            if (
                !$request ||
                (int)($request['order_id'] ?? 0) !== $oid ||
                strtolower(trim((string)($request['request_type'] ?? ''))) !== $type ||
                strtolower(trim((string)($request['status'] ?? ''))) !== 'pending'
            ) {
                snappay_request_message(true, 'درخواست در انتظار برای این سفارش یافت نشد.');
                return false;
            }
        } else {
            $request = snappay_get_request_for_order($oid, $type, 'pending');
        }
        if (!$request) {
            snappay_request_message(true, 'درخواست در انتظار برای این سفارش یافت نشد.');
            return false;
        }

        $order_state = (int)getVarFromDB('orders', 'state', 'id', $oid);
        if ($order_state < 0 || $order_state > 2) {
            snappay_request_message(true, 'امکان اعمال درخواست در وضعیت فعلی سفارش وجود ندارد.');
            return false;
        }

        $tx = snappay_tx_get_latest_for_order($oid);
        if (!$tx || (string)($tx['payment_token'] ?? '') === '') {
            snappay_request_message(true, 'تراکنش اسنپ‌پی برای این سفارش یافت نشد.');
            return false;
        }
        $tx = snappay_refresh_tx_status($tx);
        if (!snappay_tx_is_settled($tx)) {
            snappay_request_message(true, 'تراکنش اسنپ‌پی در وضعیت قابل تغییر نیست.');
            return false;
        }

        if ($action === 'deny') {
            if (!snappay_update_request_status((int)$request['id'], 'denied', $admin_note)) {
                snappay_request_message(true, 'رد درخواست ناموفق بود.');
                return false;
            }
            snappay_request_message(false, 'درخواست کاربر رد شد.');
            return true;
        }

        if ($type === 'update') {
            $requested_qty_map = snappay_request_snapshot_qty_map($request);
            if (count($requested_qty_map) === 0) {
                snappay_request_message(true, 'جزئیات درخواست به‌روزرسانی ناقص است و امکان اعمال آن وجود ندارد.');
                return false;
            }
            $ok = snappay_apply_update($oid, (int)$request['user_id'], $tx, $order_state, $requested_qty_map, 'admin_update');
        } else {
            $snapshot_data = isset($request['request_snapshot_data']) && is_array($request['request_snapshot_data']) ? $request['request_snapshot_data'] : null;
            if (!is_array($snapshot_data) || empty($snapshot_data['changed_items']) || empty($snapshot_data['financial'])) {
                $snapshot_data = snappay_capture_order_snapshot($oid, $type);
                if (is_array($snapshot_data)) {
                    snappay_update_request_snapshot((int)$request['id'], $snapshot_data);
                }
            }
            $ok = snappay_apply_cancel($oid, (int)$request['user_id'], $tx, $order_state, 'admin_cancel');
        }

        if (!$ok) {
            return false;
        }

        if (!snappay_update_request_status((int)$request['id'], 'approved', $admin_note)) {
            snappay_request_message(true, 'عملیات انجام شد اما ثبت وضعیت درخواست ناموفق بود.');
            return false;
        }

        return true;
    }
}
