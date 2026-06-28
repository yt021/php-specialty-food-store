<?php

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
        $legacy_count = 0;

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
            1,
            $legacy_count
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
            $error_msg = "الگوی سیاست روش پرداخت اسنپ‌پی در فایل تنظیمات پیدا نشد.";
            return false;
        }

        if (file_put_contents($config_path, $content, LOCK_EX) === false) {
            $error_msg = "نوشتن سیاست روش پرداخت اسنپ‌پی ناموفق بود.";
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

if(isset($indexed)){

    if($indexed == 1){
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
                            $action_error = "ابتدا تبادل بک‌اند اسنپ‌پی را فعال کنید.";
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
                            $_SESSION["code_error"] = "Dسیاست روش پرداخت اسنپ‌پی با موفقیت ذخیره شد.";
                        } else {
                            $_SESSION["code_error"] = "E" . $update_err;
                        }
                    } elseif ($action === 'run_reconcile') {
                        require_once $bu . "modules/snappay/reconcile_pending.php";
                        if (!snappay_backend_enabled()) {
                            $_SESSION["code_error"] = "Eبک‌اند اسنپ‌پی خاموش است و Reconcile اجرا نمی‌شود.";
                        } else {
                            $lookback = isset($_POST['snappay_reconcile_lookback']) ? (int)$_POST['snappay_reconcile_lookback'] : 180;
                            $batch = isset($_POST['snappay_reconcile_batch']) ? (int)$_POST['snappay_reconcile_batch'] : 200;
                            if ($lookback < 1) $lookback = 1;
                            if ($lookback > 43200) $lookback = 43200;
                            if ($batch < 1) $batch = 1;
                            if ($batch > 1000) $batch = 1000;

                            $summary = snappay_run_reconcile_pending('admin_dashboard', [
                                'lookback_minutes' => $lookback,
                                'batch_size' => $batch,
                            ]);
                            $_SESSION['snappay_last_reconcile_summary'] = is_array($summary) ? $summary : null;
                            if (is_array($summary) && !empty($summary['ok'])) {
                                $_SESSION["code_error"] = "DReconcile انجام شد: checked=" . (int)$summary['checked'] . " finalized=" . (int)$summary['finalized'] . " pending=" . (int)$summary['pending'];
                            } else {
                                $_SESSION["code_error"] = "EReconcile انجام نشد: " . (is_array($summary) ? (string)($summary['reason'] ?? 'unknown') : 'unknown');
                            }
                        }
                    } else {
                        $action_error = "عملیات نامعتبر برای اسنپ‌پی.";
                    }

                    if ($action_error !== '') {
                        $_SESSION["code_error"] = "E" . $action_error;
                    }
                }
            }

            $redirect = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : ($s . "admin/");
            header("Location: " . $redirect);
            die;
        }
?>

<div id="main" class="middle ls_main">

    <div id="sect1" class="sect">

        <div class="middle container">

            <div class="title">

                داشبورد

            </div>

        </div>

    </div>



    <div class="cut w100p"></div>

    <?php

    if($_SESSION["a_logged"]->get_level() == 2){

?>

    <div id="sect3" class="sect">

        <div class="middle container">

            <h2 class="tac">وضعیت سرویس‌ها</h2>

            <ul>

                <li>

                    <h3>سرویس پیامکی: قاصدک</h3>

<?php

//data acquisition

{

    $curl = curl_init();

    curl_setopt_array($curl, array(

    CURLOPT_URL => "https://example.invalid/disabled",

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_ENCODING => "",

    CURLOPT_MAXREDIRS => 10,

    CURLOPT_TIMEOUT => 30,

    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,

    CURLOPT_CUSTOMREQUEST => "POST",

    CURLOPT_POSTFIELDS => "",

    CURLOPT_HTTPHEADER => array(





    "apikey: not-configured",

    "cache-control: no-cache",

    "content-type: application/x-www-form-urlencoded",

    ),

    ));



    $response = curl_exec($curl);

    $err = curl_error($curl);



    curl_close($curl);

    if($err){

        $state = "red";

        $data = "غیر فعال - عدم امکان برقراری ارتباط با سرویس";

    }else if($response){

        

        $res = json_decode($response);

        //var_dump($response);

        $res_code = $res->result->code;

        if($res_code == 200){

            $bal = (int)$res->items->balance;

            $exp = $res->items->expire;

            $api_exp = strtotime(getVarFromDB('services','expire','flag','sms_api_exp'));

        }

        // error

        if($res_code == 401){

            $state = "red";

            $data = "غیر فعال - کلید امنیتی غیر فعال است.";

        }else if($res_code !== 200){

            $state = "red";

            $data = "غیر فعال - خطا در ارتباط";

        }

        else if($exp - time() < 0){

            $state = "red";

            $data = "غیر فعال - اشتراک سرویس منقضی شده است.";

        }else if($bal < 0){

            $state = "red";

            $data = "غیر فعال - اعتبار حساب پایان یافته است.";

        }

        // warning

        else

        if($bal < 50000){

            $state = "orange";

            $data = "فعال - نیاز به افزایش اعتبار";

        }else if($exp - time() < 7*24*3600){

            $state = "orange";

            $data = "فعال - نیاز به تمدید اشتراک";

        }else if($api_exp - time() < 7*24*3600){

            $state = "orange";

            $data = "فعال - نیاز به تمدید کلید امنیتی (توسط طراح)";

        }else{

            $state = "green";

            $data = "فعال";

        }

        

    }

}

?>

                    <ul class="state">

                        <li>وضعیت سرویس: <a class="<?php echo $state; ?>" <?php if($state == "orange") echo 'target="_blank" href="http://developers.ghasedak.me/"'; ?>><?php echo $data; ?></a></li>

<?php

    if(isset($res_code) && $res_code == 200){

?>

                        <li>اعتبار حساب: <?php echo $bal; ?> ریال</li>

                        <li>سررسید سرویس: <?php echo correctDate_ts($exp); ?></li>

                        <li>سررسید کلید امنیتی: <?php echo correctDate_ts($api_exp); ?></li>

<?php

    }

?>

                    </ul>

                </li>

                <li>

                    <h3>میزبانی هاست: میهن وب هاست (ircpanel30)</h3>

<?php

    $exp = getVarFromDB('services','expire','flag','mihan_host');

    $exp_ts = strtotime($exp);

    if($exp_ts - time() < 7*24*3600){

        $state = "orange";

        $data = "فعال - نیاز به تمدید اشتراک (توسط طراح)";

    }else{

        $state = "green";

        $data = "فعال";

    }

?>

                    <ul class="state">

                        <li>وضعیت سرویس: <a class="<?php echo $state; ?>" ><?php echo $data; ?></a></li>

                        <li>سررسید سرویس: <?php echo correctDate_ts($exp_ts); ?></li>

                    </ul>

                </li>

                <li>

                    <h3>دامنه: ایران سرور (abanfruit.com)</h3>

<?php

    $exp = getVarFromDB('services','expire','flag','domain');

    $exp_ts = strtotime($exp);

    if($exp_ts - time() < 7*24*3600){

        $state = "orange";

        $data = "فعال - نیاز به تمدید اشتراک";

    }else{

        $state = "green";

        $data = "فعال";

    }

?>

                    <ul class="state">

                        <li>وضعیت سرویس: <a class="<?php echo $state; ?>" <?php if($state == "orange") echo 'target="_blank" href="https://hub.iranserver.com/index.php?rp=/login"'; ?>><?php echo $data; ?></a></li>

                        <li>سررسید سرویس: <?php echo correctDate_ts($exp_ts); ?></li>

                    </ul>

                </li>

                <li>

                    <h3>درگاه پرداخت: زرین پال</h3>

<?php

//data acquisition

{

    $data = array("merchant_id" => "disabled",

    "amount" => 1000,

    "callback_url" => "https://abanfruit.com/verify.php",

    "description" => "تست ارتباط",

    "metadata" => [ "email" => "","mobile"=>"09000000000"],

    );

    

    $jsonData = json_encode($data);

    $ch = curl_init('https://example.invalid/disabled');

    curl_setopt($ch, CURLOPT_USERAGENT, 'ZarinPal Rest Api v1');

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_HTTPHEADER, array(

        'Content-Type: application/json',

        'Content-Length: ' . strlen($jsonData)

    ));



    $response = curl_exec($ch);

    $err = curl_error($ch);

    $response = json_decode($response, true, JSON_PRETTY_PRINT);

    curl_close($ch);



    if($err){

        $state = "red";

        $data = "غیر فعال - عدم امکان برقراری ارتباط با سرویس";

    }else if($response){

        $res_code = $response['data']['code'];

        if(isset($response['data']['code']) && $response['data']['code'] == 100){

            $state = "green";

            $data = "فعال";

        }else{

            $state = "red";

            $data = "غیر فعال - خطا در ارتباط";

        }

        

    }

}

?>

                    <ul class="state">

                        <li>وضعیت سرویس: <a class="<?php echo $state; ?>" <?php if($state != "green") echo 'target="_blank" href="https://next.zarinpal.com/auth/login/"'; ?>><?php echo $data; ?></a></li>

                    </ul>

                </li>

                <li>
                    <h3>درگاه پرداخت اقساطی: اسنپ‌پی</h3>
<?php
{
    $sp_state = "red";
    $sp_data = "غیر فعال - ماژول اسنپ‌پی یافت نشد";
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
    $sp_reconcile_lookback_default = 180;
    $sp_reconcile_batch_default = 200;
    $sp_reconcile_last = isset($_SESSION['snappay_last_reconcile_summary']) && is_array($_SESSION['snappay_last_reconcile_summary'])
        ? $_SESSION['snappay_last_reconcile_summary']
        : null;

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

        $sp_checkout_enabled = snappay_checkout_enabled();
        $sp_backend_enabled = snappay_backend_enabled();
        $sp_eligible_methods = (defined('SNAPPAY_ELIGIBLE_PAYMENT_METHOD_TYPES') && is_array(SNAPPAY_ELIGIBLE_PAYMENT_METHOD_TYPES))
            ? SNAPPAY_ELIGIBLE_PAYMENT_METHOD_TYPES
            : [];
        $sp_forced_methods = (defined('SNAPPAY_FORCED_PAYMENT_METHOD_TYPES') && is_array(SNAPPAY_FORCED_PAYMENT_METHOD_TYPES))
            ? SNAPPAY_FORCED_PAYMENT_METHOD_TYPES
            : [];
        $sp_reconcile_lookback_default = defined('SNAPPAY_RECONCILE_LOOKBACK_MINUTES') ? (int)SNAPPAY_RECONCILE_LOOKBACK_MINUTES : 180;
        $sp_reconcile_batch_default = defined('SNAPPAY_RECONCILE_BATCH_SIZE') ? (int)SNAPPAY_RECONCILE_BATCH_SIZE : 200;
        $sp_metrics = snappay_admin_health_metrics();

        if (!$sp_checkout_enabled) {
            $sp_cfg_chip_class = "red";
            $sp_cfg_chip_text = "گزینه استفاده از درگاه اسنپ‌پی توسط ادمین غیرفعال شده است";
        }

        if (!$sp_backend_enabled) {
            $sp_state = "orange";
            $sp_data = "غیرفعال - تبادل بک‌اند اسنپ‌پی توسط ادمین خاموش است";
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
                    $sp_data = "فعال - پاسخ غیرموفق";
                    if ($sp_err !== null && $sp_err !== '') {
                        $sp_info = "errorCode: " . htmlspecialchars((string)$sp_err, ENT_QUOTES, 'UTF-8');
                    } else {
                        $sp_info = "HTTP: " . $sp_http;
                    }
                } else {
                    $sp_state = "red";
                    $sp_data = "غیر فعال - عدم امکان برقراری ارتباط با سرویس";
                }
            } else {
                $sp_state = "orange";
                $sp_data = "غیرفعال - Health Check از تنظیمات خاموش است";
            }
        }
    }
}
?>
                    <ul class="state">
                        <li>وضعیت سرویس: <a class="<?php echo $sp_state; ?>"><?php echo $sp_data; ?></a></li>
                        <li>وضعیت OAuth: <a class="<?php echo $sp_oauth_state; ?>"><?php echo $sp_oauth_data; ?></a></li>
                        <li>نمایش درگاه برای سفارش جدید: <a class="<?php echo $sp_checkout_enabled ? 'green' : 'red'; ?>"><?php echo $sp_checkout_enabled ? 'فعال' : 'غیرفعال'; ?></a></li>
                        <li>تبادل بک‌اند با اسنپ‌پی: <a class="<?php echo $sp_backend_enabled ? 'green' : 'red'; ?>"><?php echo $sp_backend_enabled ? 'فعال' : 'غیرفعال'; ?></a></li>
                        <?php if($sp_cfg_chip_text !== ""){ ?>
                        <li class="snappay-row">تنظیمات: <a class="<?php echo $sp_cfg_chip_class; ?>"><?php echo htmlspecialchars($sp_cfg_chip_text, ENT_QUOTES, 'UTF-8'); ?></a></li>
                        <?php } ?>
                        <?php if($sp_oauth_info){ ?>
                        <li class="snappay-row">جزئیات OAuth: <span class="snappay-note"><?php echo $sp_oauth_info; ?></span></li>
                        <?php } ?>
                        <?php if($sp_info){ ?>
                        <li class="snappay-row">جزئیات سرویس: <span class="snappay-note"><?php echo $sp_info; ?></span></li>
                        <?php } ?>
                        <li><a class="orange curpo" onclick="show_admin_service('snappay')">Open Snappay module</a></li>
                    </ul>
                </li>

            </ul>

        </div>

    </div>
<style type="text/css">

    #sect3 ul li{

        float:none;

    }    

    ul.state{

        margin:10px;

        display:table;

    }

    #sect3 ul.state li{

        margin:0 15px;

        height:35px;

        float:right;

    }

    ul.state li a{

/*        border:1px solid black;*/

        padding: 0px 20px;

        line-height: 30px;

        border-radius: 16px;

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
    #sect3 ul.state li.snappay-row{
        height:auto;
        min-height:35px;
    }

    #sect3 ul.state li.snappay-row span.snappay-note{
        display:inline-block;
        line-height:24px;
        padding:0 10px;
        border-radius:12px;
        border:1px solid #cbd5e1;
        background:#f8fafc;
        color:#334155;
    }

</style>

<?php

    }

?>

    <div class="cut w100p"></div>



    <div id="sect2" class="sect ">

        <div class="middle container">

    <?php

        $u_level = $_SESSION["a_logged"]->get_level();

        $st = "SELECT flag,name FROM admin_modules ORDER BY id ASC";

        $st = $mysqli->prepare($st);

        if(!$st->execute()){

            echo "E";

            exit;

        }

        $res = $st->get_result();

        $k = 0;

        while($row = $res->fetch_assoc())

        {

            if($_SESSION["a_logged"]->check_access($row["flag"])){

    ?>

            <a onclick="show_admin_service('<?php echo $row["flag"] ?>')" class="btn half fr admin_dashboard">

                <?php

                    echo $row["name"];

                ?>

            </a>

            

    <?php

            }

        }

    ?>

        </div>  

    </div>



    <div class="cut w100p"></div>



</div>



<?php

        }

    }

?>


