<?php

if (!isset($_SESSION["a_logged"]) || !is_object($_SESSION["a_logged"]) || !method_exists($_SESSION["a_logged"], 'get_level') || $_SESSION["a_logged"]->get_level() < 2) {
    $_SESSION["code_error"] = "EAccess denied.";
    return;
}

if (
    !isset($_POST['snappay_csrf']) ||
    !isset($_SESSION['snappay_csrf']) ||
    !is_string($_POST['snappay_csrf']) ||
    !is_string($_SESSION['snappay_csrf']) ||
    !hash_equals($_SESSION['snappay_csrf'], $_POST['snappay_csrf'])
) {
    $_SESSION["code_error"] = "EUnsafe request (CSRF). Please retry.";
    return;
}

require_once $bu . "modules/snappay/reconcile_pending.php";

if (!snappay_backend_enabled()) {
    $_SESSION["code_error"] = "EBackend SnappPay is disabled by admin.";
    return;
}

$summary = snappay_run_reconcile_pending('admin_manual');
if (!is_array($summary)) {
    $_SESSION["code_error"] = "EReconcile run failed.";
    return;
}

if (!empty($summary['ok'])) {
    $_SESSION["code_error"] = "DReconcile done: checked " . (int)$summary['checked'] . ", finalized " . (int)$summary['finalized'] . ", pending " . (int)$summary['pending'] . ".";
} else {
    $_SESSION["code_error"] = "EReconcile skipped: " . (string)($summary['reason'] ?? 'unknown');
}
