<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php


require_once($bu."modules/zarinpal_function.php");
$MerchantID     = "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx";
$MerchantID     = "disabled";
$Amount         = 100000000;
$Amount         = getVarFromDB('orders','pay_price','id',$_SESSION['oid']);

// if(getVarFromDB('orders','uid','id',$_SESSION['oid']) == 4228)$Amount = 2000;


$Description     = "خرید میوه خشک - میوه خشک آبان";
$Email             = "";
$Mobile         = "";
$CallbackURL     = "https://abanfruit.com/cart/verify.php";
$ZarinGate         = false;
$SandBox         = false;

$zp     = new zarinpal();
$result = $zp->request($MerchantID, $Amount, $Description, $Email, $Mobile, $CallbackURL, $SandBox, $ZarinGate);

if (isset($result["Status"]) && $result["Status"] == 100)
{
    updateInDB('orders','pay_request_auth',$result['Authority'],'id',$_SESSION['oid']);
    // Success and redirect to pay
    $zp->redirect($result["StartPay"]);
    die();
} else {
    // error
    $_SESSION['error'] = 'Eخطا در ایجاد تراکنش - لطفا مجددا اقدام نمایید.';
//    echo "خطا در ایجاد تراکنش";
//    echo "<br />کد خطا : ". $result["Status"];
    $_SESSION['error'] .= "<br />تفسیر و علت خطا : ". $result["Message"];
}

?>
<?php
        }
    }
?>
