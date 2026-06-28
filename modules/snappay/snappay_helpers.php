<?php

function snappay_mask_token($token)
{
    if (!is_string($token) || $token === '') return '';
    $len = strlen($token);
    if ($len <= 12) return str_repeat('*', $len);
    return substr($token, 0, 6) . str_repeat('*', max($len - 10, 1)) . substr($token, -4);
}

function snappay_mask_mobile($mobile)
{
    if (!is_string($mobile) || $mobile === '') return '';
    $digits = preg_replace('/[^0-9]/', '', $mobile);
    if (strlen($digits) <= 4) return '****';
    return '****' . substr($digits, -4);
}

function snappay_tel_to_e164($tel)
{
    if (!is_string($tel)) return '';
    $tel = trim($tel);
    $digits = preg_replace('/[^0-9]/', '', $tel);
    // Common Iranian format: 09xxxxxxxxx (11 digits)
    if (strlen($digits) === 11 && substr($digits, 0, 2) === '09') {
        return '+98' . substr($digits, 1);
    }
    // Already +98... but stripped -> 98xxxxxxxxxx
    if (strlen($digits) === 12 && substr($digits, 0, 2) === '98') {
        return '+' . $digits;
    }
    return $tel;
}

function snappay_get_request_ip($use_xff = false)
{
    $ipAddress = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    if ($use_xff && array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
        $parts = explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR']);
        $candidate = trim((string)($parts[0] ?? ''));
        if ($candidate !== '') $ipAddress = $candidate;
    }
    return $ipAddress;
}

function snappay_ipv4_to_long($ip)
{
    $long = ip2long($ip);
    if ($long === false) return null;
    // Normalize to unsigned int in PHP
    return sprintf('%u', $long);
}

function snappay_ipv4_in_cidr($ip, $cidr)
{
    if (!is_string($ip) || !is_string($cidr) || $ip === '' || $cidr === '') return false;

    if (strpos($cidr, '/') === false) {
        return trim($ip) === trim($cidr);
    }

    [$subnet, $maskBits] = explode('/', $cidr, 2);
    $subnet = trim($subnet);
    $maskBits = (int)trim($maskBits);
    if ($maskBits < 0 || $maskBits > 32) return false;

    $ipLong = snappay_ipv4_to_long($ip);
    $subnetLong = snappay_ipv4_to_long($subnet);
    if ($ipLong === null || $subnetLong === null) return false;

    $mask = $maskBits === 0 ? 0 : (0xFFFFFFFF << (32 - $maskBits)) & 0xFFFFFFFF;
    // Convert to unsigned-safe integers
    $maskU = sprintf('%u', $mask);
    return (((int)$ipLong & (int)$maskU) === ((int)$subnetLong & (int)$maskU));
}

function snappay_ip_in_allowlist($ip, $allowlist)
{
    if (!is_string($ip) || $ip === '') return false;
    if (!is_array($allowlist) || count($allowlist) === 0) return true; // enforcement disabled

    $ip = trim($ip);
    foreach ($allowlist as $entry) {
        if (!is_string($entry)) continue;
        $entry = trim($entry);
        if ($entry === '') continue;
        // v1: support exact IP and IPv4 CIDR blocks
        if (snappay_ipv4_in_cidr($ip, $entry)) return true;
    }
    return false;
}

function snappay_amount_to_irr($amount_toman)
{
    $amount_toman = (int)$amount_toman;
    $mult = (int)SNAPPAY_AMOUNT_MULTIPLIER;
    if ($mult <= 0) $mult = 10;
    return $amount_toman * $mult;
}

function snappay_normalize_payment_method_types($types)
{
    if (!is_array($types)) return [];

    $out = [];
    foreach ($types as $method) {
        $method = strtoupper(trim((string)$method));
        if ($method === '') continue;
        $out[$method] = true;
    }

    return array_keys($out);
}

function snappay_extract_error_code($json)
{
    if (!is_array($json)) return null;
    if (isset($json['errorData']) && is_array($json['errorData']) && isset($json['errorData']['errorCode'])) {
        $code = (string)$json['errorData']['errorCode'];
        return ($code !== '') ? $code : null;
    }
    return null;
}

function snappay_extract_transaction_status($json)
{
    if (!is_array($json)) return null;

    if (isset($json['response']) && is_array($json['response']) && isset($json['response']['status'])) {
        $st = strtoupper(trim((string)$json['response']['status']));
        return ($st !== '') ? $st : null;
    }

    if (isset($json['errorData']) && is_array($json['errorData'])) {
        $data = $json['errorData']['data'] ?? null;
        if (is_array($data)) {
            $candidates = ['transaction-status', 'transactionStatus', 'status', 'transaction_status'];
            foreach ($candidates as $k) {
                if (isset($data[$k])) {
                    $st = strtoupper(trim((string)$data[$k]));
                    if ($st !== '') return $st;
                }
            }
        } elseif (is_string($data) && $data !== '') {
            if (preg_match('/(transaction[-_ ]?status|status)\s*[:=]\s*([A-Za-z_]+)/i', $data, $m)) {
                $st = strtoupper(trim((string)$m[2]));
                if ($st !== '') return $st;
            }
        }
    }

    return null;
}

function snappay_is_retryable_error_code($error_code)
{
    $code = (string)$error_code;
    return in_array($code, ['1000', '1053'], true);
}

function snappay_is_immediate_fail_error_code($error_code)
{
    $code = (string)$error_code;
    return in_array($code, ['1005', '1007', '1036', '1042', '1048', '1051', '1078'], true);
}

function snappay_is_valid_transaction_id($transaction_id)
{
    if (!is_string($transaction_id)) return false;
    return (bool)preg_match('/^o[0-9]+a[0-9]+$/', trim($transaction_id));
}

function snappay_user_message_for_error_code($error_code, $fallback = "تراکنش ناموفق شد.")
{
    $code = (string)$error_code;
    if ($code === '1009') return "شماره تراکنش تکراری است. لطفا دوباره تلاش کنید.";
    if ($code === '1011') return "وضعیت تراکنش در حال انتقال است. لطفا چند لحظه دیگر دوباره بررسی کنید.";
    if ($code === '1042') return "تراکنش منقضی شده است. لطفا دوباره پرداخت را انجام دهید.";
    if ($code === '1078') return "تراکنش با خطای غیرقابل بازیابی مواجه شد. لطفا دوباره تلاش کنید.";
    if ($code === '1053' || $code === '1000') return "اختلال موقت در ارتباط با درگاه رخ داده است. لطفا دوباره تلاش کنید.";
    return $fallback;
}

function snappay_mask_url_tokens($url)
{
    if (!is_string($url) || $url === '') return '';

    $parts = @parse_url($url);
    if (!is_array($parts) || !isset($parts['query'])) {
        return $url;
    }

    $query = [];
    parse_str((string)$parts['query'], $query);
    if (!is_array($query) || count($query) === 0) {
        return $url;
    }

    $changed = false;
    foreach ($query as $k => $v) {
        $lk = strtolower((string)$k);
        if ($lk === 'paymenttoken' || $lk === 'payment_token' || $lk === 'access_token' || $lk === 'token') {
            $query[$k] = snappay_mask_token((string)$v);
            $changed = true;
        }
    }
    if (!$changed) return $url;

    $rebuilt = '';
    if (isset($parts['scheme'])) $rebuilt .= $parts['scheme'] . '://';
    if (isset($parts['user'])) {
        $rebuilt .= $parts['user'];
        if (isset($parts['pass'])) $rebuilt .= ':' . $parts['pass'];
        $rebuilt .= '@';
    }
    if (isset($parts['host'])) $rebuilt .= $parts['host'];
    if (isset($parts['port'])) $rebuilt .= ':' . $parts['port'];
    if (isset($parts['path'])) $rebuilt .= $parts['path'];
    $rebuilt .= '?' . http_build_query($query);
    if (isset($parts['fragment'])) $rebuilt .= '#' . $parts['fragment'];
    return $rebuilt;
}

function snappay_mask_raw_response($raw)
{
    if (!is_string($raw) || $raw === '') return '';

    $masked = $raw;
    $masked = preg_replace_callback('/(\"paymentToken\"\\s*:\\s*\")([^\"]+)(\")/u', function ($m) {
        return $m[1] . snappay_mask_token($m[2]) . $m[3];
    }, $masked);
    $masked = preg_replace_callback('/(\"access_token\"\\s*:\\s*\")([^\"]+)(\")/u', function ($m) {
        return $m[1] . '****' . $m[3];
    }, $masked);
    $masked = preg_replace_callback('/(\"mobile\"\\s*:\\s*\")([^\"]+)(\")/u', function ($m) {
        return $m[1] . snappay_mask_mobile($m[2]) . $m[3];
    }, $masked);

    $json = json_decode($raw, true);
    if (!is_array($json)) return $masked;

    $mask_keys = function (&$val) use (&$mask_keys) {
        if (is_array($val)) {
            foreach ($val as $k => &$v) {
                if (is_string($k)) {
                    $lk = strtolower($k);
                    if ($lk === 'paymenttoken') {
                        $v = snappay_mask_token((string)$v);
                        continue;
                    }
                    if ($lk === 'access_token') {
                        $v = '****';
                        continue;
                    }
                    if ($lk === 'mobile') {
                        $v = snappay_mask_mobile((string)$v);
                        continue;
                    }
                    if (strpos($lk, 'url') !== false) {
                        $v = snappay_mask_url_tokens((string)$v);
                        continue;
                    }
                }
                $mask_keys($v);
            }
        }
    };
    $mask_keys($json);

    $encoded = json_encode($json, JSON_UNESCAPED_UNICODE);
    return is_string($encoded) ? $encoded : $masked;
}

function snappay_compute_shipping_toman($order_row)
{
    $cart_price = (int)$order_row['cart_price'];
    $sale_total = (int)$order_row['sale_total'];
    $pay_price = (int)$order_row['pay_price'];
    $shipping = $pay_price + $sale_total - $cart_price;
    return max($shipping, 0);
}

function snappay_build_cart_payload_from_order($oid, $transaction_id, $mobile_e164)
{
    include_once $GLOBALS['bu'] . "modules/wdb/db_funcs.php";
    include_once $GLOBALS['bu'] . "modules/cart/cart_funcs.php";

    $tb = "orders";
    if (!var_exist($oid, $tb, "id")) {
        return [false, "Order not found"];
    }

    $order_row = [
        'id' => $oid,
        'create_date' => getVarFromDB($tb, 'create_date', 'id', $oid),
        'cart_price' => (int)getVarFromDB($tb, 'cart_price', 'id', $oid),
        'sale_total' => (int)getVarFromDB($tb, 'sale_total', 'id', $oid),
        'pay_price' => (int)getVarFromDB($tb, 'pay_price', 'id', $oid),
    ];

    $shipping_toman = snappay_compute_shipping_toman($order_row);

    $cart_read = new cart_read($oid);
    if ($cart_read->orders === false) {
        return [false, "Failed to load order items"];
    }

    $cart_items = [];
    $idx = 1;
    foreach ($cart_read->orders as $item) {
        if (!isset($item->pid) || !isset($item->no) || !isset($item->price) || !isset($item->name)) continue;
        $pid = (int)$item->pid;
        $count = (int)$item->no;
        if ($count < 1) continue;
        $unit_price_toman = (int)$item->price;
        $category = getVarFromDB('products', 'category', 'id', $pid);
        if (!$category) $category = 'default';

        $commissionType = (int)SNAPPAY_COMMISSION_TYPE_DEFAULT;
        $map = SNAPPAY_COMMISSION_TYPE_MAP;
        if (is_array($map) && $category && array_key_exists($category, $map)) {
            $commissionType = (int)$map[$category];
        }

        $cart_items[] = [
            "amount" => snappay_amount_to_irr($unit_price_toman),
            "category" => (string)$category,
            "count" => $count,
            "id" => $idx,
            "name" => (string)$item->name,
            "commissionType" => $commissionType,
        ];
        $idx++;
    }

    if (count($cart_items) === 0) {
        return [false, "Cart is empty"];
    }

    $sale_total_toman = (int)$order_row['sale_total'];
    $pay_price_toman = (int)$order_row['pay_price'];
    $shipping_irr = snappay_amount_to_irr($shipping_toman);
    $tax_irr = (int)SNAPPAY_TAX_AMOUNT_FIXED_IRR;

    $cart_items_total_irr = 0;
    foreach ($cart_items as $ci) {
        $item_amount = (int)($ci['amount'] ?? 0);
        $item_count = (int)($ci['count'] ?? 0);
        $cart_items_total_irr += ($item_amount * $item_count);
    }

    $shipment_included = (bool)SNAPPAY_IS_SHIPMENT_INCLUDED;
    $tax_included = (bool)SNAPPAY_IS_TAX_INCLUDED;
    // Keep totalAmount aligned with the store's existing payable math.
    $cart_total_irr = $cart_items_total_irr + $shipping_irr + $tax_irr;

    $payload = [
        "amount" => snappay_amount_to_irr($pay_price_toman),
        "cartList" => [
            [
                "cartId" => 1,
                "cartItems" => $cart_items,
                "isShipmentIncluded" => $shipment_included,
                "isTaxIncluded" => $tax_included,
                "shippingAmount" => $shipping_irr,
                "taxAmount" => $tax_irr,
                "totalAmount" => $cart_total_irr,
            ],
        ],
        "discountAmount" => snappay_amount_to_irr($sale_total_toman),
        "externalSourceAmount" => 0,
        "mobile" => (string)$mobile_e164,
        "returnURL" => (string)SNAPPAY_RETURN_URL,
        "transactionId" => (string)$transaction_id,
    ];

    $forced = defined('SNAPPAY_FORCED_PAYMENT_METHOD_TYPES') ? SNAPPAY_FORCED_PAYMENT_METHOD_TYPES : [];
    $forced = snappay_normalize_payment_method_types($forced);
    if (count($forced) > 0) {
        $payload["forcedPaymentMethodTypes"] = $forced;
    }

    return [true, $payload];
}
