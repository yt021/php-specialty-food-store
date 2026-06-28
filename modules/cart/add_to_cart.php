<?php
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include($bu."modules/cart/cart_funcs.php");
    
    
    ini_set('session.cookie_domain', ".abanfruit.com" );
    session_name("abanfruit");
    session_set_cookie_params(18000,"/",".abanfruit.com",FALSE,FALSE);
    session_start();
    if(!isset($_SESSION["cart"])){$_SESSION["cart"]=new cart();}
    if(isset($_POST["func"])){
        switch($_POST["func"]){
            case "add":
                if(isset($_POST["pid"]) && isset($_POST["weight"]) && var_exist($_POST["pid"],"products","id") && getVarFromDB("products","state","id",$_POST["pid"]) == 0 && getVarFromDB("products","del_flag","id",$_POST["pid"]) == 0 && getVarFromDB("products","type","id",$_POST["pid"]) == "pack"){
                    $w = (int)$_POST["weight"];
                    $_SESSION["cart"]->add_order($_POST["pid"],$w);
                    $done = 1;
                }
                break;
            case "del":
                if(isset($_POST["pid"],$_POST["weight"],$_POST["opbid"])){
                    $w = (int)$_POST["weight"];
                    $_SESSION["cart"]->delete_order($_POST["pid"],$w,$_POST["opbid"]);
                    $done = 1;
                }
                break;
            case "inc":
                if(isset($_POST["pid"],$_POST["weight"],$_POST["opbid"])){
                    $w = (int)$_POST["weight"];
                    $_SESSION["cart"]->change_no_order($_POST["pid"],$w,$_POST["opbid"],1);
                    $done = 1;
                }
                break;
            case "dec":
                if(isset($_POST["pid"],$_POST["weight"],$_POST["opbid"])){
                    $w = (int)$_POST["weight"];
                    $_SESSION["cart"]->change_no_order($_POST["pid"],$w,$_POST["opbid"],-1);
                    $done = 1;
                }
                break;
            case "change_w":
                if(isset($_POST["pid"]) && isset($_POST["weight"]) && isset($_POST["old_weight"])){
                    $w = (int)$_POST["weight"];
                    $ow = (int)$_POST["old_weight"];
                    $_SESSION["cart"]->change_weight_order($_POST["pid"],$ow,$w);
                    $done = 1;
                }
                break;
        }
    }
    if(!isset($done)){
        echo "E";
        die;
    }
    // if(isset($_SESSION['a_logged'])){
    //     echo $_SESSION['check_cart'];
    //     unset($_SESSION['check_cart']);
    // }
    cart_sync_draft_for_logged_user();

    echo "D";
    echo $_SESSION["cart"]->number();
    echo "-"; 
    echo price_sep($_SESSION["cart"]->price());
    echo "|";
    
    include ($bu."modules/cart/cart_table_tbody.php"); 
    
?>
