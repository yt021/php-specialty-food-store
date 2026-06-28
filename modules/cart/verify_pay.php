<?php 

$indexed = 1;
include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
include $bu."modules/cart/session_start.php";
if(!EXTERNAL_INTEGRATIONS_ENABLED) die("Payment disabled in showcase mode");

$MerchantID     = "disabled";
$url = "https://example.invalid/disabled";

$ch = curl_init(); 
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,
            "merchant_id=$MerchantID");
            
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$server_output = curl_exec($ch);

curl_close ($ch);

$authos = json_decode($server_output)->data->authorities;

foreach($authos as $auth){
    var_dump($auth);
    // echo $auth->authority."<br>";
    // echo $auth->amount."<br>";
    // echo $auth->date."<br>";
    
    $oid = getVarFromDB('orders','id','pay_request_auth',$auth->authority);
    $state = getVarFromDB('orders','state','id',$oid);
    $price = getVarFromDB('orders','pay_price','id',$oid);
    if($price == $auth->amount/10 && $state < 1){
    // if($state < 1){
        // echo "$oid - here";
        updateInDB('orders','state','1','id',$oid);
        updateInDB('orders','payment_date',$auth->date,'id',$oid);
    }
    echo "<br><br>";
}




?>
