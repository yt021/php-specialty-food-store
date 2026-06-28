<?php
if(isset($_POST,$_POST['cl'],$_POST['cr'])){
    $cl = (int)$_POST['cl'];
    $cr = (int)$_POST['cr'];
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include($bu."modules/wdb/log_funcs.php");
    
    
    ini_set('session.cookie_domain', ".abanfruit.com" );
    session_name("abanfruit");
    session_set_cookie_params(18000,"/",".abanfruit.com",FALSE,FALSE);
    session_start();
    
    if(isset($_SESSION['a_logged'])){
        $st = "SELECT chats.id,chats.name as cname,users.name as uname,users.tel FROM chats LEFT JOIN users ON users.id = chats.uid WHERE chats.last_time > now() - INTERVAL 14 day ORDER BY chats.last_time DESC LIMIT 30 OFFSET $cl";
        $res = return_sel_sql($st);
        if($res->num_rows>0){
            echo "D";
            while($row = $res->fetch_assoc()){
                if(!$row['uname'])$row['uname'] = $row['cname'];
                $cls="";
                if($row['id'] == $cr)$cls = " class='selected' ";
                echo "<li id='ch${row['id']}' $cls>${row['uname']} (${row['tel']})</li>";
            }
        }
    }
}
?>