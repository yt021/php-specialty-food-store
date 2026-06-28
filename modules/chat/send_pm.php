<?php
$tb1 = "chats";
$tb2 = "chats_mes";

if(isset($_POST,$_POST['new']) && $_POST['new']){
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include($bu."modules/wdb/log_funcs.php");
    
    
    ini_set('session.cookie_domain', ".abanfruit.com" );
    session_name("abanfruit");
    session_set_cookie_params(18000,"/",".abanfruit.com",FALSE,FALSE);
    session_start();
    
    if(isset($_SESSION['a_logged'])){
        if(isset($_POST['chid']) && var_exist($_POST['chid'],$tb1,'id')){
        $chid = $_POST['chid'];
        $st = "INSERT INTO $tb2 (chid,isu,text) VALUES (?,0,?)";
        $st = $mysqli->prepare($st);
        $st->bind_param('ss',$chid,$_POST['new']);
        if(!$st->execute()){
            echo "E";
            die;
        }
        updateInDB($tb1,'last_time',date('Y-m-d H:i:s'),'id',$chid);
        echo "D";
        $st->close();
        }
    }else{
        if(isset($_SESSION['logged']) || isset($_SESSION['chat_id'])){
            if(isset($_SESSION['logged'])){
            $uid = $_SESSION['logged']->uid;
            if(var_exist($uid,'chats','uid')){
                $chid = getVarFromDB($tb1,'id','uid',$uid);
                
            }else{
                
                
                // first create new chat
                $st = "INSERT INTO $tb1 (uid) VALUES (?)";
                $st = $mysqli->prepare($st);
                $st->bind_param('s',$uid);
                if(!$st->execute()){
                    echo "E";
                    die;
                }
                $chid = $st->insert_id;
                $st->close();
            }
                if(!isset($_SESSION['chat_id'])){
                    send_sms_temp("09000000000","Admin",getVarFromDB('users','name','id',$uid));
                $_SESSION['chat_id'] = $chid;}
            }else{
                $chid = $_SESSION['chat_id'];
            }
            $st = "INSERT INTO $tb2 (chid,isu,text) VALUES (?,1,?)";
            $st = $mysqli->prepare($st);
            $st->bind_param('ss',$chid,$_POST['new']);
            if(!$st->execute()){
                echo "E";
                die;
            }
            updateInDB($tb1,'last_time',date('Y-m-d H:i:s'),'id',$chid);
            echo "D";
            $st->close();
            
        }
    }
}
?>
