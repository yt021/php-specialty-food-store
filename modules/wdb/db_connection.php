<?php
include_once 'db_config.php';

$mysqli = null;

if (
    isset($GLOBALS['wdb_mysqli_bootstrap']) &&
    $GLOBALS['wdb_mysqli_bootstrap'] === 'ready' &&
    isset($GLOBALS['mysqli']) &&
    $GLOBALS['mysqli'] instanceof mysqli
) {
    $mysqli = $GLOBALS['mysqli'];
} elseif (
    isset($GLOBALS['wdb_mysqli_bootstrap']) &&
    $GLOBALS['wdb_mysqli_bootstrap'] === 'failed'
) {
    $mysqli = null;
} else {
    $GLOBALS['wdb_mysqli_bootstrap'] = 'initializing';
    $conn = @new mysqli(HOST, USER, PASSWORD, DATABASE);

    if ($conn instanceof mysqli && !$conn->connect_errno) {
        if (!$conn->set_charset("utf8mb4")) {
            error_log("DB charset setup failed: " . $conn->error);
        }
        $GLOBALS['mysqli'] = $conn;
        $GLOBALS['wdb_mysqli_bootstrap'] = 'ready';
        $mysqli = $conn;
    } else {
        $err_no = ($conn instanceof mysqli) ? (int)$conn->connect_errno : 0;
        $err_msg = ($conn instanceof mysqli) ? (string)$conn->connect_error : 'unknown';
        error_log("DB connection failed ($err_no): $err_msg");
        $GLOBALS['mysqli'] = null;
        $GLOBALS['wdb_mysqli_bootstrap'] = 'failed';
        $mysqli = null;
    }
}
?>
