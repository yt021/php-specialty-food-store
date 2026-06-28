<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $chart_filters = array(
//        "variable_name"=>["table_name","key_column"],
        "product"=>["products","id","sub_orders","pid","name","محصول"],
        "county"=>["cities","id","addresses","county","county","استان"]
    );
    $chart_fields = array(
        "revenue"=>["فروش","int"],
        "sale"=>["تخفیف","int"],
        "profit"=>["سود","int"],
        "weight"=>["وزن","int"],
        "packs"=>["بسته","int"],
        "orders"=>["سفارش","order_list"],
        "clients"=>["مشتریان","order_list"]
    );
    
    if(isset($_POST["chart"])){
        foreach($chart_filters as $v_name=>$v_tb_data){
            if(isset($_POST[$v_name])){
                if($_POST[$v_name] == "all" || $_POST[$v_name] == "multi" ){
                    $_SESSION[$cf]->sql_where[$v_name] = $_POST[$v_name];
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
        if(isset($_POST["range_type"])){
                $_SESSION[$cf]->sql_where["range_type"] = $_POST["range_type"];
        }
        if(isset($_POST["data_serie"])){
                $_SESSION[$cf]->sql_where["data_serie"] = $_POST["data_serie"];
        }
        foreach($chart_filters as $v_name=>$v_tb_data){
            $_SESSION[$cf]->sql_where["chart_serie_range"][$v_name] = array();
            if(isset($_POST["ss_".$v_name]) && $_POST[$v_name]=="multi"){
                foreach($_POST["ss_".$v_name] as $ssi=>$ss_item){
                    $_SESSION[$cf]->sql_where["chart_serie_range"][$v_name][$ss_item] = 1;    
                }    
            }
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
    if(!isset($_SESSION[$cf]->sql_where["range_type"])){$_SESSION[$cf]->sql_where["range_type"] = "daily";}
    if(!isset($_SESSION[$cf]->sql_where["data_serie"])){$_SESSION[$cf]->sql_where["data_serie"] = "product";}
    if(!isset($_SESSION[$cf]->sql_where["items"])){
        foreach($chart_fields as $key=>$f){
            $_SESSION[$cf]->sql_where["items"][$key] = 0;
        }
        $_SESSION[$cf]->sql_where["items"]["revenue"] = 1;
    }
    if(!isset($_SESSION[$cf]->sql_where["type"])){$_SESSION[$cf]->sql_where["type"] = "amount";}
    foreach($chart_filters as $v_name=>$v_value){
        if(!isset($_SESSION[$cf]->sql_where["chart_serie_range"][$v_name])){
            $_SESSION[$cf]->sql_where["chart_serie_range"][$v_name] = array();
        }
    }
    if(!isset($_SESSION[$cf]->sql_where["items"])){
        foreach($chart_fields as $key=>$f){
            $_SESSION[$cf]->sql_where["items"][$key] = 0;
        }
        $_SESSION[$cf]->sql_where["items"]["revenue"] = 1;
    }
?>
<?php
        }
    }
?>