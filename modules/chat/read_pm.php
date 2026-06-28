<?php
if(isset($_POST,$_POST['lr'])){
    $lr = (int)$_POST['lr'];
    $indexed = 1;
    
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include($bu."modules/wdb/log_funcs.php");
    ini_set('session.cookie_domain', ".abanfruit.com" );
    session_name("abanfruit");
    session_set_cookie_params(18000,"/",".abanfruit.com",FALSE,FALSE);
    session_start();
    
    if(isset($_SESSION['a_logged'])){
        if(isset($_POST['chid']) && var_exist($_POST['chid'],'chats','id')){
        $chid = $_POST['chid'];
        // getting last admin read
        $ulr = (int)getVarFromDB('chats','ulr','id',$chid);
        $st = "SELECT * FROM chats_mes WHERE chid = $chid AND id>$lr";
        $res = return_sel_sql($st);
        $id = $lr;
        if($res->num_rows>0){
            echo "D";
            while($row = $res->fetch_assoc()){
                $cls = "";
                $hr = date("H:i:s",strtotime($row['create_time']));
                if($row['isu'] == 1)$cls = 'rec';
                $id = $row['id'];
                echo "<li class='$cls' id='${row['id']}'>${row['text']}<span>$hr</span></li>";
            }
        }else{
            echo "S";
        }
        updateInDB('chats','alr',$id,'id',$chid);
        }
    }else{
        if(isset($_SESSION['logged'])  || isset($_SESSION['chat_id'])){
            if(isset($_SESSION['logged'])){
            $uid = $_SESSION['logged']->uid;
            if(var_exist($uid,'chats','uid')){
                $chid = getVarFromDB('chats','id','uid',$uid);
            }else{
                // first create new chat
                $st = "INSERT INTO chats (uid) VALUES (?)";
                $st = $mysqli->prepare($st);
                $st->bind_param('s',$uid);
                if(!$st->execute()){
                    echo "E";
                    die;
                }
                $chid = $st->insert_id;
                $st->close();
            }
            }else{
                $chid = $_SESSION['chat_id'];
            }
            // getting last user read
            $ulr = (int)getVarFromDB('chats','ulr','id',$chid);
            $st = "SELECT * FROM chats_mes WHERE chid = $chid AND id>$lr AND create_time > now() - INTERVAL 14 day";
            $res = return_sel_sql($st);
            $id = $lr;
            if($res->num_rows>0){
                echo "D";
                while($row = $res->fetch_assoc()){
                    $cls = "";
                    if($row['isu'] == 0)$cls = 'rec';
                    $id = $row['id'];
                    echo "<li class='$cls' id='${row['id']}'>${row['text']}</li>";
                }
            }else{
                echo "S";
            }
            updateInDB('chats','ulr',$id,'id',$chid);
        }
    }
}
?>
