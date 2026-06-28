<?php 
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/cart/session_start.php";

require_once($bu."modules/zarinpal_function.php");
$tb = 'orders';
$MerchantID     = "disabled";
$Amount         = getVarFromDB($tb,'pay_price','id',$_SESSION['oid']);
$ZarinGate         = false;
$SandBox         = false;
// if(getVarFromDB('orders','uid','id',$_SESSION['oid']) == 4228)$Amount = 2000;


$zp     = new zarinpal();
$result = $zp->verify($MerchantID, $Amount, $SandBox, $ZarinGate);

if (isset($result["Status"]) && $result["Status"] == 100)
{
    if(getVarFromDB($tb,'pay_request_auth','id',$_SESSION['oid']) == $_GET['Authority']){
        
        
        updateInDB($tb,'pay_ref_no',$result["RefID"],'id',$_SESSION['oid']);
        updateInDB($tb,'payment_date',date("Y-m-d H:i:s",time()),'id',$_SESSION['oid']);
        updateInDB($tb,'state',1,'id',$_SESSION['oid']);
        include_once $bu."modules/wdb/log_funcs.php";
        $uid = getVarFromDB($tb,'uid','id',$_SESSION['oid']);
        $name = getVarFromDB('users','name','id',$uid);
        $tel = getVarFromDB('users','tel','id',$uid);
        send_sms_temp($tel,'Sabt',$name,$_SESSION['oid']);
        $_SESSION["cart"] = new cart();
        cart_draft_clear_for_uid($uid);
        if(isset($_SESSION["admin_order_set"])) unset($_SESSION["admin_order_set"]);
        $_SESSION["cart_notice"] = "Dپرداخت با موفقیت انجام شد.<br />مبلغ: ".(int)$result["Amount"]." تومان<br />کد پیگیری: ".(string)$result["RefID"]."<br />شناسه سفارش: ".(int)$_SESSION["oid"];
        if(!isset($_SESSION["cart_page"]) || !is_object($_SESSION["cart_page"])){
            $_SESSION["cart_page"] = new where_u("cart_page");
        }
        $_SESSION["cart_page"]->level = 7;
        header("Location:".$s."cart/");
        die;
    }else{
        $_SESSION['error'] = 'Eشناسه تراکنش با شناسه سفارش ثبت شده همخوانی ندارد، مجددا اقدام نمایید.';
    }
} else {
    // error
    $_SESSION['error'] = "Eپرداخت ناموفق";
    $_SESSION['error'] .=  "<br />کد خطا : ". $result["Status"];
    $_SESSION['error'] .=  "<br />تفسیر و علت خطا : ". $result["Message"];
}

?>


<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" class="" lang="en">


<?php
    include $bu."modules/main/head.php";
?>

<body>

<?php  include $bu."modules/main/header.php"; ?>

<main>
    <?php
        if(isset($_SESSION["error"])){
            $error = $_SESSION["error"];
            unset($_SESSION["error"]);
        }
    ?>
    <div class="content checkout_flow_page">
        <style type="text/css">
            .payment_result_header{border:1px solid rgba(0,0,0,0.12);border-radius:18px;background:linear-gradient(135deg,#fff 0%,#f7f2ef 100%);padding:10px 14px;margin-bottom:12px;}
            .payment_result_kicker{margin:0 0 2px 0;font-size:22px;line-height:1.15;font-weight:800;color:#4a2d22;text-align:right;}
            .payment_result_title{margin:0;font-size:14px;line-height:1.35;font-weight:600;color:#8a6d5d;text-align:right;}
            .payment_result_panel{border:1px solid rgba(0,0,0,0.12);border-radius:14px;background:#fff;padding:14px 16px;box-sizing:border-box;}
            .payment_result_actions{display:flex;justify-content:center;gap:10px;flex-wrap:wrap;margin-top:14px;}
            .payment_result_actions .btn{min-width:220px;margin:0 !important;text-align:center;box-sizing:border-box;}
            @media (max-width:640px){.payment_result_header{padding:10px 12px;}.payment_result_kicker{font-size:18px;}.payment_result_title{font-size:13px;}.payment_result_actions .btn{min-width:0;width:min(360px,100%);}}
        </style>
        <div class="payment_result_header">
            <h1 class="payment_result_kicker">نتیجه پرداخت</h1>
            <p class="payment_result_title">وضعیت تراکنش بانکی و دسترسی به اقدام بعدی</p>
        </div>
        <div class="payment_result_panel">
        <div id="user_error" class="user_hint <?php if(isset($error) && $error[0] ==  'E')echo "error"; ?> <?php if(!isset($error))echo "hide"; ?>">
            <?php if(isset($error))echo substr($error,1); ?>
        </div>
        <div class="payment_result_actions">
        <?php
            if(isset($error) && $error[0] ==  'D'){
        ?>
            <a class="btn" href="<?php echo $s; ?>account/">حساب کاربری </a>
        <?php    
            }else{
        ?>
            <a class="btn" href="<?php echo $s; ?>cart/">اقدام مجدد</a>
        <?php        
            }
        ?>
        </div>
        </div>
    </div>
</main>
<?php  include $bu."modules/main/footer.php"; ?>
</body>
</html>
