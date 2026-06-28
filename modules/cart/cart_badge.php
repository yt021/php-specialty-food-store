<?php

    $indexed = 1;

    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");

    include $bu."modules/cart/session_start.php";

    if(isset($_SESSION["logged"])){
        cart_restore_draft_for_logged_user();
        cart_sync_draft_for_logged_user();
    }

    if(!isset($_SESSION["cart"]) || !is_object($_SESSION["cart"])){
        $_SESSION["cart"] = new cart();
    }

    echo "D";
    echo (int)$_SESSION["cart"]->number();
    echo "-";
    echo price_sep($_SESSION["cart"]->price());

    die;

?>
