<?php

require_once __DIR__ . '/snappay_config.php';
require_once __DIR__ . '/snappay_helpers.php';

function snappay_log_level_rank($level)
{
    $level = strtoupper((string)$level);
    if ($level === 'DEBUG') return 10;
    if ($level === 'INFO') return 20;
    if ($level === 'WARN' || $level === 'WARNING') return 30;
    if ($level === 'ERROR') return 40;
    return 20;
}

function snappay_log_is_enabled()
{
    return (defined('SNAPPAY_LOG_ENABLED') && SNAPPAY_LOG_ENABLED);
}

function snappay_log_min_level_rank()
{
    $lvl = defined('SNAPPAY_LOG_LEVEL') ? SNAPPAY_LOG_LEVEL : 'INFO';
    return snappay_log_level_rank($lvl);
}

function snappay_log_path()
{
    if (defined('SNAPPAY_LOG_FILE') && is_string(SNAPPAY_LOG_FILE) && SNAPPAY_LOG_FILE !== '') {
        return (string)SNAPPAY_LOG_FILE;
    }
    // Default: repository-root/logs/snappay.json
    $base = realpath(__DIR__ . '/../../');
    if (!is_string($base) || $base === '') $base = __DIR__ . '/../../';
    return rtrim($base, "\\/") . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'snappay.json';
}

function snappay_log_correlation_id()
{
    static $cid = null;
    if (is_string($cid) && $cid !== '') return $cid;
    if (function_exists('random_bytes')) {
        $cid = bin2hex(random_bytes(8));
    } else {
        $cid = (string)uniqid('sp_', true);
    }
    return $cid;
}

function snappay_log_truncate($s, $maxLen)
{
    $s = (string)$s;
    $maxLen = (int)$maxLen;
    if ($maxLen <= 0) return '';
    if (strlen($s) <= $maxLen) return $s;
    return substr($s, 0, $maxLen) . '...[truncated]';
}

function snappay_log_payload_meta($payload, $previewLen = 280)
{
    if (!is_string($payload)) {
        $payload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if (!is_string($payload)) $payload = '';
    }
    $masked = snappay_mask_raw_response($payload);
    return [
        'len' => strlen($payload),
        'preview' => snappay_log_truncate($masked, (int)$previewLen),
    ];
}

function snappay_log_mask_context($ctx)
{
    if (!is_array($ctx)) return [];

    $mask = function ($value, $key = '') use (&$mask) {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $k => $v) {
                $out[$k] = $mask($v, is_string($k) ? $k : $key);
            }
            return $out;
        }

        if (is_object($value)) {
            return '[object]';
        }

        $keyL = strtolower((string)$key);
        if (strpos($keyL, 'password') !== false) return '****';
        if (strpos($keyL, 'client_secret') !== false) return '****';
        if ($keyL === 'authorization') return '****';
        if ($keyL === 'access_token') return '****';
        if ($keyL === 'paymenttoken' || $keyL === 'payment_token') return snappay_mask_token((string)$value);
        if ($keyL === 'mobile' || $keyL === 'tel') return snappay_mask_mobile((string)$value);
        if (strpos($keyL, 'url') !== false) {
            return snappay_log_truncate(snappay_mask_url_tokens((string)$value), 1000);
        }
        if ($keyL === 'raw' || $keyL === 'raw_response' || $keyL === 'raw_response_masked') {
            return snappay_log_payload_meta($value, 320);
        }
        if ($keyL === 'body' || $keyL === 'request_body') {
            return snappay_log_payload_meta($value, 240);
        }

        if (is_string($value)) return snappay_log_truncate($value, 1000);
        return $value;
    };

    return $mask($ctx, '');
}

function snappay_log_write_line($line)
{
    $path = snappay_log_path();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $written = @file_put_contents($path, $line . "\n", FILE_APPEND | LOCK_EX);
    if ($written === false) {
        error_log('[snappay] log write failed: ' . $path);
        return false;
    }
    return true;
}

function snappay_log($level, $event, $ctx = [])
{
    if (!snappay_log_is_enabled()) return;

    $rank = snappay_log_level_rank($level);
    if ($rank < snappay_log_min_level_rank()) return;

    $ip = snappay_get_request_ip(defined('SNAPPAY_CALLBACK_IP_USE_XFF') ? (bool)SNAPPAY_CALLBACK_IP_USE_XFF : false);
    $uri = snappay_mask_url_tokens((string)($_SERVER['REQUEST_URI'] ?? ''));
    $method = (string)($_SERVER['REQUEST_METHOD'] ?? '');

    $entry = [
        'ts' => date('c'),
        'level' => strtoupper((string)$level),
        'event' => (string)$event,
        'cid' => snappay_log_correlation_id(),
        'ip' => $ip,
        'http_method' => $method,
        'uri' => $uri,
        'ctx' => snappay_log_mask_context(is_array($ctx) ? $ctx : []),
    ];

    $line = json_encode($entry, JSON_UNESCAPED_UNICODE);
    if (!is_string($line)) return;
    snappay_log_write_line($line);
}
