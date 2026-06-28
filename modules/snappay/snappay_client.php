<?php

require_once __DIR__ . '/snappay_config.php';
require_once __DIR__ . '/snappay_runtime.php';
require_once __DIR__ . '/snappay_helpers.php';
require_once __DIR__ . '/snappay_logger.php';

    function snappay_api_body_meta($json_body)
    {
        if ($json_body === null) {
            return ['present' => false];
        }
        if (!is_array($json_body)) {
            return ['present' => true, 'type' => gettype($json_body)];
        }
        return [
            'present' => true,
            'keys' => array_keys($json_body),
        ];
    }

    function snappay_get_cached_token()
    {
        if (!isset($_SESSION)) return null;
        if (!isset($_SESSION['snappay_oauth']) || !is_array($_SESSION['snappay_oauth'])) return null;
        $t = $_SESSION['snappay_oauth'];
        if (!isset($t['access_token'], $t['expires_at'])) return null;
        if ((int)$t['expires_at'] <= time() + 60) return null;
        return (string)$t['access_token'];
    }

    function snappay_set_cached_token($token, $expires_in)
    {
        if (!isset($_SESSION)) return;
        $expires_in = (int)$expires_in;
        if ($expires_in <= 0) $expires_in = 3600;
        $_SESSION['snappay_oauth'] = [
            'access_token' => (string)$token,
            'expires_at' => time() + $expires_in,
        ];
    }

    function snappay_oauth_token()
    {
        if (!function_exists('curl_init')) {
            snappay_log('ERROR', 'oauth.unavailable_curl', [
                'error' => 'cURL extension is not available',
            ]);
            return [false, null, "cURL extension is not available"];
        }

        $cached = snappay_get_cached_token();
        if ($cached) {
            snappay_log('INFO', 'oauth.cache_hit', []);
            return [true, $cached, null];
        }

        $url = rtrim(SNAPPAY_BASE_URL, '/') . '/api/online/v1/oauth/token';
        $auth = base64_encode(SNAPPAY_CLIENT_ID . ':' . SNAPPAY_CLIENT_SECRET);
        $headers = [
            'Authorization: Basic ' . $auth,
            'Content-Type: application/x-www-form-urlencoded',
        ];
        $body = http_build_query([
            'grant_type' => 'password',
            'scope' => SNAPPAY_SCOPE,
            'username' => SNAPPAY_USERNAME,
            'password' => SNAPPAY_PASSWORD,
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $err = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($raw === false) {
            snappay_log('ERROR', 'oauth.curl_error', [
                'curl_errno' => $errno,
                'curl_error' => $err,
            ]);
            return [false, null, "OAuth curl error ($errno): $err"];
        }
        $json = json_decode($raw, true);
        if ($code < 200 || $code >= 300 || !is_array($json) || !isset($json['access_token'])) {
            snappay_log('ERROR', 'oauth.failed', [
                'http_status' => $code,
                'raw_response' => $raw,
            ]);
            return [false, null, "OAuth failed (HTTP $code)"];
        }

        snappay_set_cached_token($json['access_token'], $json['expires_in'] ?? 3600);
        snappay_log('INFO', 'oauth.success', [
            'http_status' => $code,
            'expires_in' => (int)($json['expires_in'] ?? 3600),
        ]);
        return [true, (string)$json['access_token'], null];
    }

    function snappay_api_request($method, $path, $query = [], $json_body = null, $timeout_seconds = 20)
    {
        if (!snappay_backend_enabled()) {
            snappay_log('WARN', 'api.disabled_by_admin', [
                'method' => (string)$method,
                'path' => (string)$path,
            ]);
            return [
                'ok' => false,
                'http_status' => 0,
                'timeout' => false,
                'json' => null,
                'raw' => null,
                'error' => 'SnappPay backend is disabled by admin',
            ];
        }

        if (!function_exists('curl_init')) {
            snappay_log('ERROR', 'api.unavailable_curl', [
                'method' => (string)$method,
                'path' => (string)$path,
                'error' => 'cURL extension is not available',
            ]);
            return [
                'ok' => false,
                'http_status' => 0,
                'timeout' => false,
                'json' => null,
                'raw' => null,
                'error' => "cURL extension is not available",
            ];
        }

        $method = strtoupper((string)$method);
        $base = rtrim(SNAPPAY_BASE_URL, '/');
        $url = $base . $path;
        if (is_array($query) && count($query) > 0) {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($query);
        }

        snappay_log('INFO', 'api.request', [
            'method' => $method,
            'path' => $path,
            'query' => is_array($query) ? $query : [],
            'request_body_meta' => snappay_api_body_meta($json_body),
            'timeout_seconds' => (int)$timeout_seconds,
        ]);

        [$ok, $token, $oauth_err] = snappay_oauth_token();
        if (!$ok) {
            snappay_log('ERROR', 'api.oauth_failed', [
                'method' => $method,
                'path' => $path,
                'error' => (string)$oauth_err,
            ]);
            return [
                'ok' => false,
                'http_status' => 0,
                'timeout' => false,
                'json' => null,
                'raw' => null,
                'error' => $oauth_err,
            ];
        }

        $headers = [
            'Authorization: Bearer ' . $token,
            'Accept: application/json',
        ];
        $body = null;
        if ($json_body !== null) {
            $headers[] = 'Content-Type: application/json';
            $body = json_encode($json_body, JSON_UNESCAPED_UNICODE);
        }

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => (int)$timeout_seconds,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);
        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $raw = curl_exec($ch);
        $errno = curl_errno($ch);
        $err = curl_error($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $timeout = ($errno === CURLE_OPERATION_TIMEDOUT);
        if ($raw === false) {
            snappay_log('ERROR', 'api.curl_error', [
                'method' => $method,
                'path' => $path,
                'http_status' => $code,
                'timeout' => $timeout,
                'curl_errno' => $errno,
                'curl_error' => $err,
            ]);
            return [
                'ok' => false,
                'http_status' => $code,
                'timeout' => $timeout,
                'json' => null,
                'raw' => null,
                'error' => "curl error ($errno): $err",
            ];
        }

        $json = json_decode($raw, true);
        $successful = (is_array($json) && array_key_exists('successful', $json)) ? (bool)$json['successful'] : null;
        $ok = ($code >= 200 && $code < 300 && $successful === true);

        snappay_log($ok ? 'INFO' : 'WARN', 'api.response', [
            'method' => $method,
            'path' => $path,
            'http_status' => $code,
            'successful' => $successful,
            'ok' => $ok,
            'raw_response_meta' => [
                'len' => strlen((string)$raw),
                'preview' => substr(snappay_mask_raw_response((string)$raw), 0, 320),
            ],
        ]);

        return [
            'ok' => $ok,
            'http_status' => $code,
            'timeout' => false,
            'json' => $json,
            'raw' => $raw,
            'error' => $ok ? null : ("HTTP $code"),
        ];
    }

function snappay_api_eligible($amount_irr, $payment_method_types = null)
{
    $query = ['amount' => (int)$amount_irr];
    if ($payment_method_types === null && defined('SNAPPAY_ELIGIBLE_PAYMENT_METHOD_TYPES')) {
        $payment_method_types = SNAPPAY_ELIGIBLE_PAYMENT_METHOD_TYPES;
    }
    $payment_method_types = snappay_normalize_payment_method_types($payment_method_types);
    if (count($payment_method_types) > 0) {
        $query['paymentMethodTypes'] = implode(',', $payment_method_types);
    }
    return snappay_api_request('GET', '/api/online/offer/v1/eligible', $query, null, 15);
}

    function snappay_api_token($payload)
    {
        return snappay_api_request('POST', '/api/online/payment/v1/token', [], $payload, 25);
    }

    function snappay_api_verify($payment_token, $timeout_seconds = 30)
    {
        return snappay_api_request('POST', '/api/online/payment/v1/verify', [], ['paymentToken' => $payment_token], $timeout_seconds);
    }

    function snappay_api_settle($payment_token, $timeout_seconds = 30)
    {
        return snappay_api_request('POST', '/api/online/payment/v1/settle', [], ['paymentToken' => $payment_token], $timeout_seconds);
    }

    function snappay_api_status($payment_token)
    {
        return snappay_api_request('GET', '/api/online/payment/v1/status', ['paymentToken' => $payment_token], null, 15);
    }

    function snappay_api_cancel($payment_token)
    {
        return snappay_api_request('POST', '/api/online/payment/v1/cancel', [], ['paymentToken' => $payment_token], 25);
    }

    function snappay_api_update($payload)
    {
        return snappay_api_request('POST', '/api/online/payment/v1/update', [], $payload, 25);
    }

    function snappay_api_revert($payment_token)
    {
        return snappay_api_request('POST', '/api/online/payment/v1/revert', [], ['paymentToken' => $payment_token], 25);
    }
