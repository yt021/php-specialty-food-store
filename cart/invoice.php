<?php
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/cart/session_start.php";
    if(!isset($_SESSION["oid"])){
        header("Location: $s"."cart/");
        die;
    }
        
?>
<?php
    $oid = $_SESSION["oid"];
    include $bu."modules/cart/invoice_data.php";
?>