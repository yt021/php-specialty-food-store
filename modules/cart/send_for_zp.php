<?php
if(!defined('EXTERNAL_INTEGRATIONS_ENABLED') || !EXTERNAL_INTEGRATIONS_ENABLED) {
    die("Payment disabled in showcase mode");
}

include_once $bu."modules/nusoap/nusoap.php";
$oid = $_SESSION["oid"];
$MerchantID = 'disabled'; // Showcase placeholder
$Amount = $_SESSION["pay_price"]; //Amount will be based on Toman - Required
$Amount = (int)getVarFromDB("orders","pay_price","id",$oid);     //IRI


$Description = 'خرید میوه خشک - میوه خشک آبان'; // Required
// $Email = 'UserEmail@Mail.Com'; // Optional
// $Mobile = '09000000000'; // Example only
//         'Email'          => $Email,
//         'Mobile'         => $Mobile,
$site = 'https://abanfruit.com';
$CallbackURL = "$site/cart/pay_don.php"; // Required


$client = new nusoap_client('https://www.zarinpal.com/pg/services/WebGate/wsdl', 'wsdl');
$client->soap_defencoding = 'UTF-8';

$result = $client->call('PaymentRequest', [
    [
        'MerchantID'     => $MerchantID,
        'Amount'         => $Amount,

        'Description'    => $Description,
        'CallbackURL'    => $CallbackURL,
    ],
]);

//Redirect to URL You can do it also by creating a form
if($result['Status'] == 100){
    updateInDB("orders","pay_request_auth",$result['Authority'],"id",$oid);
    Header('Location: https://www.zarinpal.com/pg/StartPay/'.$result['Authority'].'/ZarinGate');
    die;
//برای استفاده از زرین گیت باید ادرس به صورت زیر تغییر کند:
//Header('Location: https://www.zarinpal.com/pg/StartPay/'.$result->Authority.'/ZarinGate');
}else{
    db_new_zp_error($_SESSION["oid"],$result['Status']);
    $_SESSION["cart_notice"] = "Eخطایی رخ داده است، لطفا مجددا اقدام نمایید.";
}

?>
