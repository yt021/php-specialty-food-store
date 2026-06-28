<?php
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/cart/session_start.php";   
    
    if(!defined('EXTERNAL_INTEGRATIONS_ENABLED') || !EXTERNAL_INTEGRATIONS_ENABLED) die("SMS disabled in showcase mode");
    $sms_pass = 'not-configured';
    
    $limit = 100;
    
    $message = '/param/ عزیز؛ 
15 درصد تخفیف ویژه مشتریان عزیز میوه خشک آبان!
کد تخفیف شما: 10345 
مهلت استفاده: 23 تیر ماه
abanfruit.com';

    $url = 'https://example.invalid/disabled';
    
    $st = "SELECT count(id) as cid FROM users ";
    $st = $mysqli->prepare($st);
    $st->execute();
    $res = $st->get_result();
    
    $row = $res->fetch_assoc()['cid'];
    
    $turns = (int)floor($row / $limit);
    // $turns = 5;
    for($i = 104;$i<$turns;$i++){
        // $i=0;
        $param = array(
            'sms_pass'=>$sms_pass,
            'message'=>$message,
            'start'=>$i
        );
        
        $handler = curl_init($url);             
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $param);                       
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response2 = curl_exec($handler);
    }
    echo 'done';
    die;
    
?>
