<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

if(isset($_SESSION["logged"])){
    $uid = $_SESSION["logged"]->uid;
// Check Address
    $data["address"] = $_SESSION["address"]->data();
    if(db_check_address($uid,$data["address"])){
        $aid = db_check_address($uid,$data["address"]);
    }else{
// Create Address
        $aid = db_new_address($uid,$data["address"]);
    }
}else{
    $data = $_SESSION["user"]->data();
    $data["address"] = $_SESSION["address"]->data();
    $uid = db_new_user($_SESSION["user"]->data());
    $aid = db_new_address($uid,$data["address"]);
    $_SESSION["logged"] = new logged($uid,$data);
}
    
// Create Order
    if(!isset($_SESSION["send_date"]))$_SESSION["send_date"]="";
    
    $oid = db_new_order($uid,$aid,$_SESSION["cart"],$_SESSION["cart_pure"],$_SESSION["sale_total"],$_SESSION["pay_price"],$_SESSION["send_date"]);
    db_new_orders_sale($uid,$oid,$_SESSION["sales"]);
    $_SESSION["oid"] = $oid;
    
//    $_SESSION["cart"] = new cart();
    if(!$oid){
        // var_dump($_SESSION["send_date"]);
        echo "error";
        exit;
    }
    $result = "ok";
?>
<?php
        }
    }
?>