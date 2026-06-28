<?php 
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/cart/session_start.php";
    if(!function_exists("cart_discount_code_validate_for_cart")){
        include_once $bu."modules/cart/cart_funcs.php";
    }
    $cfs = "cart_page";
    $preserve_checkout_state = false;
    $current_checkout_level = 0;
    if(isset($_SESSION[$cfs]) && is_object($_SESSION[$cfs]) && isset($_SESSION[$cfs]->level)){
        $current_checkout_level = (int)$_SESSION[$cfs]->level;
        if($current_checkout_level === 6){
            $preserve_checkout_state = true;
        }else if($current_checkout_level >= 7){
            // Keep final step only for immediate post-payment redirect (message/lock present).
            $has_checkout_notice = (isset($_SESSION["cart_notice"]) && trim((string)$_SESSION["cart_notice"]) !== "");
            if($has_checkout_notice){
                $preserve_checkout_state = true;
            }
        }
    }
    if(isset($_SESSION["cart_return_locked"]) && $_SESSION["cart_return_locked"]){
        $preserve_checkout_state = true;
    }
    if(isset($_SERVER["HTTP_REFERER"])){
        $origin =  $_SERVER["HTTP_REFERER"];
        $origin = substr($origin,0,strripos($origin,"/"));
        $origin = substr($origin,strripos($origin,"/")+1);
        if($origin != "cart" && !$preserve_checkout_state){
            $_SESSION[$cfs] = new where_u($cfs);
        }
    }else if(!isset($_SESSION[$cfs])){
        $_SESSION[$cfs] = new where_u($cfs);
    }
    if(!isset($_SESSION[$cfs]))$_SESSION[$cfs] = new where_u($cfs);
    if(
        !$preserve_checkout_state &&
        isset($_SESSION[$cfs]) &&
        is_object($_SESSION[$cfs]) &&
        isset($_SESSION[$cfs]->level) &&
        (int)$_SESSION[$cfs]->level >= 7
    ){
        $_SESSION[$cfs] = new where_u($cfs);
    }
    if(isset($_SESSION["cart_return_locked"])){
        unset($_SESSION["cart_return_locked"]);
    }
    if(
        isset($_SESSION[$cfs]) &&
        is_object($_SESSION[$cfs]) &&
        isset($_SESSION[$cfs]->level) &&
        (int)$_SESSION[$cfs]->level >= 7 &&
        isset($_SESSION["oid"]) &&
        var_exist((int)$_SESSION["oid"],"orders","id")
    ){
        $completed_oid = (int)$_SESSION["oid"];
        if((int)getVarFromDB("orders","state","id",$completed_oid) >= 1){
            $_SESSION["cart"] = new cart();
            $order_uid = (int)getVarFromDB("orders","uid","id",$completed_oid);
            if($order_uid > 0){
                cart_draft_clear_for_uid($order_uid);
                if(isset($_SESSION["logged"]) && isset($_SESSION["logged"]->uid) && (int)$_SESSION["logged"]->uid === $order_uid){
                    $_SESSION["cart_draft_restored_uid"] = $order_uid;
                }
            }else{
                cart_sync_draft_for_logged_user();
            }
            if(isset($_SESSION["admin_order_set"])) unset($_SESSION["admin_order_set"]);
        }
    }
    
    if($_SESSION[$cfs]->level > 0 && $_SESSION[$cfs]->level < 7)
        if(isset($_SESSION["cart"]) && $_SESSION["cart"]->number() > 0){}else{
            $_SESSION[$cfs]->level = 0;
            header("Location:$s"."cart/");
            die;
    }
    if($_SESSION[$cfs]->level > 2)
        if(isset($_SESSION["logged"]) || (isset($_SESSION["user"]) && $_SESSION["user"]->data()["error"][0] == 0)){}else{
            $_SESSION[$cfs]->level = 2;
            header("Location:$s"."cart/");
            die;
    }
    if($_SESSION[$cfs]->level > 3)
        if(isset($_SESSION["address"]) && $_SESSION["address"]->data()["error"][0] == 0){}else{
            $_SESSION[$cfs]->level = 3;
            header("Location:$s"."cart/");
            die;
    }
    if($_SESSION[$cfs]->level > 4)
        if($_SESSION["address"]->data()["county"] != "شهر تهران" || ($_SESSION["address"]->data()["county"] == "شهر تهران" && isset($_SESSION["send_date"]))){}else{
            $_SESSION[$cfs]->level = 4;
            header("Location:$s"."cart/");
            die;
    }
    if(isset($_POST["empty"])){
            $_SESSION["cart"] = new cart();
            cart_sync_draft_for_logged_user();
            $_SESSION[$cfs]->level = 0;
            header("Location:$s"."cart/");
            die;
    }
    if(isset($_POST["excess_submit"])){$_SESSION['shift3_excess'] = true;}
    
    if(isset($_POST['lksr_clear'])){
        $had_code = (isset($_SESSION['lksr']) && trim((string)$_SESSION['lksr']) !== "");
        if(isset($_SESSION['lksr'])) unset($_SESSION['lksr']);
        if(isset($_SESSION["cart_notice"])){
            unset($_SESSION["cart_notice"]);
        }
        $_SESSION["discount_code_notice"] = $had_code ? "Dکد تخفیف حذف شد." : "Dکد تخفیفی برای حذف وجود ندارد.";
    }
    if(isset($_POST['lksr_s'],$_POST['lksr'])){
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

        $validation = cart_discount_code_validate_for_cart(
            $_POST['lksr'],
            $mode,
            $uid,
            $cart_cost,
            $send_cost,
            $admin_state,
            $send_applicable
        );

        if(!empty($validation["ok"])){
            $_SESSION['lksr'] = (string)$validation["normalized_code"];
            if(isset($_SESSION["cart_notice"])){
                unset($_SESSION["cart_notice"]);
            }
            $msg = isset($validation["message"]) ? trim((string)$validation["message"]) : "";
            if($msg === ""){
                $msg = "کد تخفیف اعمال شد.";
            }
            $_SESSION["discount_code_notice"] = "D".$msg;
        }else{
            if(isset($_SESSION['lksr'])) unset($_SESSION['lksr']);
            $msg = isset($validation["message"]) ? trim((string)$validation["message"]) : "";
            if($msg === ""){
                $msg = "کد تخفیف معتبر نیست.";
            }
            if(isset($_SESSION["cart_notice"])){
                unset($_SESSION["cart_notice"]);
            }
            $_SESSION["discount_code_notice"] = "E".$msg;
        }
    }

    if(!function_exists("cart_has_verified_user_profile")){
        function cart_has_verified_user_profile(){
            if(isset($_SESSION["logged"])){
                return true;
            }
            if(isset($_SESSION["user"]) && is_object($_SESSION["user"])){
                $user_data = $_SESSION["user"]->data();
                if(is_array($user_data) && isset($user_data["error"][0]) && (int)$user_data["error"][0] === 0){
                    return true;
                }
            }
            return false;
        }
    }
    if(!function_exists("cart_step_title")){
        function cart_step_title($level){
            switch((int)$level){
                case 0: return "سبد خرید";
                case 1: return "ورود یا تایید شماره";
                case 2: return "اطلاعات خریدار";
                case 3: return "اطلاعات گیرنده";
                case 4: return "زمان/روش ارسال";
                case 5: return "روش پرداخت";
            }
            return "مرحله بعد";
        }
    }
    if(!function_exists("cart_step_next_level")){
        function cart_step_next_level($level = null){
            global $cfs;
            if($level === null && isset($_SESSION[$cfs])){
                $level = (int)$_SESSION[$cfs]->level;
            }else{
                $level = (int)$level;
            }
            switch($level){
                case 0:
                    return cart_has_verified_user_profile() ? 3 : 1;
                case 1:
                    return 2;
                case 2:
                    return 3;
                case 3:
                    if(isset($_SESSION["address"]) && is_object($_SESSION["address"])){
                        $address_data = $_SESSION["address"]->data();
                        $county = trim((string)($address_data["county"] ?? ""));
                        if($county !== "شهر تهران"){
                            return 5;
                        }
                    }
                    return 4;
                case 4:
                    return 5;
                case 5:
                    return 6;
            }
            return max($level + 1,1);
        }
    }
    if(!function_exists("cart_step_prev_level")){
        function cart_step_prev_level($level = null){
            global $cfs;
            if($level === null && isset($_SESSION[$cfs])){
                $level = (int)$_SESSION[$cfs]->level;
            }else{
                $level = (int)$level;
            }
            switch($level){
                case 0:
                    return 0;
                case 1:
                    return 0;
                case 2:
                    return 1;
                case 3:
                    return cart_has_verified_user_profile() ? 0 : 2;
                case 4:
                    return 3;
                case 5:
                    if(isset($_SESSION["address"]) && is_object($_SESSION["address"])){
                        $address_data = $_SESSION["address"]->data();
                        $county = trim((string)($address_data["county"] ?? ""));
                        if($county !== "شهر تهران"){
                            return 3;
                        }
                    }
                    return 4;
            }
            return max($level - 1,0);
        }
    }
    if(!function_exists("cart_step_next_text")){
        function cart_step_next_text($level = null){
            $next_level = cart_step_next_level($level);
            if($next_level === 6){
                return "ادامه: پرداخت و ثبت سفارش";
            }
            return "ادامه: ".cart_step_title($next_level);
        }
    }
    if(!function_exists("cart_step_back_text")){
        function cart_step_back_text($level = null){
            $prev_level = cart_step_prev_level($level);
            if($prev_level === 0){
                return "بازگشت: سبد خرید";
            }
            return "بازگشت: ".cart_step_title($prev_level);
        }
    }
    if(!function_exists("cart_step_back_level")){
        function cart_step_back_level($level = null){
            return cart_step_prev_level($level);
        }
    }
    
    if(isset($_POST["submit"])){
        switch($_SESSION[$cfs]->level){
            case 0:
                $_SESSION[$cfs]->level++;
                if(isset($_SESSION["logged"])){
                    $_SESSION[$cfs]->level = 3;
                }else{
                    if(isset($_SESSION["user"]) && $_SESSION["user"]->data()["error"][0] == 0){
                        $_SESSION[$cfs]->level = 3;
                    }
                }
                break;
            case 1:
                include $bu."modules/cart/rec_code.php";
                
                if(isset($result) && $result == "ok"){
                    $_SESSION[$cfs]->level++;
                }
                break;
            case 2:
                include $bu."modules/cart/add_user_new.php";
                if($result == "ok"){
                    $_SESSION[$cfs]->level++;
                }
                break;
            case 3:
                include $bu."modules/cart/add_address.php";
                if($result == "ok"){
                    $_SESSION[$cfs]->level++;
                    if($_SESSION["address"]->data()["county"] != "شهر تهران"){
//                        $_SESSION[$cfs]->level=5;
                    // }else{
                    // if($_SESSION["address"]->data()["county"] == "استان تهران"){
                        // if($_SESSION["cart"]->has_category("پکیج ولنتاین") > 0){
                            
                        // }else{
                        unset($_SESSION["send_date"]);
                        $_SESSION["send_date"] = new send_date('post',2,0);
                        $_SESSION[$cfs]->level=5;
                        // }
                    }
                }
                break;
            case 4:
                include $bu."modules/cart/add_send_date.php";
                if($result == "ok"){
                    $_SESSION[$cfs]->level=5;
                }
                break;
            case 5:
                include $bu."modules/cart/submit_order.php";
                if($result == "ok"){
                    $is_admin_manual_pay = (
                        isset($_SESSION["a_logged"]) &&
                        isset($_POST["admin_manual_pay"]) &&
                        $_POST["admin_manual_pay"] == "1"
                    );
                    if($is_admin_manual_pay){
                        $day_id=0;
                        if(isset($_SESSION["send_date"]) && var_exist($_SESSION["send_date"]->sdid,"send_date","id")){
                            $day_id=$_SESSION["send_date"]->sdid;
                        }
//                        db_pay_order($_SESSION["oid"],"پرداخت نقدی",$day_id);
                        $_SESSION[$cfs]->level=6;
                        $_SESSION["cart_notice"] = "Dسفارش شما با موفقیت ثبت شد.<br>
                        شناسه پرداخت: پرداخت نقدی<br>";
                    }else{
//                        include $bu."modules/cart/send_for_pep.php";
                        // Header("Location:$s"."cart/send_for_pep.php");

                        $payment_method = "zarinpal";
                        if(isset($_POST["payment_method"]) && check_value("text",$_POST["payment_method"])){
                            $payment_method = $_POST["payment_method"];
                        }
                        $_SESSION["payment_method"] = $payment_method;

                        if($payment_method === "snappay"){
                            require_once($bu."modules/snappay/snappay_config.php");
                            require_once($bu."modules/snappay/snappay_runtime.php");
                            if(snappay_checkout_enabled()){
                                include $bu."modules/cart/request_snappay.php";
                            }else{
                                $_SESSION['error'] = "Eروش پرداخت اقساطی اسنپ‌پی در حال حاضر غیرفعال است.";
                                header("Location:$s"."cart/");
                                die;
                            }
                        }else{
                            include $bu."modules/cart/request_zarinpal.php";
                        }

                        die;
                    }
                }
                break;
            case 6:
                if(isset($_SESSION["a_logged"],$_SESSION["oid"])){
                     $_SESSION["cart_notice"] = '';
                    if(isset($_POST['pay_code'])){
                        updateInDB('orders','pay_id',check_value("text",$_POST['pay_code']),'id',$_SESSION['oid']);
                        $_SESSION["cart_notice"] = "Dکد رهگیری پرداخت ثبت شد.<br>
                        ";
                    }
                    if(isset($_POST['pay_card'])){
                        updateInDB('orders','pay_request_auth',check_value("text",$_POST['pay_card']),'id',$_SESSION['oid']);
                        if($_SESSION["cart_notice"] == '')$_SESSION["cart_notice"] = "D";
                        $_SESSION["cart_notice"] .= "4 رقم آخر شماره کارت ثبت شد.<br>
                        ";
                        
                    }
                    $pay_code = false;
                    $pay_card = false;
                    $pay_code_raw = "";
                    $pay_card_raw = "";
                    if(isset($_POST['pay_code'])){
                        $pay_code_raw = trim((string)$_POST['pay_code']);
                        $pay_code = check_value("text",$_POST['pay_code']);
                    }
                    if(isset($_POST['pay_card'])){
                        $pay_card_raw = trim((string)$_POST['pay_card']);
                        $pay_card = check_value("text",$_POST['pay_card']);
                    }
                    $has_manual_info = ($pay_code_raw !== "" || $pay_card_raw !== "");
                    if($has_manual_info){
                        $day_id = 0;
                        if(isset($_SESSION["send_date"]) && var_exist($_SESSION["send_date"]->sdid,"send_date","id")){
                            $day_id = $_SESSION["send_date"]->sdid;
                        }
                        // Manual admin payment must still finalize order state for dashboards/processing.
                        $manual_pay_id = $pay_code ? $pay_code : "manual-admin";
                        db_pay_order($_SESSION["oid"],$manual_pay_id,$day_id);
                        updateInDB('orders','payment_date',date("Y-m-d H:i:s"),'id',$_SESSION['oid']);
                        // Checkout completed: clear basket and reset one-order admin guard.
                        $_SESSION["cart"] = new cart();
                        $order_uid = (int)getVarFromDB("orders","uid","id",$_SESSION["oid"]);
                        if($order_uid > 0){
                            cart_draft_clear_for_uid($order_uid);
                        }else{
                            cart_sync_draft_for_logged_user();
                        }
                        if(isset($_SESSION["admin_order_set"])) unset($_SESSION["admin_order_set"]);
                    }
                    $_SESSION["payment_method"] = "manual";
                }
                $_SESSION[$cfs]->level = 7;
                break;
        }
        header("Location:$s"."cart/");
        die;
    }else
        
    if(isset($_POST["clear"])){
        switch($_POST["clear"]){
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
//            case '6':
                if($_SESSION[$cfs]->level > $_POST["clear"])
                $_SESSION[$cfs]->level = $_POST["clear"];
                break;
        }
        header("Location:$s"."cart/");
        die;
    }
    else{
?>

<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" class="" lang="en">


<?php
    include $bu."modules/main/head.php";
?>

<body>

<?php  include $bu."modules/main/header.php"; ?>
<style type="text/css">
    .checkout_flow_page{
        max-width:1240px;
        margin:0 auto;
    }
    .checkout_flow_header{
        border:1px solid rgba(0,0,0,0.12);
        border-radius:18px;
        background:linear-gradient(135deg,#fff 0%,#f7f2ef 100%);
        padding:10px 14px;
        margin-bottom:12px;
    }
    .checkout_flow_kicker{
        margin:0 0 2px 0;
        font-size:22px;
        line-height:1.15;
        font-weight:800;
        color:#4a2d22;
        text-align:right;
    }
    .checkout_flow_title{
        margin:0;
        font-size:14px;
        line-height:1.35;
        font-weight:600;
        color:#8a6d5d;
        text-align:right;
    }
    .checkout_flow_panel{
        border:1px solid rgba(0,0,0,0.12);
        border-radius:14px;
        background:#fff;
        padding:14px 16px;
        box-sizing:border-box;
    }
    .checkout_flow_panel + .checkout_flow_panel{
        margin-top:14px;
    }
    .checkout_flow_panel_title{
        margin:0 0 10px 0;
        font-size:16px;
        line-height:1.3;
        font-weight:700;
        color:#2c201b;
        text-align:right;
    }
    .checkout_flow_panel_text{
        margin:0;
        line-height:1.9;
        color:#4f433e;
    }
    .checkout_flow_table_wrap{
        overflow:auto;
    }
    .checkout_flow_panel table.cart{
        width:100%;
        margin:0;
    }
    .checkout_flow_panel table.cart thead th{
        padding-top:8px;
        padding-bottom:8px;
        font-size:12px;
        line-height:1.3;
        font-weight:700;
    }
    .checkout_flow_panel table.cart tbody td{
        padding-top:10px;
        padding-bottom:10px;
        vertical-align:middle;
    }
    .checkout_flow_actions{
        display:flex;
        justify-content:center;
        align-items:stretch;
        gap:10px;
        flex-wrap:wrap;
    }
    .checkout_flow_actions .btn,
    .checkout_flow_actions input.btn,
    .checkout_flow_actions button.btn{
        min-width:220px;
        margin:0 !important;
        text-align:center;
        box-sizing:border-box;
    }
    .checkout_flow_split{
        display:grid;
        grid-template-columns:minmax(0,1fr);
        gap:18px;
    }
    .checkout_flow_summary_col,
    .checkout_flow_side_col{
        min-width:0;
    }
    .checkout_flow_note{
        margin-bottom:12px;
    }
    .checkout_nav_row{
        display:flex;
        justify-content:center;
        align-items:center;
        gap:10px;
        flex-wrap:wrap;
        margin-top:14px;
    }
    .checkout_nav_row .btn{
        min-width:220px;
        margin:0 !important;
        text-align:center;
        box-sizing:border-box;
    }
    @media (min-width:1100px){
        .checkout_flow_split{
            grid-template-columns:minmax(0,1fr) 420px;
            align-items:start;
        }
        .checkout_flow_side_col.is-sticky{
            position:sticky;
            top:16px;
            align-self:start;
        }
    }
    @media (max-width:640px){
        .checkout_flow_header{
            padding:10px 12px;
        }
        .checkout_flow_kicker{
            font-size:18px;
        }
        .checkout_flow_title{
            font-size:13px;
        }
        .checkout_flow_actions .btn,
        .checkout_flow_actions input.btn,
        .checkout_flow_actions button.btn,
        .checkout_nav_row .btn{
            min-width:0;
            width:100%;
            max-width:100%;
        }
    }
</style>
<?php
       
?>

<?php
    include $bu."$module_name/".$_SESSION[$cfs]->level.".php";
?>

<?php  include $bu."modules/main/footer.php"; ?>
<script type="text/javascript">
function sub_show(key,value){
    var form = document.createElement("form");
    var input = document.createElement("input");
    
    form.method = "POST";
    form.action = "<?php echo $URL; ?>";
    
    input.value = value;
    input.name = key;
    form.appendChild(input);
    
    document.body.appendChild(form);
    form.submit();
}
</script>
</body>
</html>
<?php
    }
?>
