<?php

    $indexed = 1;

    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");

    include $bu."modules/cart/session_start.php";

    if(isset($_SESSION["a_logged"])){
        $_SESSION["cart"] = new cart();
        cart_sync_draft_for_logged_user();
        $fields = ["tel","create_code","user","address","logged","sales","sales_total","cart_page"];
        foreach($fields as $f){
          if(isset($_SESSION[$f]))unset($_SESSION[$f]);
        }
    }

    header("Location:$s"."cart/");

    die;

?>
