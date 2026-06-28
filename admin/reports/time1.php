<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
$vertical_items_no = 5;
$bar_fields = ["revenue","sale","profit","weight","packs","orders","clients"];
$chart_fields = array(
    "revenue"=>["فروش","int"],
    "sale"=>["تخفیف","int"],
    "profit"=>["سود","int"],
    "weight"=>["وزن","int"],
    "packs"=>["بسته","int"],
    "orders"=>["سفارش","order_list"],
    "clients"=>["مشتریان","order_list"]
);
$chart_types = array(
    "amount"=>"مقدار",
//    "percentage"=>"درصد",
    "per_range"=>"بی‌بعد"
);
$chart_filters = array(
//    "variable_name"=>["table_name","key_column","filter_table","filter_column","title"],
    "product"=>["products","id","sub_orders","pid","name","محصول"],
    "county"=>["cities","county","addresses","county","county","استان"]
);
$chart_categories = array(
//    "variable_name"=>["table_name","key_column","filter_table","filter_column"],
    "product"=>["products","id","sub_orders","pid","name","محصول"],
    "county"=>["cities","county","addresses","county","county","استان"]
);

$time_column = "orders.create_date";

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
//    Series Colors
$series_colors = array(
    "(230,25,75)",
    "(60,180,75)",
    "(255,225,25)",
    "(0,130,200)",
    "(245,130,48)",
    "(145,30,180)",
    "(70,240,240)",
    "(240,50,230)",
    "(210,245,60)",
    "(250,190,190)",
    "(0,128,128)",
    "(230,190,255)",
    "(170,110,40)",
    "(255,250,200)",
    "(128,0,0)",
    "(170,255,195)",
    "(128,128,0)",
    "(255,215,180)",
    "(0,0,128)",
    
    "(230,25,75)",
    "(60,180,75)",
    "(255,225,25)",
    "(0,130,200)",
    "(245,130,48)",
    "(145,30,180)",
    "(70,240,240)",
    "(240,50,230)",
    "(210,245,60)",
    "(250,190,190)",
    "(0,128,128)",
    "(230,190,255)",
    "(170,110,40)",
    "(255,250,200)",
    "(128,0,0)",
    "(170,255,195)",
    "(128,128,0)",
    "(255,215,180)",
    "(0,0,128)",
    "(230,25,75)",
    "(60,180,75)",
    "(255,225,25)",
    "(0,130,200)",
    "(245,130,48)",
    "(145,30,180)",
    "(70,240,240)",
    "(240,50,230)",
    "(210,245,60)",
    "(250,190,190)",
    "(0,128,128)",
    "(230,190,255)",
    "(170,110,40)",
    "(255,250,200)",
    "(128,0,0)",
    "(170,255,195)",
    "(128,128,0)",
    "(255,215,180)",
    "(0,0,128)"
);
$series_colors["total"] = "(50,50,50)";
//    Chart Series
$data_serie_name = $_SESSION[$cf]->sql_where["data_serie"];
$chart_series = $select_values[$data_serie_name];
$chart_series_key_column = $chart_filters[$data_serie_name][3];
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

<!--    <label for="horizontal">برحسب (محور افقی):</label> 
    <select name="horizontal">
        <?php
            $fields = array(
                "time"=>"زمان",
            );
            $selected = " selected ";
            foreach($fields as $key=>$f){
                if($_SESSION[$cf]->sql_where["horizontal"] == $key)$selected=" selected ";
                echo '<option '.$selected.' value="'.$key.'">'.$f.'</option>';
                $selected = "";
            }
        ?>
    </select>  -->
    <div class="field">
    <label for="range_type">بازه:</label> 
    <select name="range_type">
        <?php
            $fields = array(
                "daily"=>"روزانه",
                "weekly"=>"هفتگی",
                "fortnight"=>"دو هفتگی",
                "monthly"=>"ماهانه",
                "seasonly"=>"فصلی",
                "yearly"=>"سالانه",
            );
            $selected = " selected ";
            foreach($fields as $key=>$f){
                if($_SESSION[$cf]->sql_where["range_type"] == $key)$selected=" selected ";
                echo '<option '.$selected.' value="'.$key.'">'.$f.'</option>';
                $selected = "";
            }
        ?>
    </select>
    </div>
    <div class="field">
    <label for="data_serie">به تفکیک:</label> 
    <select name="data_serie">
        <?php
            foreach($chart_filters as $v_name=>$v_tb_data){
                $selected = "";
                if($_SESSION[$cf]->sql_where["data_serie"] == $v_name)$selected=" selected ";
                echo '<option '.$selected.' value="'.$v_name.'">'.$v_tb_data[5].'</option>';
                
            }
        ?>
    </select>
    </div>
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
    <div class="box_holder">
    <label class="fr" for="items">موارد: </label>
    
<?php
    foreach($chart_fields as $key=>$f){
        $checked = "";
        if($_SESSION[$cf]->sql_where["items"][$key] == 1)$checked = " checked ";
        echo '
            <div class="options_item'.$checked.'" onclick="option_check(this)">
                '.$f[0].'<input class="hide" type="checkbox" name="items[]" '.$checked.' value="'.$key.'">'.'
                <div class="check_box">
                    <div class="check"></div>
                </div>
            </div>
        ';
    }
?>
    </div>



    <div class="box_holder">    
    <label class="fr" for="type">نوع: </label>
    
<?php
    foreach($chart_types as $key=>$f){
        $checked = "";
        if($_SESSION[$cf]->sql_where["type"] == $key)$checked = " checked ";
        echo '
            <div class="radio options_item'.$checked.'" onclick="option_radio(this)">
                '.$f.'<input class="hide" type="radio" name="type" '.$checked.' value="'.$key.'">'.'
                <div class="check_box">
                    <div class="check"></div>
                </div>
            </div>
        ';
    }
?>
    </div>
    <input class="mt20" type="submit" name="chart" value="تایید">
</form>
<?php
    if($_SESSION[$cf]->sql_where["horizontal"] == "time"){?>
    </div>
</div>

<?php
    if($_SESSION[$cf]->sql_where[$data_serie_name] == "multi" || $_SESSION[$cf]->sql_where[$data_serie_name] == "all"){
?>
<div class="cut w100p"></div>
<div id="sect4" class="sect ">
    <div class="middle container">
        راهنمای نمودارها:<br>
        <div class="chart_legend">
            <ul>
                <li>
                    مجموع
                    <div class="legend_dot" style="background-color:rgb<?php echo $series_colors["total"]; ?>;"></div>
                </li>
<?php
    $color_i = 0;
    foreach($chart_series as $cs_key=>$cs_title){
        $ok = 0;
        if($_SESSION[$cf]->sql_where[$data_serie_name] == "multi"){
            if(isset($_SESSION[$cf]->sql_where["chart_serie_range"][$data_serie_name][$cs_key])){
                $ok=1;
            }
        }else if($_SESSION[$cf]->sql_where[$data_serie_name] == "all"){
            $ok = 1;
        }
        
        if($ok == 1){
            $color_i++;
?>
                <li>
                    <?php echo $cs_title;?>
                    <div class="legend_dot" style="background-color:rgb<?php echo $series_colors[$color_i]; ?>;"></div>
                </li>
<?php
        }
    }
?>
            </ul>
        </div>

    </div>
</div>
<?php
    }
?>
<?php
    }
?>
<div class="cut w100p"></div>
<?php
class bar{
    public $amount;
    public $percentage;
    public $title;
    public function __construct(){
        $this->title="";
        $this->amount = array();
        $this->percentage = array();
        foreach($GLOBALS["chart_fields"] as $key=>$f){
            switch($f[1]){
                case 'int':
                    $this->amount[$key] = 0;
                    break;
                case 'order_list':
                    $this->amount[$key] = new order_by();
                    break;
            }
            $this->percentage[$key] = 0;
        }
    }
    public function add($key,$value,$total=""){
//        echo $key;
        if($GLOBALS["chart_fields"][$key][1] == "int"){
            $this->amount[$key] += $value;
        }else if($GLOBALS["chart_fields"][$key][1] == "order_list"){
            if($total == "total"){
                $keys_array = $value->keys_array();
                foreach($keys_array as $key_value){
                    $this->amount[$key]->add_item($key_value);
                }
            }else{
            $this->amount[$key]->add_item($value);
            }
        }
        return;
    }
    public function finalize(){
        foreach($GLOBALS["chart_fields"] as $key=>$f){
            switch($f[1]){
                case 'int':
                    break;
                case 'order_list':
                    $this->amount[$key] = substr_count($this->amount[$key]->order_str(""),",");
                    break;
            }
        }
    }
}
class order_data{
    public $values;
    public $oid;
    public $uid;
    public function __construct($oid){
        $this->values = array();
        foreach($GLOBALS["chart_fields"] as $key=>$f){
            switch($f[1]){
                case 'int':
                    $this->values[$key] = 0;
                    break;
            }
        }
        $this->uid = null;
        $this->oid = $oid;
    }
    public function report($key){
        switch($key){
            case 'orders':
                return $this->oid;
                break;
            case 'clients':
                return $this->uid;
                break;
            default:
                return $this->values[$key];
            
        }
    }
}


//    Chart Filters
$time_where = db_time_condition($time_column,$_SESSION[$cf]->sql_where["start_date"],$_SESSION[$cf]->sql_where["end_date"],1);
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
// Chart Categories - X Axis
class range{
    public $title;
    public $range_where;
    public function __construct($title,$where){
        $this->title = $title;
        $this->range_where = $where;
    }
}
$ranges = array();
//    Time Category

if($_SESSION[$cf]->sql_where["horizontal"] == "time"){
    $start_date = $_SESSION[$cf]->sql_where["start_date"];
    $end_date = $_SESSION[$cf]->sql_where["end_date"];
    $start_ts = create_tsDate($_SESSION[$cf]->sql_where["start_date"]);
    $end_ts = create_tsDate($_SESSION[$cf]->sql_where["end_date"]);
//    $period_length = ($end_ts - $start_ts)/(24*3600);
//    if($period_length<11){
//        $type = "daily";
//    }else
//    if($period_length<70){
//        $type = "weekly";
//    }else
//    if($period_length<175){
//        $type = "fortnight";
//    }else
//    if($period_length<366){
//        $type = "monthly";
//    }else
//    if($period_length<4*365){
//        $type = "seasonly";
//    }else{
//        $type = "yearly";
//    }
    $type = $_SESSION[$cf]->sql_where["range_type"];
    switch($type){
        case "daily":
            $step=1;
            $time_ts = $start_ts;
            $k = 0;
            while($time_ts<$end_ts){
                $period_start = $time_ts;
                $period_end = $time_ts + $step*24*3600;
                $where = db_time_condition($time_column,CaSDate($period_start),CaSDate($period_end),1);
                $range_title = correctDate_ts_woy($period_start);
                
                $ranges[$k] = new range($range_title,$where);
                
                $time_ts = $period_end;
                $k++;
            }
            break;
        case "weekly":
            $step=7;
            $start_ts = $start_ts - dayoWeekJ(CaSDate($start_ts)["day_name"])*24*3600;
            $time_ts = $start_ts;
            $k = 0;
            while($time_ts<$end_ts){
                $period_start = $time_ts;
                $period_end = $time_ts + $step*24*3600;
                $where = db_time_condition($time_column,CaSDate($period_start),CaSDate($period_end),1);
                $range_title = "از ".correctDate_ts_woy($period_start)."<br> تا ".correctDate_ts_woy($period_end);
                $ranges[$k] = new range($range_title,$where);
                
                $time_ts = $period_end;
                $k++;
            }
            break;
        case "fortnight":
            $s_date = $start_date;
            if($start_date["day"]<16){
                $start_ts = create_ts($start_date["year"],$start_date["month"],1);
                $s_date["day"]=1;
            }else{
                $start_ts = create_ts($start_date["year"],$start_date["month"],16);
                $s_date["day"]=16;
            }
            $time_ts = $start_ts;
            $k = 0;
            while($time_ts<$end_ts){
                if($s_date["day"]==1){
                    $period_start = create_ts($s_date["year"],$s_date["month"],1);
                    $period_end = create_ts($s_date["year"],$s_date["month"],16);
                    $range_title = "نیمه اول ".CaSDate($period_start)["month_name"];   
                }else if($s_date["day"]==16){
                    $period_start = create_ts($s_date["year"],$s_date["month"],16);
                    if($s_date["month"]==12){
                        $period_end = create_ts($s_date["year"]+1,1,1);
                    }else{
                        $period_end = create_ts($s_date["year"],$s_date["month"]+1,1);
                    }
                    $range_title = "نیمه دوم ".CaSDate($period_start)["month_name"];   
                }
                $where = db_time_condition($time_column,CaSDate($period_start),CaSDate($period_end),1);
                $ranges[$k] = new range($range_title,$where);
                
                $time_ts = $period_end;
                $s_date = CaSDate($time_ts);
                $k++;
            }
            break;
        case "monthly":
            $s_date = $start_date;
            $s_date["day"]=1;
            $start_ts = create_ts($start_date["year"],$start_date["month"],1);
            $time_ts = $start_ts;
            $k = 0;
            while($time_ts<$end_ts){
                $period_start = create_ts($s_date["year"],$s_date["month"],1);
                if($s_date["month"]==12){
                    $period_end = create_ts($s_date["year"]+1,1,1);
                }else{
                    $period_end = create_ts($s_date["year"],$s_date["month"]+1,1);
                }
                $range_title = CaSDate($period_start)["month_name"]." ".CaSDate($period_start)["year"];
                $where = db_time_condition($time_column,CaSDate($period_start),CaSDate($period_end),1);
                $ranges[$k] = new range($range_title,$where);
                
                $time_ts = $period_end;
                $s_date = CaSDate($time_ts);
                $k++;
            }
            break;
        case "seasonly":
            $s_date = $start_date;
            $s_date["day"]=1;
            $s_date["month"] = ((int)(($s_date["month"]-1)/3))*3+1;
            $start_ts = create_ts($s_date["year"],$s_date["month"],1);
            $time_ts = $start_ts;
            $k = 0;
            $seasons = array(
                1=>"بهار",
                4=>"تابستان",
                7=>"پاییز",
                10=>"زمستان"
            );
            while($time_ts<$end_ts){
                $period_start = create_ts($s_date["year"],$s_date["month"],1);
                if($s_date["month"]==10){
                    $period_end = create_ts($s_date["year"]+1,1,1);
                }else{
                    $period_end = create_ts($s_date["year"],$s_date["month"]+3,1);
                }
                $range_title = $seasons[(int)$s_date["month"]]." ".CaSDate($period_start)["year"];
                $where = db_time_condition($time_column,CaSDate($period_start),CaSDate($period_end),1);
                $ranges[$k] = new range($range_title,$where);
                
                $time_ts = $period_end;
                $s_date = CaSDate($time_ts);
                $k++;
            }
            break;
        case "yearly":
            $s_date = $start_date;
            $s_date["day"]=1;
            $s_date["month"] = 1;
            $start_ts = create_ts($s_date["year"],$s_date["month"],1);
            $time_ts = $start_ts;
            $k = 0;
            while($time_ts<$end_ts){
                $period_start = create_ts($s_date["year"],1,1);
                $period_end = create_ts($s_date["year"]+1,1,1);

                $range_title = "سال ".CaSDate($period_start)["year"];
                $where = db_time_condition($time_column,CaSDate($period_start),CaSDate($period_end),1);
                $ranges[$k] = new range($range_title,$where);
                
                $time_ts = $period_end;
                $s_date = CaSDate($time_ts);
                $k++;
            }
            break;
    }    
    $time_where = "";
}

// Create Final Filter_where
$ff_where = $time_where;
foreach($chart_filters as $v_name=>$v_tb_data){
    $ff_where .= " ".$filter_where[$v_name];
}
//    Chart Data Collection

//    Max Chart Data
$max = new bar();
//    Total Chart Data
$chart_data[0] = new bar();
foreach($chart_fields as $key=>$f){
    $max->amount[$key] = 0;
    $chart_data[0]->amount[$key] = 0;
}
$k = 1;
$tb = "orders";
$tb2 = "sub_$tb";
$tb3 = "addresses";
    
foreach($ranges as $range){
    $chart_data[$k] = array();
    $chart_data[$k]["total"] = new bar();
    $chart_data[$k]["total"]->title = $range->title;
    
    foreach($chart_series as $key=>$value){
        $chart_data[$k][$key] = new bar();
    }
    
    $range_where = $range->range_where;
    
    $st = "SELECT 
    $tb.id,$tb.uid,$tb.create_date,$tb.cart_pure,$tb.sale_total,
    $tb2.pid,$tb2.number,$tb2.weight,$tb3.county 
    FROM $tb 
    CROSS JOIN $tb2
    CROSS JOIN $tb3
    WHERE 
    $tb.id = $tb2.oid AND
    $tb.aid = $tb3.id AND
    $tb.del_flag = 0 AND
    $tb.state >= 1
    $ff_where
    $range_where
    ORDER BY $tb.id DESC
    ";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $order = new order_data(0);
    while($row = $res->fetch_assoc())
    {
        if($_SESSION[$cf]->sql_where["data_serie"] == "product"){
            $row["packs"] = $row["number"];
            $pf = product_finance($row["pid"],$row["weight"],$row["create_date"]);
            
            $row["revenue"]= $pf["price"]*$row["packs"];
            $row["sale"] = 0;
            $row["profit"] = $pf["profit"]*$row["packs"];
            $row["weight"] = $pf["weight"]*$row["packs"];
            $row["orders"] = $row["id"];
            $row["clients"] = $row["uid"];
            
            foreach($chart_fields as $cf_key=>$f){
                $chart_data[$k][$row[$chart_series_key_column]]->add($cf_key,$row[$cf_key]) ;
            }
        }else{
            $oid = $row["id"];
            if($order->oid != $oid){
                if($_SESSION[$cf]->sql_where["product"]=="all"){
                    $order->values["profit"] -= $order->values["sale"];
                }
                foreach($chart_fields as $cf_key=>$f){
                    $chart_data[$k][$row[$chart_series_key_column]]->add($cf_key,$order->report($cf_key)) ;
                }           
                $order = new order_data($oid);
                $order->uid = $row["uid"];
                if($_SESSION[$cf]->sql_where["product"]=="all"){
                    $order->values["revenue"] = $row["cart_pure"];
                    $order->values["sale"] = $row["sale_total"];
                }
            }
            if($_SESSION[$cf]->sql_where["product"]!="all"){
                $pf = product_finance($row["pid"],$row["weight"],$row["create_date"]);
                $order->values["revenue"] += $row["number"]*$pf["price"];
                $order->values["profit"] += $row["number"]*$pf["profit"];
            }else{
                if($_SESSION[$cf]->sql_where["items"]["profit"] == 1){
                    $pf = product_finance($row["pid"],$row["weight"],$row["create_date"]);
                    $order->values["profit"] += $row["number"]*$pf["profit"];
                }
            }
            $order->values["packs"] += $row["number"];
            $order->values["weight"] += $row["number"]*$row["weight"];
        }
    }

//        Category(Range) Total
    foreach($chart_fields as $cf_key=>$f){
        foreach($chart_series as $cs_key=>$value){
            $chart_data[$k]["total"]->add($cf_key,$chart_data[$k][$cs_key]->amount[$cf_key],"total"); 
        }
    }
    foreach($chart_series as $cs_key=>$value){
        $chart_data[$k][$cs_key]->finalize();
    }
    $chart_data[$k]["total"]->finalize();
//    Total Calculation
    foreach($chart_fields as $cf_key=>$f){
        $chart_data[0]->amount[$cf_key] += $chart_data[$k]["total"]->amount[$cf_key];
    }
    $k++;
}

// Calculating Absolute Percentages
// Amount Mode
if($_SESSION[$cf]->sql_where["type"]=="amount"){
    foreach($chart_fields as $cf_key=>$f){
        if($_SESSION[$cf]->sql_where["items"][$cf_key] == 1){
            for($i=1;$i<$k;$i++){
                if($chart_data[0]->amount[$cf_key]){
                    foreach($chart_series as $cs_key=>$s){
                        if($_SESSION[$cf]->sql_where[$data_serie_name] != "multi" ||
                        ($_SESSION[$cf]->sql_where[$data_serie_name] == "multi" && isset($_SESSION[$cf]->sql_where["chart_serie_range"][$data_serie_name][$cs_key]))
                        ){
                            $chart_data[$i][$cs_key]->percentage[$cf_key] = 100*$chart_data[$i][$cs_key]->amount[$cf_key]/$chart_data[0]->amount[$cf_key];
                            if($max->amount[$cf_key]<$chart_data[$i][$cs_key]->amount[$cf_key])$max->amount[$cf_key]=$chart_data[$i][$cs_key]->amount[$cf_key];
                        }
                    }
                    if($_SESSION[$cf]->sql_where[$data_serie_name] == "all"){
                        $chart_data[$i]["total"]->percentage[$cf_key] = 100*$chart_data[$i]["total"]->amount[$cf_key]/$chart_data[0]->amount[$cf_key];
                        if($max->amount[$cf_key]<$chart_data[$i]["total"]->amount[$cf_key])$max->amount[$cf_key]=$chart_data[$i]["total"]->amount[$cf_key];
                    }
                }
            }
            if($chart_data[0]->amount[$cf_key])$max->percentage[$cf_key] = 100*$max->amount[$cf_key]/$chart_data[0]->amount[$cf_key];
        }
    }
}


// Per Range Mode (Total)
if($_SESSION[$cf]->sql_where["type"]=="per_range"){
    foreach($chart_fields as $cf_key=>$f){
        if($_SESSION[$cf]->sql_where["items"][$cf_key] == 1){
            for($i=1;$i<$k;$i++){
                if($chart_data[$i]["total"]->amount[$cf_key]){
                    foreach($chart_series as $cs_key=>$s){
                        if($_SESSION[$cf]->sql_where[$data_serie_name] != "multi" ||
                        ($_SESSION[$cf]->sql_where[$data_serie_name] == "multi" && isset($_SESSION[$cf]->sql_where["chart_serie_range"][$data_serie_name][$cs_key]))
                        ){
                            $chart_data[$i][$cs_key]->percentage[$cf_key] = 100*
                            $chart_data[$i][$cs_key]->amount[$cf_key]/$chart_data[$i]["total"]->amount[$cf_key];
                            if($max->amount[$cf_key]<$chart_data[$i][$cs_key]->amount[$cf_key])
                            $max->amount[$cf_key]=$chart_data[$i][$cs_key]->amount[$cf_key];
                            if($max->percentage[$cf_key]<$chart_data[$i][$cs_key]->percentage[$cf_key])
                            $max->percentage[$cf_key]=$chart_data[$i][$cs_key]->percentage[$cf_key];
                        }
                    }
                }
            }
        }
    }
}
?>
<div id="sect4" class="sect ">
    <div class="middle container">
<?php
foreach($chart_fields as $f=>$chart_title){
    if($_SESSION[$cf]->sql_where["items"][$f] == 1){
?>
<?php
        $vertical_items_no = 5;
        
        if($_SESSION[$cf]->sql_where["type"]=="percentage" || $_SESSION[$cf]->sql_where["type"]=="per_range")
        {
            $max_chart = (int)$max->percentage[$f];  
            $division = 20;
            if($max_chart<20){
                $division = 2;
            }
            if($max_chart<10){
                $division = 1;
            }
            if($max_chart<5){
                $division = 0.5;
            }
            if($max_chart<2){
                $division = 0.2;
            }
            if($division < 1){
                $max_chart = ((int)($max->percentage[$f]/$division))*$division;
            }
            $max_chart = ((int)($max_chart/$division)+1)*$division;
            $max_chart = min([$max_chart,100]);
//            echo $max_chart."<br>";
            $vertical_items_no = $max_chart/$division;
            $ratio = 1;
            if($max->percentage[$f])$ratio = $max_chart/$max->percentage[$f];
            $min_chart = 0;
            $step = ($max_chart-$min_chart)/$vertical_items_no;
//            echo "$division-$step";
            if($step == 0){
                $vertical_items_no = 1;
            }
        }else{
            $decimals = pow(10,strlen((string)round($max->amount[$f],0))-2);
            $max_chart = ceil(round((float)$max->amount[$f]/($decimals),1))*$decimals;
            $ratio = 1;
            if($max->amount[$f])$ratio = $max_chart/$max->amount[$f];
            $ratio = max([1.2,$ratio]);
            $max_chart = $ratio * $max->amount[$f];
            $min_chart = 0;
            $step = ($max_chart-$min_chart)/$vertical_items_no;
            if($step == 0){
                $vertical_items_no = 1;
            }
        }
?>
        <h4 class="tac"><?php echo $chart_title[0]; ?></h4>
        <div class="chart_div_holder">
        <div class="chart_div">
            <div class="vertical">
                <ul>
<?php
    for($i=0;$i<$vertical_items_no;$i++){
        $value = $max_chart-$step/2-$step*$i;
        echo "<li>$value</li>";
    }
?>
                </ul>
            </div>
            <div class="vertical_lines">
                <ul>
<?php
    for($i=0;$i<$vertical_items_no;$i++){
        echo "<li></li>";
    }
?>
                </ul>
            </div>
<?php
    $total_width = 910;
?>
            <div class="horizontal">
                <ul style="--iwidth:<?php echo $total_width/$k;  ?>">
<?php
    for($i=1;$i<$k;$i++){
        $text = $chart_data[$k-$i]["total"]->title;
        echo "<li><span>".$text."</span></li>";
    }
?>
                </ul>
            </div>
<?php
//    Line Chart with Markers for Time Group Data
?>
            <div class="chart line">
<?php
$total_height = 450;
$chart_show_series = $chart_series;
if($_SESSION[$cf]->sql_where["type"]=="amount"){$chart_show_series["total"] = "total";}

$color_i = 0;
foreach($chart_show_series as $cs_key=>$value){
    $ok = 0;
        if($_SESSION[$cf]->sql_where[$data_serie_name] == "multi"){
            if(isset($_SESSION[$cf]->sql_where["chart_serie_range"][$data_serie_name][$cs_key])){
                $ok=1;
            }
        }else if($_SESSION[$cf]->sql_where[$data_serie_name] == "all"){
            $ok = 1;
        }
        
        if($ok == 1){
            $color_i++;
        }
    if($cs_key == "total"){$color_i = "total";}
?>
                <ul style="--cs_color:rgb<?php echo $series_colors[$color_i]; ?>;">
<?php
    // Calculating Points
    
    $sigma_height = 0;
    for($i=1;$i<$k;$i++){
        $height = 0;
        if($max->percentage[$f])
        $height = (100/$ratio*$chart_data[$i][$cs_key]->percentage[$f]/$max->percentage[$f]);
        // Point = [X,Y] (From Bottom Left);
        $points[$i] = [(2*$i-1)*$total_width/($k-1)/2,$height/100*$total_height];
        $values[$i] = $chart_data[$i][$cs_key]->amount[$f];
        $sigma_height += $height;
    }
    // If there is useful data show chart
    if($sigma_height){
    // Calculating Path
    for($i=1;$i<$k-1;$i++){
        $x = $points[$i][0];
        $y = $points[$i][1];
        $dx = $points[$i+1][0]-$x; 
        $dy = $points[$i+1][1]-$y;
        $hypo = sqrt($dx*$dx+$dy*$dy);
        $ang = asin(($dy)/$hypo)*180/3.1415;
        $ang = -$ang;
        $v = $values[$i];
        echo "
                    <li style='--y: $y; --x: $x'>
                        <div class='data-point' data-value='$v'></div>
                        <div class='line-segment' style='--hypo: $hypo;--ang: $ang;'></div>
                    </li>
                        ";
    }
    $x = $points[$i][0];
    $y = $points[$i][1]; 
    $v = $values[$i]; 
    echo "
        <li style='--y: $y; --x: $x'>
            <div class='data-point' data-value='$v'></div>
        </li>
        ";

    }           
?>
                </ul>
            
<?php
}
?>
            </div>
<?php
    // Calculating Points & Path
//    
//    $total_height = 450;
//    $path_str = "";
//    
//$chart_series["total"] = ["here"];
//foreach($chart_series as $cs_key=>$value){
//    for($i=1;$i<$k;$i++){
//        $height = 0;
//        if($max->percentage[$f])
////        echo $ratio."<br><br><br>";
//        $height = (100/$ratio*$chart_data[$k-$i][$cs_key]->percentage[$f]/$max->percentage[$f]);
////        echo $ratio."<br>";
//        // Point = [X,Y] (From Bottom Left);
//        
//        $points[$i] = [$total_width - (2*$i-1)*$total_width/($k-1)/2,$total_height*(1-$height/100)];
//        if($i == 1){
//            $path_str = "M ";
//        }else{
//            $path_str .= " L ";
//        }
//        $path_str .= $points[$i][0]." ".$points[$i][1]." ";
//    }
//    echo '<path d=" '.$path_str.' "  FILL="none" STROKE="rgb'.$series_colors[$cs_key].'"/> ';
//    for($i=1;$i<$k;$i++){
//        echo '<circle onmouseover="show_data_point_value(this)" onmouseout="disable_floating_data()" cx="'.$points[$i][0].'" cy="'.$points[$i][1].'" r="7" name="'.round($chart_data[$k-$i][$cs_key]->amount[$f],2).'" FILL="rgb'.$series_colors[$cs_key].'"/>';
//    }
//}
?>
        </div>
        </div>
    </div>
    <br>
        <div class="cut wp100"></div>
    <br>        
    <div class="middle container">
<?php
    }
}
?>

<?php
        }
    }
?>