<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
$vertical_items_no = 5;

$chart_fields = array(
    "first_weight"=>["وزن اولیه","int"],
    "waste_weight"=>["وزن ضایعات","int"],
    "pure_weight"=>["وزن دستچین شده","int"],
    "quality"=>["کیفیت","int"],
    "price_cof"=>["ضریب قیمت","int"]
);
$chart_types = array(
    "amount"=>"مقدار",
    "percentage"=>"درصد"
);
$chart_filters = array(
//    "variable_name"=>["table_name","key_column","filter_table","filter_column","title"],
    "product"=>["products","id","activity","v1","name","محصول"],
    "supplier"=>["suppliers","id","activity","v5","name","تامین‌کننده"]
);
$chart_categories = array(
//    "variable_name"=>["table_name","key_column","filter_table","filter_column"],
    "product"=>["products","id","activity","v1","name","محصول"],
    "supplier"=>["suppliers","id","activity","v5","name","تامین‌کننده"]
);

$time_column = "activity.v3";

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
$data_serie_name = "supplier";
$chart_series = $select_values[$data_serie_name];
$chart_series_key_column = $chart_filters[$data_serie_name][3];
?>
<form class="date" action="<?php echo $URL; ?>" method="post">
<?php
    foreach($chart_filters as $v_name=>$v_tb_data){
?>
    <label for="<?php echo $v_name; ?>"><?php echo $v_tb_data[5]; ?>:</label> 
    <select name="<?php echo $v_name; ?>">
        <option selected value="all">همه <?php echo $v_tb_data[5]; ?>‌ها</option>
        <?php
            foreach($select_values[$v_name] as $value=>$title){
                $selected = "";
                if($_SESSION[$cf]->sql_where[$v_name] == $value)$selected=" selected ";
                echo '<option '.$selected.' value="'.$value.'">'.$title.'</option>';
            }
        ?>
    </select>
<?php
    }
?>

    <label for="horizontal">برحسب (محور افقی):</label> 
    <select name="horizontal">
        <?php
            $fields = array(
                "time"=>"زمان",
                "supplier"=>"تامین‌کننده",
                "product"=>"محصول"
            );
            $selected = " selected ";
            foreach($fields as $key=>$f){
                if($_SESSION[$cf]->sql_where["horizontal"] == $key)$selected=" selected ";
                echo '<option '.$selected.' value="'.$key.'">'.$f.'</option>';
                $selected = "";
            }
        ?>
    </select>
    <div class="cut"></div>


    <label for="items">موارد: </label>
<?php
    foreach($chart_fields as $key=>$f){
        $checked = "";
        if($_SESSION[$cf]->sql_where["items"][$key] == 1)$checked = " checked ";
        echo $f[0].'<input type="checkbox" name="items[]" '.$checked.' value="'.$key.'">';
    }
?>

    
    <label for="type">نوع: </label>
<?php
    foreach($chart_types as $key=>$f){
        $checked = "";
        if($_SESSION[$cf]->sql_where["type"] == $key)$checked = " checked ";
        echo $f.'<input type="radio" name="type" '.$checked.' value="'.$key.'">';
    }
?>

    <input type="submit" name="chart" value="تایید">
</form>
<style type="text/css">
form.date input[type="radio"],form.date input[type="checkbox"]{
    width:auto;
    padding:0;
    float:none;
    margin:0 5px;
    vertical-align: middle;
}

div.chart_legend ul li{
    float:right;
    margin:10px;
}
div.chart_legend div.legend_dot{
    width:15px;
    height:15px;
    border-radius:10px;
    margin:auto;
    margin-top:15px;
    
}
</style>
<?php
    if($_SESSION[$cf]->sql_where["horizontal"] == "time"){?>
    </div>
</div>
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
    foreach($chart_series as $cs_key=>$cs_title){
?>
                <li>
                    <?php echo $cs_title;?>
                    <div class="legend_dot" style="background-color:rgb<?php echo $series_colors[$cs_key]; ?>;"></div>
                </li>
<?php
    }
?>
            </ul>
        </div>
<?php
    }
?>
    </div>
</div>
<div class="cut w100p"></div>
<style type="text/css">
div.chart_div{
    width:960px;
    height:500px;
    position:relative;
}
div.chart_div div.horizontal ul{
    width:910px;
    display:flex;
    height:50px;
    position:absolute;
    bottom:0;
    border-top:1px solid red;
    align-items:center;
    justify-content:space-around;
    padding-top:20px;
}
div.chart_div div.horizontal ul li{
    text-align: center;
}
div.chart_div div.vertical ul{
    width:50px;
    display:flex;
    height:450px;
    position:absolute;
    left:0;
    border-right:1px solid red;
    align-content:space-around;
    flex-wrap:wrap;
/*    justify-content:space-around;*/
}
div.chart_div div.vertical ul li{
    width:50px;
    text-align:center;

}
div.chart_div div.vertical_lines ul{
    width:880px;
    display:flex;
    height:450px;
    position:absolute;
    right:15px;
    top:0;
    align-content:space-around;
    flex-wrap:wrap;
}
div.chart_div div.vertical_lines ul li{
    width:inherit;
    text-align:center;
    border-bottom:1px solid rgba(0,0,0,0.2);
}
div.chart_div div.chart{
    width:910px;
    height:450px;
    position:absolute;
    top:0;
    right:0;
}
div.chart_div div.chart.bar ul{
    width:inherit;
    height:inherit;
    display:flex;
    position:absolute;
    align-items:flex-end;
    justify-content:space-around;
}
div.chart_div div.chart.bar ul li{
    width:25px;
    background-color:maroon;
    bottom:0;
}
<?php
if($_SESSION[$cf]->sql_where["horizontal"] != "time"){
?>
    div.chart_div div.horizontal ul li span{
        transform:rotate(-90deg) translatex(30px);
        transform-origin: bottom right;
        display: block;
        width: 180px;
        height: 25px;
    }
    div.chart_div div.horizontal ul li{
        text-align:unset;
        width:25px;
    }
    div.chart_div {
        height:600px;
    }
    div.chart_div div.horizontal ul{
        height:150px;
        align-items:unset;
    }
<?php
}else{
?>
    div.chart_div div.chart.line svg{
        width:100%;
        height:100%;
        /*background-color:rgba(0,0,0,0.1);*/
    }
    div.chart_div div.chart.line svg path{
/*        stroke:maroon !important;*/
        stroke-width: 4px;
        fill:none;
        opacity:0.5;
    }
    div.chart_div div.chart.line svg path:hover{
        opacity:1;
    }
    div.chart_div div.chart.line svg circle{
/*        fill:maroon !important;*/
    }
    div.chart_div div.chart.line svg circle:hover{
/*        fill:gold !important;*/
/*        transform:scale(1.1);*/
    }
    div.chart_div div.chart.line svg g div{
        
    }
    div.chart_div div.chart.line svg g foreignObject{
        display:none;
    }
    div.chart_div div.chart.line svg g:hover foreignObject{
        display:block;
    }
    
    div.chart_div div.data_point_holder{
            position:absolute;
            top:0;
            right:0;
            border-bottom:1px solid maroon;
            width:120px;
/*            height:50px;*/
            margin-right:15px;
    }
    div.chart_div div.floating_value_div{
        background-color:white;
        border:1px solid maroon;
        border-radius:5px;
        position:absolute;
        z-index:1000;
        text-align:center;
        display:none;
        width:60px;
        height:30px;
    }
    div.chart_div div.floating_value_div.active{
        display:block;
    }
<?php
}
?>
</style>
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
}


//    Chart Filters
$time_where = db_time_condition($time_column,$_SESSION[$cf]->sql_where["start_date"],$_SESSION[$cf]->sql_where["end_date"],1);
foreach($chart_filters as $v_name=>$v_tb_data){
    if($_SESSION[$cf]->sql_where[$v_name] == "all"){
        $filter_where[$v_name] = "";
    }else{
        $filter_where[$v_name] = "AND ".$v_tb_data[2].".".$v_tb_data[3]." = '".$_SESSION[$cf]->sql_where[$v_name]."'";
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
    $period_length = ($end_ts - $start_ts)/(24*3600);
    if($period_length<11){
        $type = "daily";
    }else
    if($period_length<70){
        $type = "weekly";
    }else
    if($period_length<175){
        $type = "fortnight";
    }else
    if($period_length<366){
        $type = "monthly";
    }else
    if($period_length<4*365){
        $type = "seasonly";
    }else{
        $type = "yearly";
    }
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

//    Other Chart Categories
foreach($chart_categories as $v_name=>$v_tb_data){
    if($_SESSION[$cf]->sql_where["horizontal"] == $v_name){
        $st = "SELECT ".$v_tb_data[1].",".$v_tb_data[4]." FROM ".$v_tb_data[0]." WHERE del_flag=0";
        $st = $mysqli->prepare($st);
        if(!$st->execute()){
            echo "E";
            exit;
        }
        $res = $st->get_result();
        $k = 0;
        while($row = $res->fetch_assoc())
        {
            $where = "AND ".$v_tb_data[2].".".$v_tb_data[3]." = '".$row[$v_tb_data[1]]."'";
            $ranges[$k] = new range($row[$v_tb_data[4]],$where);
            $k++;
        }
        $filter_where[$v_name] = "";
    }
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
$tb = "activity";
foreach($ranges as $range){
    $chart_data[$k] = array();
    
    $chart_data[$k]["total"] = new bar();
    $chart_data[$k]["total"]->title = $range->title;
    
    foreach($chart_series as $key=>$value){
        $chart_data[$k][$key] = new bar();
    }
    
    $range_where = $range->range_where;
    
    $st = "SELECT 
    v1,v2,v4,v5
    FROM $tb 
    WHERE 
    $tb.del_flag = 0 AND
    $tb.asf = 'handpick'
    $ff_where
    $range_where
    ";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    
    while($row = $res->fetch_assoc())
    {
        $row["first_weight"] = $row["v2"];
        $row["waste_weight"] = $row["v4"];
        $row["pure_weight"] = $row["first_weight"] - $row["waste_weight"];
        $row["quality"] = 0;
        $row["price_cof"] = 0;
        foreach($chart_fields as $key=>$f){
            $chart_data[$k][$row[$chart_series_key_column]]->amount[$key] += $row[$key];
        }
    }
//        Category(Range) Total
    foreach($chart_series as $cs_key=>$value){
        foreach($chart_fields as $cf_key=>$f){
            $chart_data[$k]["total"]->amount[$cf_key] += $chart_data[$k][$cs_key]->amount[$cf_key];
        }
    }
//        Percentage Calculation for Chart Series
    foreach($chart_data[$k] as $chart_data_now){
        if($chart_data_now->amount["first_weight"]){
        $chart_data_now->amount["quality"] = $chart_data_now->amount["pure_weight"] / $chart_data_now->amount["first_weight"] * 100;}else{$chart_data_now->amount["quality"] = 0;}
         if($chart_data_now->amount["pure_weight"]){
        $chart_data_now->amount["price_cof"] = $chart_data_now->amount["first_weight"] / $chart_data_now->amount["pure_weight"];}
        else{$chart_data_now->amount["price_cof"] = 1;}
    }
    
//    Total Calculation
    foreach($chart_fields as $cf_key=>$f){
        $chart_data[0]->amount[$cf_key] += $chart_data[$k]["total"]->amount[$cf_key];
    }
    $k++;
}
//    Percentage Calculation for Chart Series
if($chart_data[0]->amount["first_weight"]){
    $chart_data[0]->amount["quality"] = 
    $chart_data[0]->amount["pure_weight"] / 
    $chart_data[0]->amount["first_weight"] * 100;
}else{
    $chart_data[0]->amount["quality"] = 0;
}
if($chart_data[0]->amount["pure_weight"]){
    $chart_data[0]->amount["price_cof"] = 
    $chart_data[0]->amount["first_weight"] / 
    $chart_data[0]->amount["pure_weight"];
}else{
    $chart_data[0]->amount["price_cof"] = 1;
}
// Calculating Absolute Percentages
foreach($chart_fields as $cf_key=>$f){
    if($_SESSION[$cf]->sql_where["items"][$cf_key] == 1){
        for($i=1;$i<$k;$i++){
            if($chart_data[0]->amount[$cf_key]){
                foreach($chart_data[$i] as $chart_data_now){
                    $chart_data_now->percentage[$cf_key] = 100*$chart_data_now->amount[$cf_key]/$chart_data[0]->amount[$cf_key];
                }
                
                if($max->amount[$cf_key]<$chart_data[$i]["total"]->amount[$cf_key])$max->amount[$cf_key]=$chart_data[$i]["total"]->amount[$cf_key];
            }
        }
        if($chart_data[0]->amount[$cf_key])$max->percentage[$cf_key] = 100*$max->amount[$cf_key]/$chart_data[0]->amount[$cf_key];
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
        
        if($_SESSION[$cf]->sql_where["type"]=="percentage"){
            $max_chart = (int)$max->percentage[$f];
            $max_chart = ((int)($max_chart/5)+1)*5;
            $vertical_items_no = $max_chart/10;
            $ratio = 1;
            if($max->percentage[$f])$ratio = $max_chart/$max->percentage[$f];
            $ratio = max([1.2,$ratio]);
            $max_chart = $ratio * $max->amount[$f];
            $min_chart = 0;
            $step = ($max_chart-$min_chart)/$vertical_items_no;
            if($step == 0){
                $vertical_items_no = 1;
            }
        }else{
            
            $decimals = pow(10,strlen((string)round($max->amount[$f],0))-1);
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
            <div class="horizontal">
                <ul>
<?php
    for($i=1;$i<$k;$i++){
        $text = $chart_data[$k-$i]["total"]->title;
        if($_SESSION[$cf]->sql_where["horizontal"] != "time"){
            $text = "<span>$text</span>";
        }
        echo "<li>".$text."</li>";
    }
?>

                </ul>
            </div>
<?php
    /////////////////////////////////////
    //  Bar Chart for Product & County Group Data
    if($_SESSION[$cf]->sql_where["horizontal"] != "time"){
?>
            <div class="chart bar">
                <ul>
<?php
    for($i=1;$i<$k;$i++){
        $height = 0;
        if($max->percentage[$f])$height = (100/$ratio*$chart_data[$k-$i]["total"]->percentage[$f]/$max->percentage[$f]);
        
        echo '<li style="height:'.$height.'%;"></li>';
    }
?>

                </ul>
            </div>
<?php
    
    }else{
//    Line Chart with Markers for Time Group Data
?>
            <div class="chart line">
                <svg>
<?php
    // Calculating Points & Path
    $total_width = 910;
    $total_height = 450;
    $path_str = "";
    
$chart_series["total"] = ["here"];
foreach($chart_series as $cs_key=>$value){
    for($i=1;$i<$k;$i++){
        $height = 0;
        if($max->percentage[$f])
//        echo $ratio."<br><br><br>";
        $height = (100/$ratio*$chart_data[$k-$i][$cs_key]->percentage[$f]/$max->percentage[$f]);
//        echo $ratio."<br>";
        // Point = [X,Y] (From Bottom Left);
        
        $points[$i] = [$total_width - (2*$i-1)*$total_width/($k-1)/2,$total_height*(1-$height/100)];
        if($i == 1){
            $path_str = "M ";
        }else{
            $path_str .= " L ";
        }
        $path_str .= $points[$i][0]." ".$points[$i][1]." ";
    }
    echo '<path d=" '.$path_str.' "  FILL="none" STROKE="rgb'.$series_colors[$cs_key].'"/> ';
    for($i=1;$i<$k;$i++){
        echo '<circle onmouseover="show_data_point_value(this)" onmouseout="disable_floating_data()" cx="'.$points[$i][0].'" cy="'.$points[$i][1].'" r="7" name="'.round($chart_data[$k-$i][$cs_key]->amount[$f],2).'" FILL="rgb'.$series_colors[$cs_key].'"/>';
    }
}
?>
                    
                </svg>
            </div>
<?php
    }
?>
            <div class="data_point_holder">
                مقدار: <span class="value_holder"></span>
                
            </div>
            <div class="floating_value_div">
                
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
<script type="text/javascript">
    function show_data_point_value(item){
        value = item.getAttribute('name');
        x = parseFloat(item.getAttribute('cx'))+20;
        y = parseFloat(item.getAttribute('cy'))-40;
        section_div = item.parentNode.parentNode.parentNode;
        value_holder = section_div.getElementsByClassName('value_holder')[0];
        value_holder.innerHTML = value;
        fvd = section_div.getElementsByClassName('floating_value_div')[0];
        fvd.innerHTML = value;
        fvd.setAttribute('style','top:'+y+';left:'+x);
        fvd.classList.add('active');
    }                
    function disable_floating_data(){
        fvds = document.getElementsByClassName('floating_value_div');
        for(i=0;i<fvds.length;i++){
            fvds[i].classList.remove('active');
        }
    }
</script>
<?php
        }
    }
?>