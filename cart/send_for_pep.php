<?php
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    if(!EXTERNAL_INTEGRATIONS_ENABLED) die("Payment disabled in showcase mode");
    include $bu."modules/cart/session_start.php";
?>
<?php
    error_reporting(0);
    $site = 'http://localhost';
    $redirectAddress = "$site/cart/pay_done.php";
    //IPG data
    include $bu.'modules/pep/pep_config.php';
    $action = "1003";       //برای خرید
    
    
    //Order data
    $oid = $_SESSION["oid"];
    $invoiceNumber = $oid;
    $invoiceDate = getVarFromDB("orders","create_date","id",$oid);
    $amount = 10*(int)getVarFromDB("orders","pay_price","id",$oid);     //IRI Rials
    
    $timestamp = date("Y/m/d H:i:s");
    
    // Sign: Step 1: Create String
    $sign_str = "#".$merchant_code."#".$terminal_code."#".$invoiceNumber."#".$invoiceDate."#".$amount."#".$redirectAddress."#".$action."#".$timestamp."#"; 
    
    // Sign: Step 2: SHA1 Hashing
//    $hashed_sign = sha1($sign_str,true);
    // Sign: Step 3: Sign data with Private Key
//    require_once($bu."modules/pep/RSAProcessor.class.php");
//    $processor = new RSAProcessor($bu."modules/pep/key.xml",RSAKeyType::XMLFile);
//    $signed_str =  $processor->sign($hashed_sign);
    // Sign: Step 4: Base64 Encoding
//    $sign = base64_encode($signed_str);
    
    $sign = pep_sign_data($sign_str);
    
?>
<body onload="document.forms['pay'].submit();">
<style>form{display:none;}</style>
<form name="pay" id="Form2" method="post" Action="https://pep.shaparak.ir/gateway.aspx" >
    <input type="hidden" name="invoiceNumber" value="<?php echo $invoiceNumber; ?>" />
    <input type="hidden" name="invoiceDate" value="<?php echo $invoiceDate; ?>" />
    <input type="hidden" name="amount" value="<?php echo $amount; ?>" />
    <input type="hidden" name="terminalCode" value="<?php echo $terminal_code; ?>" />
    <input type="hidden" name="merchantCode" value="<?php echo $merchant_code; ?>" />
    <input type="hidden" name="redirectAddress" value="<?php echo $redirectAddress; ?>" />
    <input type="hidden" name="timeStamp" value="<?php echo $timestamp; ?>" />
    <input type="hidden" name="action" value="<?php echo $action; ?>" />
    <input type="hidden" name="sign" value="<?php echo $sign; ?>" />
</form>
</body>
