<?php

if (isset($indexed)) {
    if ($indexed == 1) {

if (!function_exists('snappay_admin_update_config_flags')) {
    function snappay_admin_update_config_flags($config_path, $checkout_enabled, $backend_enabled, &$error_msg = '')
    {
        $checkout_lit = $checkout_enabled ? 'true' : 'false';
        $backend_lit = $backend_enabled ? 'true' : 'false';

        if (!is_file($config_path)) {
            $error_msg = "فایل تنظیمات اسنپ‌پی یافت نشد.";
            return false;
        }

        $content = file_get_contents($config_path);
        if ($content === false) {
            $error_msg = "خواندن فایل تنظیمات اسنپ‌پی ناموفق بود.";
            return false;
        }

        $checkout_count = 0;
        $backend_count = 0;

        $content = preg_replace(
            "/define\\(\\s*'SNAPPAY_CHECKOUT_ENABLED'\\s*,\\s*(?:true|false)\\s*\\);/i",
            "define('SNAPPAY_CHECKOUT_ENABLED', " . $checkout_lit . ");",
            $content,
            1,
            $checkout_count
        );
        $content = preg_replace(
            "/define\\(\\s*'SNAPPAY_BACKEND_ENABLED'\\s*,\\s*(?:true|false)\\s*\\);/i",
            "define('SNAPPAY_BACKEND_ENABLED', " . $backend_lit . ");",
            $content,
            1,
            $backend_count
        );
        $content = preg_replace(
            "/define\\(\\s*'SNAPPAY_ENABLED'\\s*,\\s*(?:true|false|SNAPPAY_CHECKOUT_ENABLED)\\s*\\);/i",
            "define('SNAPPAY_ENABLED', SNAPPAY_CHECKOUT_ENABLED);",
            $content,
            1
        );

        if ($checkout_count !== 1 || $backend_count !== 1) {
            $error_msg = "الگوی فلگ‌های اسنپ‌پی در فایل تنظیمات پیدا نشد.";
            return false;
        }

        if (file_put_contents($config_path, $content, LOCK_EX) === false) {
            $error_msg = "نوشتن فایل تنظیمات اسنپ‌پی ناموفق بود.";
            return false;
        }

        return true;
    }
}

if (!function_exists('snappay_admin_normalize_method_types')) {
    function snappay_admin_normalize_method_types($types)
    {
        $allowed = ['INSTALLMENT', 'POSTPAID', 'FINANCING'];
        if (!is_array($types)) return [];
        $out = [];
        foreach ($types as $t) {
            $u = strtoupper(trim((string)$t));
            if (in_array($u, $allowed, true) && !in_array($u, $out, true)) {
                $out[] = $u;
            }
        }
        return $out;
    }
}

if (!function_exists('snappay_admin_update_method_policy')) {
    function snappay_admin_update_method_policy($config_path, $eligible_types, $forced_types, &$error_msg = '')
    {
        if (!is_file($config_path)) {
            $error_msg = "فایل تنظیمات اسنپ‌پی یافت نشد.";
            return false;
        }

        $all_methods = ['INSTALLMENT', 'POSTPAID', 'FINANCING'];
        $raw_normalize = function ($types) {
            if (!is_array($types)) return [];
            $out = [];
            foreach ($types as $t) {
                $u = strtoupper(trim((string)$t));
                if ($u === '') continue;
                $out[$u] = true;
            }
            return array_keys($out);
        };
        $has_all_methods = function ($types) use ($all_methods) {
            foreach ($all_methods as $method) {
                if (!in_array($method, $types, true)) {
                    return false;
                }
            }
            return true;
        };

        $eligible_raw = $raw_normalize($eligible_types);
        $forced_raw = $raw_normalize($forced_types);
        // If all method chips are selected, store [] in config (means "no filter" / all methods).
        $eligible = $has_all_methods($eligible_raw) ? [] : snappay_admin_normalize_method_types($eligible_raw);
        $forced = $has_all_methods($forced_raw) ? [] : snappay_admin_normalize_method_types($forced_raw);

        $to_array_literal = function ($arr) {
            if (!is_array($arr) || count($arr) === 0) return '[]';
            $parts = [];
            foreach ($arr as $v) {
                $parts[] = "'" . str_replace("'", "\\'", (string)$v) . "'";
            }
            return '[' . implode(',', $parts) . ']';
        };

        $eligible_lit = $to_array_literal($eligible);
        $forced_lit = $to_array_literal($forced);

        $content = file_get_contents($config_path);
        if ($content === false) {
            $error_msg = "خواندن فایل تنظیمات اسنپ‌پی ناموفق بود.";
            return false;
        }

        $eligible_count = 0;
        $forced_count = 0;

        $content = preg_replace(
            "/define\\(\\s*'SNAPPAY_ELIGIBLE_PAYMENT_METHOD_TYPES'\\s*,\\s*.*?\\);/m",
            "define('SNAPPAY_ELIGIBLE_PAYMENT_METHOD_TYPES', " . $eligible_lit . ");",
            $content,
            1,
            $eligible_count
        );
        $content = preg_replace(
            "/define\\(\\s*'SNAPPAY_FORCED_PAYMENT_METHOD_TYPES'\\s*,\\s*.*?\\);/m",
            "define('SNAPPAY_FORCED_PAYMENT_METHOD_TYPES', " . $forced_lit . ");",
            $content,
            1,
            $forced_count
        );

        if ($eligible_count !== 1 || $forced_count !== 1) {
            $error_msg = "الگوی سیاست روش‌های پرداخت اسنپ‌پی در فایل تنظیمات پیدا نشد.";
            return false;
        }

        if (file_put_contents($config_path, $content, LOCK_EX) === false) {
            $error_msg = "ذخیره سیاست روش‌های پرداخت اسنپ‌پی ناموفق بود.";
            return false;
        }

        return true;
    }
}

if (!function_exists('snappay_admin_health_metrics')) {
    function snappay_admin_health_metrics()
    {
        $m = [
            'pending_count' => null,
            'pending_oldest' => null,
            'last_success_at' => null,
            'last_error_code' => null,
            'last_error_at' => null,
        ];

        if (!isset($GLOBALS['bu'], $GLOBALS['dbc_adrs'])) return $m;
        include $GLOBALS['bu'] . $GLOBALS['dbc_adrs'];
        if (!isset($mysqli) || !is_object($mysqli)) return $m;

        $st = $mysqli->prepare("SELECT COUNT(*) AS c, MIN(created_at) AS oldest FROM snappay_transactions WHERE final_status = 'PENDING'");
        if ($st && $st->execute()) {
            $res = $st->get_result();
            if ($res && ($row = $res->fetch_assoc())) {
                $m['pending_count'] = isset($row['c']) ? (int)$row['c'] : null;
                $m['pending_oldest'] = !empty($row['oldest']) ? (string)$row['oldest'] : null;
            }
        }

        $st = $mysqli->prepare("SELECT MAX(updated_at) AS t FROM snappay_transactions WHERE final_status = 'SETTLE_OK'");
        if ($st && $st->execute()) {
            $res = $st->get_result();
            if ($res && ($row = $res->fetch_assoc())) {
                $m['last_success_at'] = !empty($row['t']) ? (string)$row['t'] : null;
            }
        }

        $st = $mysqli->prepare("SELECT error_code, created_at FROM snappay_errors ORDER BY id DESC LIMIT 1");
        if ($st && $st->execute()) {
            $res = $st->get_result();
            if ($res && ($row = $res->fetch_assoc())) {
                $m['last_error_code'] = isset($row['error_code']) ? (string)$row['error_code'] : null;
                $m['last_error_at'] = !empty($row['created_at']) ? (string)$row['created_at'] : null;
            }
        }

        return $m;
    }
}

if (
    isset($_SESSION["a_logged"]) &&
    is_object($_SESSION["a_logged"]) &&
    method_exists($_SESSION["a_logged"], 'get_level') &&
    $_SESSION["a_logged"]->get_level() == 2 &&
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['snappay_toggle_action'])
) {
    if (
        !isset($_POST['snappay_toggle_csrf']) ||
        !isset($_SESSION['snappay_toggle_csrf']) ||
        !is_string($_POST['snappay_toggle_csrf']) ||
        !is_string($_SESSION['snappay_toggle_csrf']) ||
        !hash_equals($_SESSION['snappay_toggle_csrf'], $_POST['snappay_toggle_csrf'])
    ) {
        $_SESSION["code_error"] = "Eدرخواست نامعتبر (CSRF).";
    } else {
        $snappay_config_path = $bu . "modules/snappay/snappay_config.php";
        if (!is_file($snappay_config_path)) {
            $_SESSION["code_error"] = "Eفایل تنظیمات اسنپ‌پی یافت نشد.";
        } else {
            require_once $bu . "modules/snappay/snappay_config.php";
            require_once $bu . "modules/snappay/snappay_runtime.php";
            require_once $bu . "modules/cart/cart_funcs.php";
            require_once $bu . "modules/snappay/product_feed.php";

            $current_checkout = snappay_checkout_enabled();
            $current_backend = snappay_backend_enabled();
            $new_checkout = $current_checkout;
            $new_backend = $current_backend;
            $action = (string)$_POST['snappay_toggle_action'];
            $action_error = '';
            $update_err = '';

            if ($action === 'toggle_checkout') {
                $new_checkout = !$current_checkout;
                if ($new_checkout && !$current_backend) {
                    $action_error = "برای فعال‌کردن نمایش درگاه ابتدا بک‌اند را فعال کنید.";
                } elseif (snappay_admin_update_config_flags($snappay_config_path, $new_checkout, $new_backend, $update_err)) {
                    $_SESSION["code_error"] = "Dتنظیمات اسنپ‌پی با موفقیت به‌روزرسانی شد.";
                } else {
                    $_SESSION["code_error"] = "E" . $update_err;
                }
            } elseif ($action === 'toggle_backend') {
                $new_backend = !$current_backend;
                if (!$new_backend) {
                    $new_checkout = false;
                }
                if (snappay_admin_update_config_flags($snappay_config_path, $new_checkout, $new_backend, $update_err)) {
                    $_SESSION["code_error"] = "Dتنظیمات اسنپ‌پی با موفقیت به‌روزرسانی شد.";
                } else {
                    $_SESSION["code_error"] = "E" . $update_err;
                }
            } elseif ($action === 'save_policy') {
                $eligible = isset($_POST['snappay_eligible_methods']) ? $_POST['snappay_eligible_methods'] : [];
                $forced = isset($_POST['snappay_forced_methods']) ? $_POST['snappay_forced_methods'] : [];
                if (snappay_admin_update_method_policy($snappay_config_path, $eligible, $forced, $update_err)) {
                    $_SESSION["code_error"] = "Dسیاست روش‌های پرداخت اسنپ‌پی ذخیره شد.";
                } else {
                    $_SESSION["code_error"] = "E" . $update_err;
                }
            } elseif ($action === 'generate_product_feed') {
                include $bu . $dbc_adrs;
                if (!isset($mysqli) || !is_object($mysqli)) {
                    $_SESSION["code_error"] = "Eاتصال به پایگاه داده برقرار نشد.";
                } else {
                    $feed_error = '';
                    $feed_result = snappay_product_feed_write($mysqli, $feed_error);
                    if ($feed_result === false) {
                        $_SESSION["code_error"] = "E" . $feed_error;
                    } else {
                        $_SESSION["code_error"] = "Dخوراک محصول اسنپ‌پی ساخته شد. تعداد آیتم‌ها: " . (int)$feed_result['items_count'];
                    }
                }
            } else {
                $action_error = "عملیات اسنپ‌پی نامعتبر است.";
            }

            if ($action_error !== '') {
                $_SESSION["code_error"] = "E" . $action_error;
            }
        }
    }

    $redirect = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : ($s . "admin/snappay/");
    header("Location: " . $redirect);
    die;
}

$sp_state = "red";
$sp_data = "غیرفعال - ماژول اسنپ‌پی یافت نشد";
$sp_info = "";
$sp_cfg_chip_class = "";
$sp_cfg_chip_text = "";
$sp_checkout_enabled = false;
$sp_backend_enabled = false;
$sp_oauth_state = "orange";
$sp_oauth_data = "نامشخص";
$sp_oauth_info = "";
$sp_metrics = [
    'pending_count' => null,
    'pending_oldest' => null,
    'last_success_at' => null,
    'last_error_code' => null,
    'last_error_at' => null,
];
$sp_eligible_methods = [];
$sp_forced_methods = [];
$sp_feed_url = 'https://abanfruit.com/snappay-products.json';
$sp_feed_path = $bu . 'snappay-products.json';
$sp_feed_exists = false;
$sp_feed_updated_at = '';
$sp_feed_size = 0;
$sp_method_options = [
    'INSTALLMENT' => 'قسطی',
    'POSTPAID' => 'پس‌پرداخت',
    'FINANCING' => 'اعتباری',
];

if (!isset($_SESSION['snappay_toggle_csrf']) || !is_string($_SESSION['snappay_toggle_csrf']) || strlen($_SESSION['snappay_toggle_csrf']) < 32) {
    try {
        $_SESSION['snappay_toggle_csrf'] = bin2hex(random_bytes(16));
    } catch (Exception $e) {
        $_SESSION['snappay_toggle_csrf'] = sha1(uniqid('snappay_toggle_', true));
    }
}
$sp_toggle_csrf = (string)$_SESSION['snappay_toggle_csrf'];

if (file_exists($bu."modules/snappay/snappay_config.php")) {
    require_once($bu."modules/snappay/snappay_client.php");
    require_once($bu."modules/snappay/snappay_helpers.php");
    require_once($bu."modules/snappay/snappay_runtime.php");
    require_once($bu."modules/snappay/product_feed.php");

    $sp_checkout_enabled = snappay_checkout_enabled();
    $sp_backend_enabled = snappay_backend_enabled();
    $sp_feed_url = snappay_product_feed_public_url();
    $sp_feed_path = snappay_product_feed_output_path();
    $sp_feed_exists = is_file($sp_feed_path);
    if ($sp_feed_exists) {
        $sp_feed_updated_at = date('Y-m-d H:i:s', (int)filemtime($sp_feed_path));
        $sp_feed_size = (int)filesize($sp_feed_path);
    }
    $sp_eligible_methods = (defined('SNAPPAY_ELIGIBLE_PAYMENT_METHOD_TYPES') && is_array(SNAPPAY_ELIGIBLE_PAYMENT_METHOD_TYPES))
        ? SNAPPAY_ELIGIBLE_PAYMENT_METHOD_TYPES
        : [];
    $sp_forced_methods = (defined('SNAPPAY_FORCED_PAYMENT_METHOD_TYPES') && is_array(SNAPPAY_FORCED_PAYMENT_METHOD_TYPES))
        ? SNAPPAY_FORCED_PAYMENT_METHOD_TYPES
        : [];
    $sp_metrics = snappay_admin_health_metrics();

    if (!$sp_checkout_enabled) {
        $sp_cfg_chip_class = "red";
        $sp_cfg_chip_text = "نمایش درگاه در تنظیمات خاموش است.";
    }

    if (!$sp_backend_enabled) {
        $sp_state = "orange";
        $sp_data = "غیرفعال - یکپارچه‌سازی بک‌اند خاموش است";
        $sp_info = "";
        $sp_oauth_state = "orange";
        $sp_oauth_data = "غیرفعال - بک‌اند خاموش است";
    } else {
        [$sp_oauth_ok, $sp_oauth_token, $sp_oauth_err] = snappay_oauth_token();
        if ($sp_oauth_ok) {
            $sp_oauth_state = "green";
            $sp_oauth_data = "فعال";
        } else {
            $sp_oauth_state = "red";
            $sp_oauth_data = "غیرفعال";
            $sp_oauth_info = htmlspecialchars((string)$sp_oauth_err, ENT_QUOTES, 'UTF-8');
        }

        if (defined('SNAPPAY_HEALTHCHECK_ENABLED') && SNAPPAY_HEALTHCHECK_ENABLED) {
            $test_amount = defined('SNAPPAY_HEALTHCHECK_TEST_AMOUNT_IRR') ? (int)SNAPPAY_HEALTHCHECK_TEST_AMOUNT_IRR : 10000;
            if ($test_amount < 1) $test_amount = 10000;

            $sp_res = snappay_api_eligible($test_amount);
            $sp_err = snappay_extract_error_code($sp_res['json'] ?? null);
            $sp_http = (int)($sp_res['http_status'] ?? 0);

            if (!empty($sp_res['ok'])) {
                $sp_state = "green";
                $sp_data = "فعال";
            } elseif ($sp_http > 0) {
                $sp_state = "orange";
                $sp_data = "فعال - پاسخ ناموفق";
                if ($sp_err !== null && $sp_err !== '') {
                    $sp_info = "errorCode: " . htmlspecialchars((string)$sp_err, ENT_QUOTES, 'UTF-8');
                } else {
                    $sp_info = "HTTP: " . $sp_http;
                }
            } else {
                $sp_state = "red";
                $sp_data = "غیرفعال - عدم امکان اتصال به سرویس";
            }
        } else {
            $sp_state = "orange";
            $sp_data = "غیرفعال - بررسی سلامت در تنظیمات خاموش است";
        }
    }
}

$title = getVarFromDB("admin_modules", "name", "flag", $cf);
if (!$title || $title === "") $title = "اسنپ‌پی";

?>

<div id="main" class="middle ls_main">
    <div id="sect1" class="sect">
        <div class="middle container">
            <div class="title">
                <?php echo $title; ?>
            </div>
        </div>
    </div>

    <div class="cut w100p"></div>

    <div id="sect2" class="sect">
        <div class="middle container">
            <h2 class="tac">مدیریت اسنپ‌پی</h2>
            <div class="snappay-module-card">
                <h3>وضعیت سرویس</h3>
                <ul class="state">
                    <li>وضعیت سرویس: <a class="<?php echo $sp_state; ?>"><?php echo $sp_data; ?></a></li>
                    <li>وضعیت OAuth: <a class="<?php echo $sp_oauth_state; ?>"><?php echo $sp_oauth_data; ?></a></li>
                    <li>نمایش درگاه برای سفارش جدید: <a class="<?php echo $sp_checkout_enabled ? 'green' : 'red'; ?>"><?php echo $sp_checkout_enabled ? 'فعال' : 'غیرفعال'; ?></a></li>
                    <li>اتصال بک‌اند: <a class="<?php echo $sp_backend_enabled ? 'green' : 'red'; ?>"><?php echo $sp_backend_enabled ? 'فعال' : 'غیرفعال'; ?></a></li>
                    <?php if($sp_cfg_chip_text !== ""){ ?>
                    <li class="snappay-row">تنظیمات: <a class="<?php echo $sp_cfg_chip_class; ?>"><?php echo htmlspecialchars($sp_cfg_chip_text, ENT_QUOTES, 'UTF-8'); ?></a></li>
                    <?php } ?>
                    <?php if($sp_oauth_info){ ?>
                    <li class="snappay-row">جزئیات OAuth: <span class="snappay-note"><?php echo $sp_oauth_info; ?></span></li>
                    <?php } ?>
                    <?php if($sp_info){ ?>
                    <li class="snappay-row">جزئیات سرویس: <span class="snappay-note"><?php echo $sp_info; ?></span></li>
                    <?php } ?>
                </ul>
            </div>

            <div class="snappay-module-card">
                <h3>اقدام سریع</h3>
                <div class="snappay-action-row">
                    <form method="post" class="snappay-toggle-form">
                        <input type="hidden" name="snappay_toggle_csrf" value="<?php echo htmlspecialchars($sp_toggle_csrf, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="snappay_toggle_action" value="toggle_checkout">
                        <button type="submit" class="btn snappay-btn"><?php echo $sp_checkout_enabled ? 'غیرفعال‌کردن نمایش درگاه' : 'فعال‌کردن نمایش درگاه'; ?></button>
                    </form>
                    <form method="post" class="snappay-toggle-form">
                        <input type="hidden" name="snappay_toggle_csrf" value="<?php echo htmlspecialchars($sp_toggle_csrf, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="snappay_toggle_action" value="toggle_backend">
                        <button type="submit" class="btn snappay-btn"><?php echo $sp_backend_enabled ? 'خاموش‌کردن بک‌اند' : 'روشن‌کردن بک‌اند'; ?></button>
                    </form>
                </div>
            </div>

            <div class="snappay-module-card">
                <h3>خوراک محصول سرچوایز</h3>
                <ul class="state">
                    <li>وضعیت فایل: <a class="<?php echo $sp_feed_exists ? 'green' : 'orange'; ?>"><?php echo $sp_feed_exists ? 'ساخته شده' : 'هنوز ساخته نشده'; ?></a></li>
                    <li class="snappay-row">لینک ثابت: <span class="snappay-note" dir="ltr"><?php echo htmlspecialchars($sp_feed_url, ENT_QUOTES, 'UTF-8'); ?></span></li>
                    <?php if($sp_feed_exists){ ?>
                    <li class="snappay-row">آخرین ساخت: <span class="snappay-note" dir="ltr"><?php echo htmlspecialchars($sp_feed_updated_at, ENT_QUOTES, 'UTF-8'); ?></span></li>
                    <li class="snappay-row">حجم فایل: <span class="snappay-note" dir="ltr"><?php echo number_format((float)($sp_feed_size / 1024), 1); ?> KB</span></li>
                    <?php } ?>
                </ul>
                <div class="snappay-action-row">
                    <form method="post" class="snappay-toggle-form">
                        <input type="hidden" name="snappay_toggle_csrf" value="<?php echo htmlspecialchars($sp_toggle_csrf, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="snappay_toggle_action" value="generate_product_feed">
                        <button type="submit" class="btn snappay-btn">ساخت / به‌روزرسانی خوراک محصول</button>
                    </form>
                    <a class="btn snappay-btn" href="<?php echo htmlspecialchars($sp_feed_url, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener">مشاهده فایل</a>
                </div>
                <div class="snappay-inline-note">این لینک را یک بار برای اسنپ‌پی/سرچوایز ارسال کنید و کران‌جاب را برای ساخت شبانه همین فایل تنظیم کنید.</div>
            </div>

            <div class="snappay-module-card">
                <h3>سیاست روش‌های پرداخت</h3>
                <form method="post" class="snappay-toggle-form snappay-stack-form">
                    <input type="hidden" name="snappay_toggle_csrf" value="<?php echo htmlspecialchars($sp_toggle_csrf, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="snappay_toggle_action" value="save_policy">
                    <div class="snappay-policy-block">
                        <div class="snappay-subtitle">روش‌های مجاز</div>
                        <div class="snappay-chip-group">
                            <?php foreach($sp_method_options as $method_key => $method_label){ ?>
                            <label class="snappay-method-chip">
                                <input type="checkbox" name="snappay_eligible_methods[]" value="<?php echo $method_key; ?>" <?php echo in_array($method_key, $sp_eligible_methods, true) ? 'checked' : ''; ?>>
                                <span><?php echo $method_label; ?> <small><?php echo $method_key; ?></small></span>
                            </label>
                            <?php } ?>
                        </div>
                        <div class="snappay-subtitle">روش‌های اجباری</div>
                        <div class="snappay-chip-group">
                            <?php foreach($sp_method_options as $method_key => $method_label){ ?>
                            <label class="snappay-method-chip">
                                <input type="checkbox" name="snappay_forced_methods[]" value="<?php echo $method_key; ?>" <?php echo in_array($method_key, $sp_forced_methods, true) ? 'checked' : ''; ?>>
                                <span><?php echo $method_label; ?> <small><?php echo $method_key; ?></small></span>
                            </label>
                            <?php } ?>
                        </div>
                        <div class="snappay-form-footer">
                            <button type="submit" class="btn snappay-btn">ذخیره سیاست روش‌های پرداخت</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="snappay-module-card">
                <h3>شاخص‌های سلامت</h3>
                <table class="tracking snappay-metrics-table">
                    <thead>
                        <tr>
                            <th>شاخص</th>
                            <th>مقدار</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>تراکنش‌های در انتظار</td>
                            <td><?php echo $sp_metrics['pending_count'] === null ? '-' : (int)$sp_metrics['pending_count']; ?></td>
                        </tr>
                        <tr>
                            <td>قدیمی‌ترین تراکنش در انتظار</td>
                            <td><?php echo !empty($sp_metrics['pending_oldest']) ? htmlspecialchars((string)$sp_metrics['pending_oldest'], ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                        </tr>
                        <tr>
                            <td>آخرین تراکنش موفق</td>
                            <td><?php echo !empty($sp_metrics['last_success_at']) ? htmlspecialchars((string)$sp_metrics['last_success_at'], ENT_QUOTES, 'UTF-8') : '-'; ?></td>
                        </tr>
                        <tr>
                            <td>آخرین خطای درگاه</td>
                            <td><?php echo !empty($sp_metrics['last_error_code']) ? htmlspecialchars((string)$sp_metrics['last_error_code'], ENT_QUOTES, 'UTF-8') : '-'; ?><?php if(!empty($sp_metrics['last_error_at'])){ ?> (<?php echo htmlspecialchars((string)$sp_metrics['last_error_at'], ENT_QUOTES, 'UTF-8'); ?>)<?php } ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style type="text/css">
    ul.state{
        margin:10px 0;
        display:table;
    }

    ul.state li{
        margin:0 15px 8px 0;
        min-height:35px;
        float:right;
        line-height:30px;
    }

    ul.state li a{
        padding: 0 20px;
        line-height: 30px;
        border-radius: 16px;
        display:inline-block;
    }

    ul.state li a.green{
        background-color:#ecfdf3;
        border:1px solid #86efac;
        color:#166534;
    }

    ul.state li a.orange{
        background-color:#fffbeb;
        border:1px solid #fcd34d;
        color:#92400e;
    }

    ul.state li a.red{
        background-color:#fef2f2;
        border:1px solid #fca5a5;
        color:#991b1b;
    }

    ul.state li.snappay-row{
        height:auto;
        line-height:1.7;
    }

    ul.state li.snappay-row span.snappay-note{
        display:inline-block;
        line-height:24px;
        padding:0 10px;
        border-radius:12px;
        border:1px solid #cbd5e1;
        background:#f8fafc;
        color:#334155;
    }

    .snappay-module-card{
        margin:16px 0;
        padding:0 0 16px 0;
        border-bottom:1px solid #e4e4e4;
    }

    .snappay-module-card:last-child{
        border-bottom:0;
        padding-bottom:0;
    }

    .snappay-module-card h3{
        margin:0 0 8px 0;
    }

    .snappay-action-row{
        display:flex;
        flex-wrap:wrap;
        gap:10px;
    }

    .snappay-toggle-form{
        margin:0;
    }

    .snappay-stack-form{
        display:block;
    }

    .snappay-btn{
        width:auto !important;
        display:inline-block !important;
        padding:0 16px !important;
        line-height:30px !important;
        font-size:16px !important;
        margin-top:0 !important;
        border-radius:16px !important;
        border:1px solid #cbd5e1 !important;
        background:#ffffff !important;
        color:#1e293b !important;
        cursor:pointer;
    }

    .snappay-btn:hover{
        background:#eff6ff !important;
        border-color:#93c5fd !important;
        color:#1d4ed8 !important;
    }

    .snappay-btn:focus-visible{
        outline:2px solid #93c5fd;
        outline-offset:2px;
    }

    .snappay-policy-block{
        display:block;
    }

    .snappay-chip-group{
        display:flex;
        flex-wrap:wrap;
        gap:8px;
        margin:6px 0 2px 0;
    }

    .snappay-method-chip{
        display:inline-block;
        margin:0;
        cursor:pointer;
    }

    .snappay-method-chip input{
        position:absolute;
        opacity:0;
        pointer-events:none;
    }

    .snappay-method-chip span{
        display:inline-block;
        line-height:30px;
        padding:0 12px;
        border-radius:16px;
        border:1px solid #cbd5e1;
        background:#f8fafc;
        color:#334155;
        font-size:14px;
    }

    .snappay-method-chip span small{
        font-size:11px;
        opacity:0.8;
    }

    .snappay-method-chip input:checked + span{
        background:#dbeafe;
        border-color:#60a5fa;
        color:#1e40af;
    }

    .snappay-method-chip:hover span{
        border-color:#93c5fd;
        background:#eff6ff;
        color:#1d4ed8;
    }

    .snappay-method-chip input:focus-visible + span{
        outline:2px solid #93c5fd;
        outline-offset:2px;
    }

    .snappay-subtitle{
        font-weight:bold;
        margin:8px 0 4px 0;
    }

    .snappay-form-footer{
        margin-top:8px;
    }

    .snappay-metrics-table{
        margin-top:8px;
    }

    .snappay-inline-note{
        margin-top:10px;
        padding:6px 10px;
        border-radius:4px;
        background:#f8fafc;
        border:1px solid #cbd5e1;
        color:#334155;
        display:inline-block;
    }
</style>

<?php
    }
}
?>
