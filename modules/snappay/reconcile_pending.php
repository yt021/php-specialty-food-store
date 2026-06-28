<?php
// Suggested cron (every 5 minutes):
// */5 * * * * /usr/bin/php /path/to/repository/modules/snappay/reconcile_pending.php >/dev/null 2>&1

if (!isset($_SERVER['DOCUMENT_ROOT']) || !is_string($_SERVER['DOCUMENT_ROOT']) || $_SERVER['DOCUMENT_ROOT'] === '') {
    $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . '/../../');
}

if (!isset($bu)) {
    include_once $_SERVER['DOCUMENT_ROOT'] . "/base_shop.php";
}

require_once($bu . "modules/snappay/snappay_client.php");
require_once($bu . "modules/snappay/snappay_db.php");
require_once($bu . "modules/snappay/snappay_helpers.php");
require_once($bu . "modules/snappay/snappay_finalize.php");

if (!function_exists('snappay_reconcile_mark_order_cancelled')) {
    function snappay_reconcile_mark_order_cancelled($oid)
    {
        if (defined('SNAPPAY_ORDER_STATE_CANCELLED') && SNAPPAY_ORDER_STATE_CANCELLED !== null) {
            updateInDB("orders", "state", (int)SNAPPAY_ORDER_STATE_CANCELLED, "id", (int)$oid);
            return true;
        }
        snappay_log('WARN', 'reconcile.cancel_state_missing', [
            'oid' => (int)$oid,
        ]);
        return false;
    }
}

if (!function_exists('snappay_reconcile_status_once')) {
    function snappay_reconcile_status_once($tx)
    {
        $payment_token = (string)($tx['payment_token'] ?? '');
        if ($payment_token === '') {
            return [null, null, ['ok' => false, 'error' => 'missing_token']];
        }

        $status_res = snappay_api_status($payment_token);
        $err_code = snappay_extract_error_code($status_res['json'] ?? null);

        if (!$status_res['ok'] && snappay_is_retryable_error_code($err_code)) {
            usleep(400000);
            $status_res = snappay_api_status($payment_token);
            $err_code = snappay_extract_error_code($status_res['json'] ?? null);
        }

        if (!$status_res['ok'] && $err_code === '1011') {
            usleep(250000);
            $status_res = snappay_api_status($payment_token);
            $err_code = snappay_extract_error_code($status_res['json'] ?? null);
        }

        $status = snappay_extract_transaction_status($status_res['json'] ?? null);
        return [$status, $err_code, $status_res];
    }
}

if (!function_exists('snappay_run_reconcile_pending')) {
    function snappay_run_reconcile_pending($run_ctx = 'cron', $options = [])
    {
        $summary = [
            'ok' => true,
            'ctx' => (string)$run_ctx,
            'checked' => 0,
            'finalized' => 0,
            'pending' => 0,
            'cancelled' => 0,
            'failed' => 0,
            'errors' => 0,
            'started_at' => date('c'),
            'finished_at' => null,
        ];

        if (!defined('SNAPPAY_RECONCILE_ENABLED') || !SNAPPAY_RECONCILE_ENABLED) {
            $summary['ok'] = false;
            $summary['reason'] = 'disabled';
            $summary['finished_at'] = date('c');
            return $summary;
        }

        if (!snappay_backend_enabled()) {
            $summary['ok'] = false;
            $summary['reason'] = 'backend_disabled';
            $summary['finished_at'] = date('c');
            return $summary;
        }

        $lookback = defined('SNAPPAY_RECONCILE_LOOKBACK_MINUTES') ? (int)SNAPPAY_RECONCILE_LOOKBACK_MINUTES : 180;
        $limit = defined('SNAPPAY_RECONCILE_BATCH_SIZE') ? (int)SNAPPAY_RECONCILE_BATCH_SIZE : 200;
        if (is_array($options)) {
            if (isset($options['lookback_minutes'])) {
                $lookback = (int)$options['lookback_minutes'];
            }
            if (isset($options['batch_size'])) {
                $limit = (int)$options['batch_size'];
            }
        }
        $txs = snappay_tx_list_pending_for_reconcile($lookback, $limit);

        foreach ($txs as $tx) {
            $summary['checked']++;
            $tx_id = (string)$tx['id'];
            $oid = (int)$tx['oid'];
            $token = (string)$tx['payment_token'];

            [$status, $status_err_code, $status_res] = snappay_reconcile_status_once($tx);
            if (!$status_res['ok']) {
                $summary['errors']++;
                snappay_error_insert(
                    (string)$oid,
                    $tx_id,
                    'reconcile_status',
                    (string)($status_res['http_status'] ?? 0),
                    $status_err_code,
                    'status_failed',
                    substr(snappay_mask_raw_response((string)($status_res['raw'] ?? '')), 0, 800)
                );
                continue;
            }

            $amount = $status_res['json']['response']['amount'] ?? null;
            if ($status !== null) {
                snappay_tx_set_snappay_status($tx_id, $status, $amount);
            }

            if ($status === 'SETTLE') {
                snappay_tx_set_settle_result($tx_id, 'SUCCESS');
                snappay_tx_set_final_status($tx_id, 'SETTLE_OK');
                if (snappay_finalize_paid_order_once($oid, (string)$tx['transaction_id'], false)) {
                    $summary['finalized']++;
                }
                continue;
            }

            if ($status === 'VERIFY') {
                $claimed = snappay_tx_claim_settle($tx_id);
                if (!$claimed) {
                    $latest = snappay_tx_get_by_id($tx_id);
                    if ($latest && ((string)($latest['final_status'] ?? '') === 'SETTLE_OK' || (string)($latest['settle_status'] ?? '') === 'SUCCESS')) {
                        $summary['finalized']++;
                    } else {
                        $summary['pending']++;
                    }
                    continue;
                }

                $settle_res = snappay_api_settle($token, 30);
                $settle_err_code = snappay_extract_error_code($settle_res['json'] ?? null);
                if (!$settle_res['ok'] && snappay_is_retryable_error_code($settle_err_code)) {
                    usleep(400000);
                    $settle_res = snappay_api_settle($token, 30);
                    $settle_err_code = snappay_extract_error_code($settle_res['json'] ?? null);
                }

                if ($settle_res['ok']) {
                    snappay_tx_set_settle_result($tx_id, 'SUCCESS');
                    snappay_tx_set_final_status($tx_id, 'SETTLE_OK');
                    if (snappay_finalize_paid_order_once($oid, (string)$tx['transaction_id'], false)) {
                        $summary['finalized']++;
                    }
                    continue;
                }

                snappay_error_insert(
                    (string)$oid,
                    $tx_id,
                    'reconcile_settle',
                    (string)($settle_res['http_status'] ?? 0),
                    $settle_err_code,
                    'settle_failed',
                    substr(snappay_mask_raw_response((string)$settle_res['raw']), 0, 800)
                );

                [$status2, $status2_err_code, $status2_res] = snappay_reconcile_status_once($tx);
                if ($status2_res['ok']) {
                    $amount2 = $status2_res['json']['response']['amount'] ?? null;
                    if ($status2 !== null) snappay_tx_set_snappay_status($tx_id, $status2, $amount2);
                    if ($status2 === 'SETTLE') {
                        snappay_tx_set_settle_result($tx_id, 'SUCCESS');
                        snappay_tx_set_final_status($tx_id, 'SETTLE_OK');
                        if (snappay_finalize_paid_order_once($oid, (string)$tx['transaction_id'], false)) {
                            $summary['finalized']++;
                        }
                        continue;
                    }
                    if ($status2 === 'CANCEL') {
                        snappay_tx_set_final_status($tx_id, 'CANCELLED');
                        snappay_reconcile_mark_order_cancelled($oid);
                        $summary['cancelled']++;
                        continue;
                    }
                    if ($status2 === 'REVERT') {
                        snappay_tx_set_final_status($tx_id, 'FAILED');
                        $summary['failed']++;
                        continue;
                    }
                } else {
                    snappay_error_insert(
                        (string)$oid,
                        $tx_id,
                        'reconcile_settle_status',
                        (string)($status2_res['http_status'] ?? 0),
                        $status2_err_code,
                        'status_failed',
                        substr(snappay_mask_raw_response((string)($status2_res['raw'] ?? '')), 0, 800)
                    );
                }

                snappay_tx_set_settle_result($tx_id, 'RETRY');
                $summary['pending']++;
                continue;
            }

            if ($status === 'PENDING' || $status === null) {
                $summary['pending']++;
                continue;
            }

            if ($status === 'CANCEL') {
                snappay_tx_set_final_status($tx_id, 'CANCELLED');
                snappay_reconcile_mark_order_cancelled($oid);
                $summary['cancelled']++;
                continue;
            }

            if ($status === 'REVERT') {
                snappay_tx_set_final_status($tx_id, 'FAILED');
                $summary['failed']++;
                continue;
            }

            $summary['pending']++;
        }

        $summary['finished_at'] = date('c');
        snappay_log('INFO', 'reconcile.summary', $summary);
        return $summary;
    }
}

$is_direct = realpath(__FILE__) === realpath($_SERVER['SCRIPT_FILENAME'] ?? '');
if ($is_direct) {
    if (php_sapi_name() !== 'cli') {
        include_once $bu . "modules/cart/session_start.php";
        if (!isset($_SESSION["a_logged"]) || !is_object($_SESSION["a_logged"]) || !method_exists($_SESSION["a_logged"], 'get_level') || $_SESSION["a_logged"]->get_level() < 2) {
            http_response_code(403);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['ok' => false, 'reason' => 'forbidden'], JSON_UNESCAPED_UNICODE);
            exit;
        }
    }
    $summary = snappay_run_reconcile_pending('direct');
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($summary, JSON_UNESCAPED_UNICODE);
}
