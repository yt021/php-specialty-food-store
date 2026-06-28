<?php 
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/cart/session_start.php";   
    
    
    
    $limit = 100;
    $start = $_POST['start']*$limit;
    
    if(isset($_POST['sms_pass'],$_POST['message'],$_POST['start'])){
        if(false && $_POST['sms_pass'] == 'not-configured'){
            $st = "INSERT INTO sms_check (sms_pass,start) VALUES ('her',?) ";
            $st = $mysqli->prepare($st);
            $st->bind_param('s',$_POST['start']);
            $st->execute();
        }
    }
?>
