<?php
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/cart/session_start.php";

    $res = true;
    if(isset($_SERVER["HTTP_REFERER"])){
        $origin =  $_SERVER["HTTP_REFERER"];
        if(strripos($origin,"/") == (strlen($origin)-1)){
            $origin = substr($origin,0,strripos($origin,"/"));
            $origin = substr($origin,strripos($origin,"/")+1);
        }else{
            $origin = substr($origin,strripos($origin,"/")+1);
            $origin = substr($origin,0,strripos($origin,"."));
        }
        switch($origin){
            case "account":
            case "register":
            case "cart":
                $res = false;
                break;
        }
    }
    if(!isset($_SESSION["tel"]) || $res){
        echo "Eخطایی رخ داده است، لطفا مجدد اقدام بفرمایید.";
        die;
    }
    

    $tel = $_SESSION["tel"];
    if(var_exist($tel,"users_password_attempt","tel")){
        $create_date = getVarFromDB("users_password_attempt","create_date","tel",$tel,"id DESC");
        $sql_timestamp = strtotime($create_date);
        $sql_now = db_time_now();
        
        
        
        
        if($sql_timestamp > $sql_now - 600){
            $code = generate_code();
            $d_res = db_new_user_attempt($tel,$code);
            if($d_res[0] == "D"){
                echo "Dکد تایید جدید ارسال شد.";
                if(var_exist($tel,"users","tel")){
                    updateInDB("users","password",$code,"tel",$tel);
                }
            }else{
                echo $d_res;
            }

        }else{
            echo "A بازه زمانی طولانی شده است، لطفا مجدد اقدام بفرمایید.";
        }
    }
    die;

?>
