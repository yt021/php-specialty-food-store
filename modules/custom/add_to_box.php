<?php
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include($bu."modules/cart/cart_funcs.php");
    
    session_start();
    
    if(isset($_SESSION["custom"]) && is_a($_SESSION["custom"],"order_box"))
    
    if(isset($_POST["func"])){
        switch($_POST["func"]){
            case "add":
                if(isset($_POST["pid"]) && var_exist($_POST["pid"],"products","id") && getVarFromDB("products","state","id",$_POST["pid"]) == 0 && getVarFromDB("products","del_flag","id",$_POST["pid"]) == 0){
                    $w = (int)getVarFromDB("products_price","weight","pid",$_POST["pid"]);
                    $_SESSION["custom"]->content->add_order($_POST["pid"],$w);
                    $done = 1;
                }
                break;
            case "del":
                if(isset($_POST["pid"])){
                    $w = (int)getVarFromDB("products_price","weight","pid",$_POST["pid"]);
                    $_SESSION["custom"]->content->delete_order($_POST["pid"],$w);
                    $done = 1;
                }
                break;
            case "inc":
                if(isset($_POST["pid"])){
                    $w = (int)getVarFromDB("products_price","weight","pid",$_POST["pid"]);
                    $_SESSION["custom"]->content->change_no_order($_POST["pid"],$w,1);
                    $done = 1;
                }
                break;
            case "dec":
                if(isset($_POST["pid"])){
                    $w = (int)getVarFromDB("products_price","weight","pid",$_POST["pid"]);
                    $_SESSION["custom"]->content->change_no_order($_POST["pid"],$w,-1);
                    $done = 1;
                }
                break;
        }
    }
    if(!isset($done)){
        echo "E";
        die;
    }
    echo "D";
    echo $_SESSION["custom"]->content->number();
    echo "-"; 
    echo $_SESSION["custom"]->content->capacity - $_SESSION["custom"]->content->number();
    echo "-"; 
    echo $_SESSION["custom"]->content->list_items("پیمانه");
    echo "|";
    
    include ($bu."modules/custom/custom_table_tbody.php"); 
    
?>
