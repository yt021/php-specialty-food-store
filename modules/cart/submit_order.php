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
    
    $cart = $_SESSION["cart"];
    $address =  $_SESSION["address"];
    if(!isset($_SESSION["send_date"]))$send_date="";
    else $send_date = $_SESSION["send_date"];
    
    //    Send Price Calculation
    if($send_date){
        $send_cost = getVarFromDB("sd_shifts","price","id",$send_date->shift);
    }else{
        
        if($address->data()["county"] != "شهر تهران")
        {
            $send_date = new send_date('post',2,0);
            $send_cost = getVarFromDB("sd_shifts","price","id",$send_date->shift);
        }else{
            $send_cost = $address->data()["cost"];
        }
    }
    if($address->data()["county"] == "استان تهران")
    {
        $send_date = new send_date('post',2,0);
        $send_cost = getVarFromDB("sd_shifts","price","id",$send_date->shift);
    }
    
    //      Sales Calculation
    unset($_SESSION["sales"]);
    $admin_state = 1;
    if(isset($_SESSION["a_logged"]))$admin_state = 0;
    if(isset($_SESSION["logged"])){$mode = "new";$uid = $_SESSION["logged"]->uid;}
    else{$mode = "first";$uid=0;}
    
    $send_applicable = true;
    if($send_date->shift == 3){
    $send_applicable = false;}
    
    $sales = new sales($mode,$uid,$cart->price(),$send_cost,$admin_state,$send_applicable);
    $cart_pure = (int)max($cart->price()-$sales->cart_sale(),0);
    $total_price = $cart->price()+$send_cost;
    $pay_price = (int)max($total_price - $sales->total_sale(),0);
    
    $sale_total = (int)min($sales->total_sale(),$total_price);
    
    
    
    
// Create Order
    if(!isset($_SESSION["send_date"]))$_SESSION["send_date"]="";

    // Always keep $oid in sync with session if it already exists
    $oid = isset($_SESSION["oid"]) ? $_SESSION["oid"] : null;

    // For first submission (or non-admin), create a new order
    if(!isset($_SESSION["admin_order_set"])){
        $oid = db_new_order($uid,$aid,$cart,$cart_pure,$sale_total,$pay_price,$send_date);
        db_new_orders_sale($uid,$oid,$sales);
        $_SESSION["oid"] = $oid;
        if(isset($_SESSION["a_logged"]))$_SESSION['admin_order_set'] = 1;
    }
    $etb = "errors_order_submit";
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
        $ipAddress = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
    }
    $browser = $_SERVER['HTTP_USER_AGENT'];
    
    $has_error = false;
    
    if(!$uid){
        $has_error = true;
        $error_stage = "user";
    }else if(!$aid){
        $has_error = true;
        $error_stage = "address";
    }else if(!$oid){
        $has_error = true;
        $error_stage = "order";
    }
    
    if($has_error){
        $error_message = "failed to create $error_stage, ".var_export($_SESSION,true);
        $st = "INSERT INTO $etb (error_stage,error_message,ip,browser,uid,aid,oid) VALUES (?,?,?,?,?,?,?);";
        $st = $mysqli->prepare($st);
        $st->bind_param('sssssss',$error_stage,$error_message,$ipAddress,$browser,$uid,$aid,$oid);
        if(!$st->execute()){
            echo "error";
            exit;
        }
        // Generic message for customers
        $_SESSION["cart_notice"] = "خطایی رخ داده است. لطفا مجددا اقدام نمایید.";
        // Extra debug info only for admins so they can see what failed
        if(isset($_SESSION["a_logged"])){
            $_SESSION["cart_notice"] .= " (debug: stage=".$error_stage.", uid=".$uid.", aid=".$aid.", oid=".$oid.")";
        }
        $result = "no";
    }else{
        $result = "ok";
    }
    
//    $_SESSION["cart"] = new cart();
//    if(!$oid){
//        var_dump($_SESSION["send_date"]);
//        
//    }
    
?>
<?php
        }
    }
?>
