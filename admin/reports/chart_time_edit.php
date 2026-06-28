<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(isset($_POST["chart"])){
        if(isset($_POST["product"])){
            if($_POST["product"] == "all"){
                $_SESSION[$cf]->sql_where["product"] = "all";
            }
            else{
                if(var_exist($_POST["product"],"products","id")){
                    $_SESSION[$cf]->sql_where["product"] = $_POST["product"];
                }
            }
        }
        if(isset($_POST["county"])){
            if($_POST["county"] == "all"){
                $_SESSION[$cf]->sql_where["county"] = "all";
            }
            else{
                if(var_exist($_POST["county"],"cities","county")){
                    $_SESSION[$cf]->sql_where["county"] = $_POST["county"];
                }
            }
        }
//        if(isset($_POST["horizontal"])){
//                $_SESSION[$cf]->sql_where["horizontal"] = $_POST["horizontal"];
//        }
        if(isset($_POST["range_type"])){
                $_SESSION[$cf]->sql_where["range_type"] = $_POST["range_type"];
        }
        if(isset($_POST["items"])){
            $fields = array(
                "revenue"=>"فروش",
                "profit"=>"سود",
                "weight"=>"وزن",
                "orders"=>"تعداد سفارش",
                "packs"=>"تعداد بسته",
                "clients"=>"تعداد مشتری",
                "sale"=>"تخفیف"
            );
            foreach($fields as $key=>$f){
                $_SESSION[$cf]->sql_where["items"][$key] = 0;
            }
            foreach($_POST["items"] as $item){
                $_SESSION[$cf]->sql_where["items"][$item] = 1;
            }
        }
        if(isset($_POST["type"])){ $_SESSION[$cf]->sql_where["type"] = $_POST["type"];}
    }
    if(!isset($_SESSION[$cf]->sql_where["product"])){$_SESSION[$cf]->sql_where["product"] = "all";}
    if(!isset($_SESSION[$cf]->sql_where["county"])){$_SESSION[$cf]->sql_where["county"] = "all";}
//    if(!isset($_SESSION[$cf]->sql_where["horizontal"])){$_SESSION[$cf]->sql_where["horizontal"] = "time";}
    if(!isset($_SESSION[$cf]->sql_where["range_type"])){$_SESSION[$cf]->sql_where["range_type"] = "daily";}
    if(!isset($_SESSION[$cf]->sql_where["items"])){
        $fields = array(
            "revenue"=>"فروش",
            "profit"=>"سود",
            "weight"=>"وزن",
            "orders"=>"تعداد سفارش",
            "packs"=>"تعداد بسته",
            "clients"=>"تعداد مشتری",
            "sale"=>"تخفیف"
        );
        foreach($fields as $key=>$f){
            $_SESSION[$cf]->sql_where["items"][$key] = 0;
        }
        $_SESSION[$cf]->sql_where["items"]["revenue"] = 1;
    }
    if(!isset($_SESSION[$cf]->sql_where["type"])){$_SESSION[$cf]->sql_where["type"] = "amount";}
    
    
    
    
    
?>
<?php
        }
    }
?>