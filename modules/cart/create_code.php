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
    if(!isset($_POST["tel"]) || $res){
        echo $origin."<br>";
        echo "Eخطایی رخ داده است، لطفا مجدد اقدام بفرمایید.";
        die;
    }
    if(!($tel = check_value("tel",$_POST["tel"]))){
        echo "Eشماره تلفن را به صورت صحیح وارد نمایید.";
        die;
    }
    $sms = 0;
    switch($origin){
        case "account":
            if(!var_exist($tel,"users","tel")){
                echo "Eنام کاربری وارد شده اشتباه است، مجددا اقدام نمایید.";
                die;
            }
            $sms = 1;
            break;
        case "register":
            if(var_exist($tel,"users","tel")){
                echo "Eنام کاربری وارد شده تکراری است، مجددا اقدام نمایید.";
                die;
            }
            break;
    }
    if(isset($_SESSION["a_logged"])){$state = 1;}else{$state = 0;}
    if(var_exist($tel,"users","tel")){
        $code = getVarFromDB("users","password","tel",$tel);
        $res = "Dگذرواژه ";
        $_SESSION["new_user"] = 0;
        $sms = 1;
        if($code === ""){
            $sms = 0;
        }
    }else{
        $code = generate_code();
        $res = "Dکد تایید ";
        $_SESSION["new_user"] = 1;
    }
    $d_res = db_new_user_attempt($tel,$code,$state,$sms);
    if($d_res[0]=="D"){
        $_SESSION["tel"] = $tel;
        echo $res;
    }else{
        echo $d_res;
    }
    die;

            

?>
