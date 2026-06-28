<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    
//    include $bu."wdb/db_connection.php";
//    include $bu."wdb/db_funcs.php";
//    include $bu."admin/log_funcs.php";

    include_once($bu."modules/cart/cart_funcs.php");
    include_once($bu."modules/wdb/log_funcs.php");
    
    
    $session_cookie_lifetime = 18000;
    $session_cookie_domain = ".abanfruit.com";
    $session_cookie_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

    ini_set('session.cookie_domain', $session_cookie_domain);
    ini_set('session.cookie_secure', $session_cookie_secure ? '1' : '0');
    ini_set('session.cookie_httponly', '1');
    if(function_exists('session_set_cookie_params')){
        if(PHP_VERSION_ID >= 70300){
            session_set_cookie_params(array(
                'lifetime' => $session_cookie_lifetime,
                'path' => '/',
                'domain' => $session_cookie_domain,
                'secure' => $session_cookie_secure,
                'httponly' => true,
                'samesite' => 'None'
            ));
        }else{
            ini_set('session.cookie_samesite', 'None');
            session_set_cookie_params($session_cookie_lifetime, "/; samesite=None", $session_cookie_domain, $session_cookie_secure, true);
        }
    }
    session_name("abanfruit");
    
    // session_set_cookie_params(1200);
    session_start();
    if(!isset($_SESSION["cart"])){$_SESSION["cart"]=new cart();}

    if(isset($_SESSION["logged"])){
        check_login();
    }else{
        if(isset($_POST["login"]) && isset($_POST["tel"]) && isset($_POST["create_code"])){
            $log_data["tel"] = check_value("tel",$_POST["tel"]);
            $log_data["create_code"] = check_number($_POST["create_code"],6);
            login($log_data);
        }        
    }
    if(isset($_SESSION["logged"])){
        cart_restore_draft_for_logged_user();
        cart_sync_draft_for_logged_user();
    }
?>
<?php
        }
    }
?>
