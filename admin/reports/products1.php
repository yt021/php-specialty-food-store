<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

    $chart_filters = array(
//        "variable_name"=>["table_name","key_column"],
        "product"=>["products","id","sub_orders","pid","name","محصول"],
        "county"=>["cities","id","addresses","county","county","استان"]
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
        foreach($chart_filters as $v_name=>$v_tb_data){
            $_SESSION[$cf]->sql_where["chart_serie_range"][$v_name] = array();
            if(isset($_POST["ss_".$v_name]) && $_POST[$v_name]=="multi"){
                foreach($_POST["ss_".$v_name] as $ssi=>$ss_item){
                    $_SESSION[$cf]->sql_where["chart_serie_range"][$v_name][$ss_item] = 1;    
                }    
            }
        }
    }
    
    foreach($chart_filters as $v_name=>$v_value){
        if(!isset($_SESSION[$cf]->sql_where[$v_name])){$_SESSION[$cf]->sql_where[$v_name] = "all";}
    }
    foreach($chart_filters as $v_name=>$v_value){
        if(!isset($_SESSION[$cf]->sql_where["chart_serie_range"][$v_name])){
            $_SESSION[$cf]->sql_where["chart_serie_range"][$v_name] = array();
        }
    }


foreach($chart_filters as $v_name=>$v_tb_data){
    switch($_SESSION[$cf]->sql_where[$v_name]){
        case "all":
            $filter_where[$v_name] = "";
            break;
        case "multi":
            if($v_name != $data_serie_name){
                $filter_where[$v_name] = array();
                foreach($_SESSION[$cf]->sql_where["chart_serie_range"][$v_name] as $css_key=>$css_value){
                    $filter_where[$v_name][] = $v_tb_data[2].".".$v_tb_data[3]." = '".$css_key."'";
                }
                $filter_where[$v_name] = "AND ( ".implode(" OR ",$filter_where[$v_name])." ) ";
            }else{
                $filter_where[$v_name] = "";
            }
            break;
        default:
            $filter_where[$v_name] = "AND ".$v_tb_data[2].".".$v_tb_data[3]." = '".$_SESSION[$cf]->sql_where[$v_name]."'";
            break;
    }

}





$chart_filters = array(
//    "variable_name"=>["table_name","key_column","filter_table","filter_column","title"],
    "product"=>["products","id","sub_orders","pid","name","محصول"],
    "county"=>["cities","county","addresses","county","county","استان"]
);

//    Select Values
$select_values = array();
foreach($chart_filters as $v_name=>$v_tb_data){
    $st = "SELECT ".$v_tb_data[1].",".$v_tb_data[4]." FROM ".$v_tb_data[0]."";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $select_values[$v_name] = array();
    while($row = $res->fetch_assoc())
    {
        $select_values[$v_name][$row[$v_tb_data[1]]] = $row[$v_tb_data[4]];
    }
}


?>
<form class="date" action="<?php echo $URL; ?>" method="post">
<?php
    foreach($chart_filters as $v_name=>$v_tb_data){
?>
    <div class="field">
    <label for="<?php echo $v_name; ?>"><?php echo $v_tb_data[5]; ?>:</label> 
    <select name="<?php echo $v_name; ?>" onchange="show_select_serie(this[this.selectedIndex].value,'ss_<?php echo $v_name; ?>')">
        <option selected value="all">همه <?php echo $v_tb_data[5]; ?>‌ها</option>
        <option <?php if($_SESSION[$cf]->sql_where[$v_name] == "multi")echo " selected "; ?> value="multi" >انتخاب بعضی از  <?php echo $v_tb_data[5]; ?>‌ها</option>
        <?php
            foreach($select_values[$v_name] as $value=>$title){
                $selected = "";
                if($_SESSION[$cf]->sql_where[$v_name] == $value)$selected=" selected ";
                echo '<option '.$selected.' value="'.$value.'">'.$title.'</option>';
            }
        ?>
    </select>
    </div>
<?php
    }
?>
<?php
    foreach($chart_filters as $v_name=>$v_tb_data){
        $hide = " hide ";
        if($_SESSION[$cf]->sql_where[$v_name] == "multi")$hide = "";
?>
    
    <div class="<?php echo $hide; ?> select_series" id="ss_<?php echo $v_name; ?>">
    <div class="cut"></div>
        <?php echo $v_tb_data[5]."‌های مورد نظر:"; ?>
        <br>
        <div class="box_holder">
            <?php
                foreach($select_values[$v_name] as $value=>$title){
                    $checked = "";
                    if(isset($_SESSION[$cf]->sql_where["chart_serie_range"][$v_name][$value]) && $_SESSION[$cf]->sql_where["chart_serie_range"][$v_name][$value] == 1)$checked = " checked ";
                    echo '
                        <div class="options_item'.$checked.'" onclick="option_check(this)">
                            '.$title.'<input class="hide" type="checkbox" name="ss_'.$v_name.'[]" '.$checked.' value="'.$value.'">'.'
                            <div class="check_box">
                                <div class="check"></div>
                            </div>
                        </div>
                    ';
                }
            ?>
        </div>
    </div>
<?php
    }
?>
    <div class="cb cut"></div>
<input class="mt20" type="submit" name="chart" value="تایید">
</form>
<?php

    $products_table = array();
    
    $st = "SELECT id,name FROM products WHERE del_flag = 0";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row = $res->fetch_assoc())
    {
        $pid = $row["id"];
        $st = "SELECT weight FROM products_price WHERE pid = $pid ORDER BY start_time DESC LIMIT 1";
        $st = $mysqli->prepare($st);
        if(!$st->execute()){
            echo "E";
            exit;
        }
        $res2 = $st->get_result();
        $row2 = $res2->fetch_assoc();
        $weight = get_str_index($row2["weight"],",")[1];
        
        $products_table[$pid]["name"] = $row["name"];
        $products_table[$pid]["number"] = array_combine($weight,array_fill(0,sizeof($weight),0));
        $products_table[$pid]["total_weight"] = 0;
        $products_table[$pid]["total_revenue"] = 0;
        $products_table[$pid]["total_profit"] = 0;
    }
    
    
    // Create Final Filter_where

$addwhere = $filter_where['county'];
$prdwhere = $filter_where['product'];
            
        {
            $tb = 'orders';$tb2 = "sub_$tb";
            $tb3 = "products_price";
            $st = "SELECT $tb2.pid,$tb2.weight,$tb2.number,$tb.create_date FROM $tb2 
            LEFT JOIN orders ON $tb2.oid = $tb.id 
            LEFT JOIN addresses ON $tb.aid = addresses.id
            WHERE $tb.state >= 1 AND $tb.del_flag = 0
            $where $addwhere $prdwhere
            ";
            
//             var_dump($st);
//            echo "$st<br><br>";
            $st = $mysqli->prepare($st);
            
            // var_dump($st);
            if(!$st->execute()){
                echo "E";
                exit;
            }
            $res = $st->get_result();
            
            while($row = $res->fetch_assoc())
            {
                $create_date = $row["create_date"];
                $pid = $row["pid"];
                $weight = $row["weight"];
                $number = $row["number"];
                $pf = product_finance($pid,$weight,$create_date);
                $price = $pf["price"];
                $profit = $pf["profit"];

        
                $products_table[$pid]["number"][$weight] += $number;
                $products_table[$pid]["total_weight"] += $number * $weight;
                $products_table[$pid]["total_revenue"] += $number * $price;
                $products_table[$pid]["total_profit"] += $number * $profit;
            }
        }    
?>

<h3 class="tac">
   جدول فروش
</h3><br>
<?php
    echo '
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        نام محصول
                    </th>
                    ';
    $products = array_keys($products_table);
    $weights = array_keys($products_table[$products[0]]["number"]);
    for($i=0;$i<sizeof($products_table[$products[0]]["number"]);$i++){
        echo        "<th>";
                        echo $weights[$i];
        echo        "گرم </th>";
    }
    echo'
                    <th>
                        مجموع وزن
                    </th>
                    <th>
                        مجموع درآمد
                    </th>
                    <th>
                        مجموع سود
                    </th>
                    <th>
                        درصد وزن
                    </th>
                    <th>
                        درصد درآمد
                    </th>
                    <th>
                        درصد سود
                    </th>
                </tr>
            </thead>
            <tbody>';
            
    $total_revenue = 0;
    $total_weight = 0;
    $total_profit = 0;
    foreach($products_table as $prd){
        $total_revenue += $prd['total_revenue'];
        $total_weight += $prd['total_weight'];
        $total_profit += $prd['total_profit'];
    }
            
    for($i=0;$i<sizeof($products);$i++){
        

        
        if(!isset($products_table[$products[$i]]["name"]))$products_table[$products[$i]]["name"] = getVarFromDB('products','name','id',$products[$i]);
        
        echo "<tr>";
        echo "<td>";
        echo $products_table[$products[$i]]["name"];
        echo "</td>";
        $weights = array_keys($products_table[$products[$i]]["number"]);
        for($j=0;$j<sizeof($products_table[$products[$i]]["number"]);$j++){
            echo "<td>";
            echo $products_table[$products[$i]]["number"][$weights[$j]];
            echo "</td>";
        }
        for(;$j<sizeof($products_table[$products[0]]["number"]);$j++){
            echo "<td>0</td>";
        }
        
        $total_weight_i = $products_table[$products[$i]]["total_weight"];
        $total_revenue_i = $products_table[$products[$i]]["total_revenue"];
        $total_profit_i = $products_table[$products[$i]]["total_profit"];
        $total_revenue_i_p = ($total_revenue === 0)? 0 : number_format((float)$products_table[$products[$i]]["total_revenue"]/$total_revenue*100, 0, '.', '');
        $total_weight_i_p = ($total_weight === 0)? 0 : number_format((float)$products_table[$products[$i]]["total_weight"]/$total_weight*100, 0, '.', '');
        $total_profit_i_p = ($total_profit === 0)? 0 : number_format((float)$products_table[$products[$i]]["total_profit"]/$total_profit*100, 0, '.', '');
        
        echo "
            <td>
                $total_weight_i گرم
            </td>
            <td>
                $total_revenue_i
            </td>
            <td>
                $total_profit_i
            </td>
            <td>
                $total_weight_i_p %
            </td>
            <td>
                $total_revenue_i_p %
            </td>
            <td>
                $total_profit_i_p %
            </td>
        ";
    }
    echo '</tbody>
            </table>';
?>

<?php
        }
    }
?>