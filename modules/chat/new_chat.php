<?php
$tb1 = "chats";
$tb2 = "chats_mes";

if(isset($_POST,$_POST['ln']) && $_POST['ln']){
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include($bu."modules/wdb/log_funcs.php");
    
    
    ini_set('session.cookie_domain', ".abanfruit.com" );
    session_name("abanfruit");
    session_set_cookie_params(18000,"/",".abanfruit.com",FALSE,FALSE);
    session_start();
    
        if(!isset($_SESSION['logged']) && !isset($_SESSION['chat_id'])){
                
            $ln = $_POST['ln'];
            // first create new chat
            $st = "INSERT INTO $tb1 (name) VALUES (?)";
            $st = $mysqli->prepare($st);
            $st->bind_param('s',$ln);
            if(!$st->execute()){
                echo "E";
                die;
            }
            $_SESSION['chat_id'] = $st->insert_id;
            $st->close();
            send_sms_temp("09000000000","Admin",$ln);
            echo "D";
            echo "
            <ul id=\"chat_holder\" class=\"chat_holder\">
    	    </ul>
            <div class=\"chat_input\">
                <input type=\"text\" name=\"chat\" placeholder=\"پیام خود را بنویسید\">
                <span class=\"icon-a\" onclick=\"send_pm()\"></span>
            </div>
            
            ";
        }
}
?>
