<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $chart_filters = array(
//        "variable_name"=>["table_name","key_column"],
        "product"=>["products","id"],
        "supplier"=>["suppliers","id"]
    );
    $chart_fields = array(
        "first_weight"=>["وزن اولیه","int"],
        "waste_weight"=>["وزن ضایعات","int"],
        "pure_weight"=>["وزن دستچین شده","int"],
        "quality"=>["کیفیت","int"],
        "price_cof"=>["ضریب قیمت","int"]
    );
    
    if(isset($_POST["chart"])){
        foreach($chart_filters as $v_name=>$v_tb_data){
            if(isset($_POST[$v_name])){
                if($_POST[$v_name] == "all"){
                    $_SESSION[$cf]->sql_where[$v_name] = "all";
                }
                else{
                    if(var_exist($_POST[$v_name],$v_tb_data[0],$v_tb_data[1])){
                        $_SESSION[$cf]->sql_where[$v_name] = $_POST[$v_name];
                    }
                }
            }
        }
        if(isset($_POST["horizontal"])){
                $_SESSION[$cf]->sql_where["horizontal"] = $_POST["horizontal"];
        }
        if(isset($_POST["items"])){
            foreach($chart_fields as $key=>$f){
                $_SESSION[$cf]->sql_where["items"][$key] = 0;
            }
            foreach($_POST["items"] as $item){
                $_SESSION[$cf]->sql_where["items"][$item] = 1;
            }
        }
        if(isset($_POST["type"])){ $_SESSION[$cf]->sql_where["type"] = $_POST["type"];}
    }
    
    foreach($chart_filters as $v_name=>$v_value){
        if(!isset($_SESSION[$cf]->sql_where[$v_name])){$_SESSION[$cf]->sql_where[$v_name] = "all";}
    }
    if(!isset($_SESSION[$cf]->sql_where["horizontal"])){$_SESSION[$cf]->sql_where["horizontal"] = "time";}
    if(!isset($_SESSION[$cf]->sql_where["items"])){
        foreach($chart_fields as $key=>$f){
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