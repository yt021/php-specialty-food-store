<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<form class="date" action="<?php echo $URL; ?>" method="post">
    <label for="product">محصول:</label> 
    <select name="product">
        <option selected value="all">همه محصولات</option>
        <?php
            $st = "SELECT id,name FROM products WHERE del_flag = 0";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "E";
                exit;
            }
            $res = $st->get_result();
            
            while($row = $res->fetch_assoc())
            {
                $selected = "";
                if($_SESSION[$cf]->sql_where["product"] == $row["id"])$selected=" selected ";
                echo '<option '.$selected.' value="'.$row["id"].'">'.$row["name"].'</option>';
            }
        ?>
    </select>
    <label for="county">استان:</label> 
    <select name="county">
        <option selected value="all">همه استان‌ها</option>
        <?php
            $st = "SELECT county FROM cities WHERE del_flag = 0";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "E";
                exit;
            }
            $res = $st->get_result();
            
            while($row = $res->fetch_assoc())
            {
                $selected = "";
                if($_SESSION[$cf]->sql_where["county"] == $row["county"])$selected=" selected ";
                echo '<option '.$selected.' value="'.$row["county"].'">'.$row["county"].'</option>';
            }
        ?>
    </select>
    <label for="horizontal">برحسب (محور افقی):</label> 
    <select name="horizontal">
        <?php
            $fields = array(
                "time"=>"زمان",
                "county"=>"استان",
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
    $chart_fields = array(
        "revenue"=>"فروش",
        "profit"=>"سود",
        "sale"=>"تخفیف",
        "weight"=>"وزن",
        "orders"=>"تعداد سفارش",
        "packs"=>"تعداد بسته",
        "clients"=>"تعداد مشتری"
        
    );
    foreach($chart_fields as $key=>$f){
        $checked = "";
        if($_SESSION[$cf]->sql_where["items"][$key] == 1)$checked = " checked ";
        echo $f.'<input type="checkbox" name="items[]" '.$checked.' value="'.$key.'">';
    }
?>

    
    <label for="type">نوع: </label>
<?php
    $fields = array(
        "amount"=>"مقدار",
        "percentage"=>"درصد"
    );
    foreach($fields as $key=>$f){
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
</style>
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
}
?>
</style>
<?php


$bar_fields = ["revenue","sale","profit","weight","packs","orders","clients"];
$od_fields = ["revenue","sale","profit","weight","packs"];

class bar{
    public $amount;
    public $percentage;
    public $title;
    public function __construct(){
        $this->title="";
        $this->amount = array(
            "revenue"=>0,
            "sale"=>0,
            "profit"=>0,
            "weight"=>0,
            "packs"=>0,
            "orders"=>new order_by(),
            "clients"=>new order_by()
        );
        $this->percentage = array(
            "revenue"=>0,
            "sale"=>0,
            "profit"=>0,
            "weight"=>0,
            "packs"=>0,
            "orders"=>0,
            "clients"=>0
        );
    }
}

class order_data{
    public $values;
    public $oid;
    public $uid;
    public function __construct($oid){
        $this->values = array(
            "revenue"=>0,
            "sale"=>0,
            "profit"=>0,
            "weight"=>0,
            "packs"=>0
        );
        $this->uid = null;
        $this->oid = $oid;
    }
}

$max = new bar();
$max->amount["clients"] = 0;
$max->amount["orders"] = 0;

class range{
    public $title;
    public $range_where;
    public function __construct($title,$where){
        $this->title = $title;
        $this->range_where = $where;
    }
}

$ranges = array();

$vertical_items_no = 5;

$time_where = db_time_condition("orders.create_date",$_SESSION[$cf]->sql_where["start_date"],$_SESSION[$cf]->sql_where["end_date"],1);
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
                $where = db_time_condition("orders.create_date",CaSDate($period_start),CaSDate($period_end),1);
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
                $where = db_time_condition("orders.create_date",CaSDate($period_start),CaSDate($period_end),1);
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
                $where = db_time_condition("orders.create_date",CaSDate($period_start),CaSDate($period_end),1);
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
                $where = db_time_condition("orders.create_date",CaSDate($period_start),CaSDate($period_end),1);
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
                $where = db_time_condition("orders.create_date",CaSDate($period_start),CaSDate($period_end),1);
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
                $where = db_time_condition("orders.create_date",CaSDate($period_start),CaSDate($period_end),1);
                $ranges[$k] = new range($range_title,$where);
                
                $time_ts = $period_end;
                $s_date = CaSDate($time_ts);
                $k++;
            }
            break;
    }    
    $time_where = "";
}

if($_SESSION[$cf]->sql_where["county"] == "all"){
    $county_where = "";
}else{
    $county_where = "AND addresses.county = '".$_SESSION[$cf]->sql_where["county"]."'";
}
if($_SESSION[$cf]->sql_where["horizontal"] == "county"){
    $st = "SELECT county FROM cities WHERE del_flag=0";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $k = 0;
    while($row = $res->fetch_assoc())
    {
        $where = "AND addresses.county = '".$row["county"]."'";
        $ranges[$k] = new range($row["county"],$where);
        $k++;
    }
    $county_where = "";
}


if($_SESSION[$cf]->sql_where["product"] == "all"){
    $product_where = "";
}else{
    $product_where = "AND sub_orders.pid = '".$_SESSION[$cf]->sql_where["product"]."'";
}
if($_SESSION[$cf]->sql_where["horizontal"] == "product"){
    $_SESSION[$cf]->sql_where["product"] = 1;
    $st = "SELECT id,name FROM products WHERE del_flag = 0";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $k = 0;
    while($row = $res->fetch_assoc())
    {
        $where = "AND sub_orders.pid = '".$row["id"]."'";
        $ranges[$k] = new range($row["name"],$where);
        $k++;
    }
    $product_where = "";
}


$chart_data[0] = new bar();
$chart_data[0]->amount["clients"] = 0;
$chart_data[0]->amount["orders"] = 0;

$k = 1;

$tb = "orders";
$tb2 = "sub_$tb";
$tb3 = "addresses";

foreach($ranges as $range){
    
    $chart_data[$k] = new bar();
    $chart_data[$k]->title = $range->title;
    $range_where = $range->range_where;
    
    $st = "SELECT 
    $tb.id,$tb.uid,$tb.create_date,$tb.cart_pure,$tb.sale_total,
    $tb2.pid,$tb2.number,$tb2.weight 
    FROM $tb 
    CROSS JOIN $tb2
    CROSS JOIN $tb3
    WHERE 
    $tb.id = $tb2.oid AND
    $tb.aid = $tb3.id AND
    $tb.del_flag = 0 AND
    $tb.state >= 1
    $time_where
    $county_where
    $product_where
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
        $oid = $row["id"];
        if($order->oid != $oid){
            if($_SESSION[$cf]->sql_where["product"]=="all"){
                $order->values["profit"] -= $order->values["sale"];
            }
            foreach($od_fields as $f){
                $chart_data[$k]->amount[$f] += $order->values[$f];
            }
            $chart_data[$k]->amount["clients"]->add_item($order->uid);
            $chart_data[$k]->amount["orders"]->add_item($order->oid);
            
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
        //if($_SESSION[$cf]->sql_where["items"]["profit"] == 1){
//            $pf = product_finance($row["pid"],$row["weight"],$row["create_date"]);
//            $order->values["profit"] += $row["number"]*$pf["profit"];
//        }
    }
    $order->values["profit"] -= $order->values["sale"];
    foreach($od_fields as $f){
        $chart_data[$k]->amount[$f] += $order->values[$f];
    }
    $chart_data[$k]->amount["clients"]->add_item($order->uid);
    $chart_data[$k]->amount["orders"]->add_item($order->oid);
            
    $chart_data[$k]->amount["clients"] = substr_count($chart_data[$k]->amount["clients"]->order_str("users"),",");
    $chart_data[$k]->amount["orders"] = substr_count($chart_data[$k]->amount["orders"]->order_str("users"),",") - 1;
    
    foreach($bar_fields as $f){
        $chart_data[0]->amount[$f] += $chart_data[$k]->amount[$f];
    }

    $k++;
    
}
foreach($bar_fields as $f){
    if($_SESSION[$cf]->sql_where["items"][$f] == 1){
        for($i=1;$i<$k;$i++){
            if($chart_data[0]->amount[$f]){
                
                $chart_data[$i]->percentage[$f] = 100*$chart_data[$i]->amount[$f]/$chart_data[0]->amount[$f];
                
                if($max->amount[$f]<$chart_data[$i]->amount[$f])$max->amount[$f]=$chart_data[$i]->amount[$f];
            }
        }
        if($chart_data[0]->amount[$f])$max->percentage[$f] = 100*$max->amount[$f]/$chart_data[0]->amount[$f];
    }
}

?>
<div id="sect4" class="sect ">
    <div class="middle container">
<?php
//$chart_fields = array(
//            "revenue"=>"فروش",
//            "profit"=>"سود",
//            "weight"=>"وزن",
//            "orders"=>"تعداد سفارش",
//            "packs"=>"تعداد بسته",
//            "clients"=>"تعداد مشتری",
//            "sale"=>"تخفیف"
//        );
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
            $min_chart = 0;
            $step = ($max_chart-$min_chart)/$vertical_items_no;
            if($step == 0){
                $vertical_items_no = 1;
            }
        }else{
            $decimals = pow(10,strlen((string)$max->amount[$f])-1);
            $max_chart = ceil(round((float)$max->amount[$f]/($decimals),1))*$decimals;
//            $vertical_items_no = ($max_chart/$decimals);
            $ratio = 1;
            if($max->amount[$f])$ratio = $max_chart/$max->amount[$f];
            $min_chart = 0;
            $step = ($max_chart-$min_chart)/$vertical_items_no;
            if($step == 0){
                $vertical_items_no = 1;
            }
        }
?>
        <h4 class="tac"><?php echo $chart_title; ?></h4>
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
        $text = $chart_data[$k-$i]->title;
        if($_SESSION[$cf]->sql_where["horizontal"] != "time"){
            $text = "<span>$text</span>";
        }
        echo "<li>".$text."</li>";
    }
?>

                </ul>
            </div>
            <div class="chart bar">
                <ul>
<?php
    for($i=1;$i<$k;$i++){
        $height = 0;
        if($max->percentage[$f])$height = (100/$ratio*$chart_data[$k-$i]->percentage[$f]/$max->percentage[$f]);
        
        echo '<li style="height:'.$height.'%;"></li>';
    }
?>

                </ul>
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