<?php
    error_reporting(0);
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/cart/session_start.php";
    
    include $bu.'modules/pep/pep_config.php';
    $error = true;
    $old = false;
    if(isset($_GET) && isset($_GET['iN']) && isset($_GET['iD']) && isset($_GET['tref'])){
        // data from IPG Response
        $data = array(
            "invoiceNumber"=>$_GET['iN'],
            "invoiceDate"=>$_GET['iD'],
            "tRefID"=>$_GET['tref'],
        );
        // data from database
        $oid = $data["invoiceNumber"];
        if(var_exist($oid,"orders","id")){
            if((int)getVarFromDB("orders","state","id",$oid) > 0){
                $old = True;
            }else{
                $invoiceDate = getVarFromDB("orders","create_date","id",$oid);
                $amount = 10*(int)getVarFromDB("orders","pay_price","id",$oid);
                
                // data from IPG back check
                $fields = array('invoiceUID' => $_GET['tref'] );
                $result = pep_post_req($fields,$ctr_url);
                $bcd = pep_xmlres_parser($result)["resultObj"];     //back check data
                // evaluating pay status
                if($bcd["result"] == "True"){
                    // evaluating first hand data consistency
                    if($oid == $bcd["invoiceNumber"] && 
                    $invoiceDate == $data["invoiceDate"] && 
                    $data["invoiceDate"] == $bcd["invoiceDate"]){
                        //evaluating correct request
                        if($bcd["action"] == "1003" && 
                        $bcd["merchantCode"] == $merchant_code && 
                        $bcd["terminalCode"] == $terminal_code){
                            // evaluating amount consistency
                            if($bcd["amount"] == $amount){
                                // increasing send date no of orders (for check max limit)
                                $day_id=0;
                                if(isset($_SESSION["send_date"]) && var_exist($_SESSION["send_date"]->sdid,"send_date","id")){
                                    $day_id=$_SESSION["send_date"]->sdid;
                                }
                                $tref_id = $bcd['transactionReferenceID'];
                                $trace_no = $bcd['traceNumber'];
                                $ref_no = $bcd['referenceNumber'];
                                
                                $pep_verify_res = pep_verifyPayment($bcd);
                                if($pep_verify_res["result"] == "True"){
                                    if(db_pay_order_pep($oid,$tref_id,$trace_no,$ref_no,$day_id)){
                                        $page  = 7;
                                        if(getVarFromDB("orders","recieve_shift","id",$oid) == 3){
                                            if(getVarFromDB("orders","recieve_shift","id",$oid) == 3){
                                                $tel = getVarFromDB('users',"tel","id",getVarFromDB('orders',"uid","id",$oid));
                                                send_sms_temp('09000000000','peykorder',$oid,$tel);
                                                send_sms_temp('09000000000','peykorder',$oid,$tel);
                                            }
                                        }
                                        $_SESSION["cart_notice"] = 
                                            "Dبا تشکر از خرید شما<br>".
                                            "سفارش شما با موفقیت ثبت شد.<br>".
                                            "کد سفارش: ".$oid."<br>".
                                            "<b>اطلاعات پرداخت:</b><br>".
                                            "شماره تراکنش: ".$tref_id."<br>".
                                            "شماره پیگیری: ".$trace_no."<br>".
                                            "شماره ارجاع: ".$ref_no."<br>"
                                            ;
                                        $_SESSION["cart"] = new cart();
                                        cart_draft_clear_for_uid((int)getVarFromDB("orders","uid","id",$oid));
                                        
                                        $error = false;
                                    }else{
                                        db_new_pep_error($oid,"verification_failed",$pep_verify_res["resultMessage"]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    if($error){
        if(isset($_SESSION["oid"])){
            $oid = $_SESSION["oid"];
            if(var_exist($oid,"orders","id")){
                if((int)getVarFromDB("orders","state","id",$oid) > 0){
                    $old = True;
                }else{
                    $fields["invoiceNumber"] = $oid;
                    $fields["invoiceDate"] = getVarFromDB("orders","create_date","id",$oid);
                    $fields["merchantCode"] = $merchant_code;
                    $fields["terminalCode"] = $terminal_code;
                    
                    $result = pep_post_req($fields,$ctr_url);
                    $bcd = pep_xmlres_parser($result)["resultObj"];     //back check data
                    // evaluating pay status
                    if($bcd["result"] == "True"){
                        //evaluating correct request
                        if($bcd["action"] == "1003"){
                            $amount = 10*(int)getVarFromDB("orders","pay_price","id",$oid);
                            // evaluating amount consistency
                            if($bcd["amount"] == $amount){
                                // increasing send date no of orders (for check max limit)
                                $day_id=0;
                                if(isset($_SESSION["send_date"]) && var_exist($_SESSION["send_date"]->sdid,"send_date","id")){
                                    $day_id=$_SESSION["send_date"]->sdid;
                                }
                                
                                $tref_id = $bcd['transactionReferenceID'];
                                $trace_no = $bcd['traceNumber'];
                                $ref_no = $bcd['referenceNumber'];
                                
                                $pep_verify_res = pep_verifyPayment($bcd);
                                if($pep_verify_res["result"] == "True"){
                                    if(db_pay_order_pep($oid,$tref_id,$trace_no,$ref_no,$day_id)){
                                        $page  = 7;
                                        if((int)getVarFromDB("orders","recieve_shift","id",$oid) == 3){
                                            $tel = getVarFromDB('users',"tel","id",getVarFromDB('orders',"uid","id",$oid));
                                            send_sms_temp('09000000000','peykorder',$oid,$tel);
                                            send_sms_temp('09000000000','peykorder',$oid,$tel);
                                        }
                                        $_SESSION["cart_notice"] = 
                                            "Dبا تشکر از خرید شما<br>".
                                            "سفارش شما با موفقیت ثبت شد.<br>".
                                            "کد سفارش: ".$oid."<br>".
                                            "<b>اطلاعات پرداخت:</b><br>".
                                            "شماره تراکنش: ".$tref_id."<br>".
                                            "شماره پیگیری: ".$trace_no."<br>".
                                            "شماره ارجاع: ".$ref_no."<br>"
                                            ;
                                        $_SESSION["cart"] = new cart();
                                        cart_draft_clear_for_uid((int)getVarFromDB("orders","uid","id",$oid));
                                        
                                        $error = false;
                                    }else{
                                        db_new_pep_error($oid,"verification_failed",$pep_verify_res["resultMessage"]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    if($error){
        $page  = 6;
        if(!isset($oid))$oid="";
        if(!isset($data))$data="";
        if(!isset($bcd))$bcd="";
        
        if($old){
            $_SESSION["cart_notice"] = "E
            تأیید پرداخت سفارش قبلا انجام شده است. برای پیگیری وضعیت سفارش به حساب کاربری خود مراجعه نمایید.<br>";
            $data="old_order";
        }else{
            $_SESSION["cart_notice"] = "Eخطایی رخ داده است، لطفا مجددا اقدام نمایید.<br>";
        }
        db_new_pep_error($oid,$data,$bcd);
    }
?>

<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" class="" lang="en">


<?php
    include $bu."modules/main/head.php";
?>

<body>

<?php  include $bu."modules/main/header.php"; ?>
<?php
       
?>

<?php
    include $bu."$module_name/$page.php";
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
