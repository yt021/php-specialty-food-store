<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

if(isset($_SESSION["logged"])){
    $result = "ok";
}
else{    

    $fields = ["name","email","PP_state"];
    foreach($fields as $f){
        if(isset($_POST[$f]))$data[$f]=$_POST[$f];
        else if(isset($_SESSION["user"]))$data[$f]=$_SESSION["user"]->data()[$f];
        else $data[$f]=null;
    }
    $fields = ["tel","create_code"];
    foreach($fields as $f){
        if(isset($_SESSION[$f])){
            $data[$f] = $_SESSION[$f];
        }
    }
    
    $_SESSION["user"] = new user($data);
    
    if($_SESSION["user"]->data()["error"][0] != 0 && sizeof($_SESSION["user"]->data()["error"][1]) >= 1){}
    else{
        $timestamp = db_time_now() - 15 * 60;
        $sqltime = date("Y-m-d H:i:s",$timestamp);
        $st = "SELECT id FROM users_password_attempt WHERE tel = ? AND code = ? AND state = 1 AND create_date >= '$sqltime' ORDER BY id DESC Limit 1";
        $st = $mysqli->prepare($st);
        $st->bind_param('ss',$_SESSION["user"]->data()["tel"],$_SESSION["user"]->data()["create_code"]);
        $st->execute();
        $st->store_result();
        if($st->num_rows == 1){
            $result = "ok";
        }else{
            $result = "no";
            unset($_SESSION["user"]);
        }
    }
}


?>
<?php
        }
    }
?>