<?php

$indexed = 1;
include_once($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
include_once $bu."modules/cart/session_start.php";
include_once $bu."modules/cart/cart_funcs.php";

header('Content-Type: application/json; charset=UTF-8');

$response = array(
    "ok" => false,
    "status" => "error",
    "message" => "خطا در اعتبارسنجی کد تخفیف. لطفا مجدد تلاش کنید.",
    "normalized_code" => "",
    "effects" => array()
);

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode($response,JSON_UNESCAPED_UNICODE);
    exit;
}

$mode = "first";
$uid = 0;
if(isset($_SESSION["logged"]) && is_object($_SESSION["logged"]) && isset($_SESSION["logged"]->uid)){
    $mode = "new";
    $uid = (int)$_SESSION["logged"]->uid;
}

$cart_cost = 0;
if(isset($_SESSION["cart"]) && is_object($_SESSION["cart"]) && method_exists($_SESSION["cart"],"price")){
    $cart_cost = (int)$_SESSION["cart"]->price();
}

$send_cost = 0;
if(isset($_SESSION["send_cost"]) && is_numeric($_SESSION["send_cost"])){
    $send_cost = (int)$_SESSION["send_cost"];
}else if(isset($_SESSION["address"]) && is_object($_SESSION["address"])){
    $address_data = $_SESSION["address"]->data();
    if(is_array($address_data) && isset($address_data["cost"]) && is_numeric($address_data["cost"])){
        $send_cost = (int)$address_data["cost"];
    }
}

$admin_state = 1;
if(isset($_SESSION["a_logged"])){
    $admin_state = 0;
}

$send_applicable = true;
if(isset($_SESSION["send_date"]) && is_object($_SESSION["send_date"]) && isset($_SESSION["send_date"]->shift) && (int)$_SESSION["send_date"]->shift === 3){
    $send_applicable = false;
}

$lksr = isset($_POST["lksr"]) ? (string)$_POST["lksr"] : "";
$validation = cart_discount_code_validate_for_cart(
    $lksr,
    $mode,
    $uid,
    $cart_cost,
    $send_cost,
    $admin_state,
    $send_applicable
);

$response["ok"] = !empty($validation["ok"]);
$response["status"] = isset($validation["status"]) ? (string)$validation["status"] : "error";
$response["message"] = isset($validation["message"]) ? (string)$validation["message"] : $response["message"];
$response["normalized_code"] = isset($validation["normalized_code"]) ? (string)$validation["normalized_code"] : "";
if(isset($validation["effects"]) && is_array($validation["effects"])){
    $response["effects"] = array_values(array_unique($validation["effects"]));
}

echo json_encode($response,JSON_UNESCAPED_UNICODE);
exit;
