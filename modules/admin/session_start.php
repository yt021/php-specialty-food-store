<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    
//    include $bu."wdb/db_connection.php";
//    include $bu."wdb/db_funcs.php";
//    include $bu."admin/log_funcs.php";

    include_once($bu."modules/wdb/log_funcs.php");
    include_once($bu."modules/admin/funcs.php");
    ini_set('session.cookie_domain', ".abanfruit.com" );
    session_name("abanfruit");
    session_set_cookie_params(18000,"/",".abanfruit.com",FALSE,FALSE);
    session_start();
    if(isset($_SESSION["a_logged"])){
        check_a_login();
    }else{
        if(isset($_POST["login"]) && isset($_POST["user"]) && isset($_POST["pass"])){
            $data["user"] = check_value("text",$_POST["user"]);
            $data["pass"] = check_value("text",$_POST["pass"]);
            a_login($data);
        }        
    }
    if(isset($_SESSION["a_logged"])){
        if($cf !== "admin"){
        if(!$_SESSION["a_logged"]->check_access($cf)){
            header("Location:$s"."admin/");
        }
        }
    }
?>
<?php
        }
    }
?>