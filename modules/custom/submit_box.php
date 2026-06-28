<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
$result = null;
if(isset($_SESSION["custom"]) && is_a($_SESSION["custom"],"order_box"))
if($_SESSION["custom"]->content->number() == $_SESSION["custom"]->capacity){
//    $_SESSION["cart"]->add_order($_SESSION["custom"]->pid,$_SESSION["custom"]->weight);
    $cart_find_id = $_SESSION["cart"]->find_order($_SESSION["custom"]->pid,$_SESSION["custom"]->weight(),$_SESSION["custom"]->opbid());
    if($cart_find_id>-1){
        $_SESSION["cart"]->change_no_order($_SESSION["custom"]->pid,$_SESSION["custom"]->weight(),$_SESSION["custom"]->opbid(),1);
    }else{
        $_SESSION["cart"]->orders[sizeof($_SESSION["cart"]->orders)] = $_SESSION["custom"];
    }
    $result = "ok";
}else{
    $_SESSION["code_error"]="E"."خطایی رخ داده است، مجددا تلاش فرمایید.";
}


?>
<?php
        }
    }
?>
