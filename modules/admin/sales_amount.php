<?php

$indexed = 1;
include_once($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
include_once $bu."modules/cart/cart_funcs.php";

if(!isset($data) || !is_array($data)){
    $data = array();
}

$allowed_types = array("percent","amount","fixed_price","send","gift");
$type = "";
if(isset($data["type"])){
    $type = cart_sale_normalize_type($data["type"]);
}
if(isset($_POST["sale_type"])){
    $requested_type = cart_sale_normalize_type($_POST["sale_type"]);
    if(in_array($requested_type,$allowed_types,true)){
        $type = $requested_type;
    }
}
if(!in_array($type,$allowed_types,true)){
    $type = "amount";
}
$data["type"] = $type;

$amount_value = "";
if(isset($data["amount"])){
    $amount_value = (string)$data["amount"];
}
if(isset($_POST["amount"]) && $_POST["amount"] !== ""){
    $amount_value = (string)$_POST["amount"];
}

if($type === "gift"){
    $st = $mysqli->prepare("SELECT id,name FROM products WHERE type='pack' AND del_flag=0 ORDER BY name ASC");
    if(!$st || !$st->execute()){
        echo '<div class="user_hint error">خطا در دریافت لیست محصولات هدیه.</div>';
        return;
    }
    $res = $st->get_result();
    $has_item = false;
    echo '<label for="sale_amount">مقدار:</label>';
    echo '<select id="sale_amount" name="amount">';
    while($row = $res->fetch_assoc()){
        $has_item = true;
        $selected = "";
        if($amount_value !== "" && (string)$amount_value === (string)$row["id"]){
            $selected = " selected ";
        }
        $min_weight = (string)getVarFromDB('products_price','weight','pid',$row['id'],'start_time DESC');
        $min_weight_arr = explode(',',$min_weight);
        $min_weight = isset($min_weight_arr[0]) ? trim((string)$min_weight_arr[0]) : "";
        $option_text = (string)$row["name"];
        if($min_weight !== ""){
            $option_text .= ' ('.$min_weight.'گرمی)';
        }
        echo '<option value="'.(int)$row["id"].'"'.$selected.'>'.htmlspecialchars($option_text,ENT_QUOTES,'UTF-8').'</option>';
    }
    echo '</select>';
    if(!$has_item){
        echo '<div class="user_hint error">هیچ محصول فعالی برای هدیه یافت نشد.</div>';
    }else{
        echo '<div class="user_hint">برای هدیه، یک محصول از لیست انتخاب کنید.</div>';
    }
    return;
}

if($type === "send"){
    echo '<label for="sale_amount">مقدار:</label>';
    echo '<input id="sale_amount" name="amount" type="text" value="0" readonly>';
    echo '<div class="user_hint">برای ارسال رایگان مقدار به صورت خودکار صفر ثبت می‌شود.</div>';
    return;
}

$placeholder = "";
$hint = "";
switch($type){
    case "percent":
        $placeholder = "مثال: 10";
        $hint = "عدد درصد تخفیف را وارد کنید (بین 1 تا 100).";
        break;
    case "fixed_price":
        $placeholder = "مثال: 350000";
        $hint = "قیمت نهایی سبد را به تومان وارد کنید.";
        break;
    default:
        $placeholder = "مثال: 50000";
        $hint = "مبلغ تخفیف را به تومان وارد کنید.";
        break;
}

echo '<label for="sale_amount">مقدار:</label>';
echo '<input id="sale_amount" name="amount" type="text" value="'.htmlspecialchars($amount_value,ENT_QUOTES,'UTF-8').'" placeholder="'.htmlspecialchars($placeholder,ENT_QUOTES,'UTF-8').'">';
echo '<div class="user_hint">'.htmlspecialchars($hint,ENT_QUOTES,'UTF-8').'</div>';

?>
