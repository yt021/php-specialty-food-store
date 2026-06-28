<?php
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/admin/session_start.php";
    if(!isset($_SESSION[$cf]->id)){
        header("Location: $s"."admin/");
        die;
    }
        
?>
<?php
    $oid = $_SESSION[$cf]->id;
    include $bu."modules/cart/invoice_data.php";
?>
