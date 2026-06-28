<?php
    if(isset($indexed)){
        if($indexed == 1){?>

<main>
    <div class="content checkout_flow_page">
        <div class="checkout_flow_header">
            <h1 class="checkout_flow_kicker"><?php echo ($_SESSION["address"]->data()["county"] == "شهر تهران") ? "انتخاب زمان تحویل" : "انتخاب نوع ارسال"; ?></h1>
            <p class="checkout_flow_title"><?php echo ($_SESSION["address"]->data()["county"] == "شهر تهران") ? "بازه زمانی مناسب برای تحویل سفارش را انتخاب کنید" : "روش ارسال سفارش را با توجه به زمان و هزینه انتخاب کنید"; ?></p>
        </div>
        <style type="text/css">
            .checkout_flow_page > h1.tac{
                display:none;
            }
            .checkout_flow_page .send_date_selection{
                border:1px solid rgba(0,0,0,0.12);
                border-radius:14px;
                background:#fff;
                padding:14px 16px;
                box-sizing:border-box;
            }
        </style>
        
<?php
    if($_SESSION["address"]->data()["county"] == "شهر تهران"){
?>
<h1 class="tac">انتخاب زمان تحویل</h1>
<div class="user_hint">
    مشتری گرامی خواهشمند است نکات زیر را رعایت فرمایید:
    <ol>
        <li>
            با توجه به برنامه زمانی خود، یک روز و نوبت تحویل را انتخاب کنید.
        </li>
<?php
        $out_of_price = getVarFromDB('sd_shifts','price','id',3);
        // echo "<li>
            // در صورت انتخاب گزینه ارسال خارج از نوبت، حداقل هزینه ارسال $out_of_price تومان است. مازاد هزینه پیک نیز بر عهده گیرنده است و در زمان تحویل تسویه خواهد شد.
        // </li>";

?>        
    </ol>
</div>



<?php
    }else{
        if($_SESSION["cart"]->has_category("پکیج ولنتاین") > 1000){
?>
<h1 class="tac">انتخاب روز تحویل</h1>
<!--<div class="user_hint">-->
<!--مشتری گرامی با توجه به اینکه جعبه ولنتاین در سفارش شما وجود دارد لطفا به نکات زیر توجه فرمایید:-->
<!--    <ol>-->
<!--        <li>-->
<!--            به دلیل محدودیت تعداد سفارشات جعبه ولنتاین برای هر روز زمان تقریبی دریافت بسته خود را انتخاب نمایید، توجه داشته باشید که این زمان تقریبی است و با توجه به زمان پست بین یک یا دو روز احتمال جابه جایی وجود دارد.-->
<!--        </li>-->
<!--        <li>-->
<!--            سفارش های دارای جعبه ولنتاین تنها به صورت پست پیشتاز ارسال خواهد شد.-->
<!--        </li>-->
<!--    </ol>-->
<!--</div>-->
<div class="user_hint error">
    زمان سفارش‌گیری جعبه‌های ولنتاین برای خارج از شهر تهران به اتمام رسیده است.
</div>
<a class="btn middle" onclick="sub_show('clear','0')">
    بازگشت به سبد خرید
</a>
<?php
        }else{
            $sefareshi = getVarFromDB('sd_shifts','price','id',1);
            $pishtaz = getVarFromDB('sd_shifts','price','id',2);
?>
<h1 class="tac">انتخاب نوع ارسال</h1>
<div class="user_hint">
    مشتری گرامی خواهشمند است نکات زیر را رعایت فرمایید:
    <ol>
        <li>
            با توجه به تفاوت‌های دو نوع پست سفارشی و پیشتاز، نوع ارسال سفارش خود را انتخاب کنید.
        </li>
        <li>
            پست سفارشی:<br>
            در این روش، مرسوله از طریق زمینی ارسال خواهد شد. زمان تحویل مرسوله،2 الی 5 روز پس از ارسال است. هزینه این روش، <?php echo price_sep($sefareshi); ?> تومان است.
        </li>
        <li>
            پست پیشتاز:<br>
            در این روش، مرسوله از طریق هوایی ارسال خواهد شد. زمان تحویل مرسوله، 1 الی 2 روز پس از ارسال است. هزینه این روش، <?php echo price_sep($pishtaz); ?> تومان است.
        </li>
        <li>
            سفارش‌ها 3 تا 5 روز کاری پس از ثبت سفارش ارسال می‌گردند.
        </li>
    </ol>
</div>
<?php
        }
    }
?>

<?php
    if(isset($_SESSION["cart_notice"])){
        $error = $_SESSION["cart_notice"];
        unset($_SESSION["cart_notice"]);
    }
?>
            <div id="user_error" class="user_hint error <?php if(!isset($error))echo "hide"; ?>">
                <?php if(isset($error))echo substr($error,1); ?>
            </div>

<style type="text/css">
div.send_date_selection{
    width:550px;
    margin:auto;
}
div.send_date_selection table{
    margin:auto;
    border-collapse: collapse;
}
div.send_date_selection table th{
    width:200px;
}
div.send_date_selection table tr:nth-child(even){
    background-color: rgba(200,50,0,0.1);
}
div.send_date_selection table tr{
    border-bottom:1px solid maroon;
}
div.send_date_selection table tr:last-child{
/*    border-bottom:0;*/
}
div.send_date_selection table tr:nth-child(even){
    background-color: rgba(200,50,0,0.1);
}
div.send_date_selection a.btn{
    width:155px;
    margin:10px;
    line-height:1.4;
    padding:10px;
    background-color:white;
    color:maroon;
    border:1px solid maroon;
}
div.send_date_selection a.btn.selected{
    background-color:maroon;
    color:white;
}
br.small_window{
    display:none;
}
div.send_date_selection a.btn.half{
    width:calc(50% - 20px);
}
div.send_date_selection a.btn.full{
    width:calc(100% - 20px);
}

@media only screen and (max-width: 800px){
    div.send_date_selection{
        width:500px;
        margin:auto;
    }
    div.send_date_selection a.btn{
        width:140px;
        margin:10px 5px;
        font-size:14px;
        line-height:1.4
    }
    div.send_date_selection a.btn.half{
        width:calc(50% - 10px);
    }
    div.send_date_selection a.btn.full{
        width:calc(100% - 10px);
    }
}
@media only screen and (max-width: 690px){
    div.send_date_selection{
        width:440px;
        margin:auto;
    }
    div.send_date_selection table th{
        width:160px;
        font-size:12px;
    }
    div.send_date_selection a.btn{
        width:125px;
/*        font-size:12px;*/
        line-height:1.4
    }
}
@media only screen and (max-width: 580px){
    div.send_date_selection{
        width:min(320px,100%);
        margin:auto;
    }
    div.send_date_selection table,
    div.send_date_selection tbody,
    div.send_date_selection tr,
    div.send_date_selection th,
    div.send_date_selection td{
        display:block;
        width:100%;
    }
    div.send_date_selection table th{
        width:100%;
        padding:8px 0 4px;
        text-align:center;
        background:transparent !important;
    }
    div.send_date_selection table td{
        padding:0;
        background:transparent !important;
    }
    div.send_date_selection table tr{
        padding:8px 0 12px;
        border-bottom:1px solid rgba(128,0,0,0.28);
        background-color:transparent !important;
        overflow:hidden;
    }
    div.send_date_selection table tr:first-child{
        border-top:1px solid rgba(128,0,0,0.28);
    }
    div.send_date_selection a.btn{
        width:100%;
        font-size:12px;
        padding:7px 6px;
        margin:4px 0;
    }
    div.send_date_selection table td + td{
        border-top:1px solid rgba(128,0,0,0.16);
        padding-top:4px;
        margin-top:4px;
    }
    br.small_window{
        display:block;
    }
    span.big_window{
        display:none;
    }
    div.send_date_selection a.btn.half{
        width:100%;
    }
    div.send_date_selection a.btn.full{
        width:100%;
    }
    div.send_date_selection table td:last-child a.btn{
        margin-bottom:0;
    }
}
@media only screen and (max-width: 360px){
    div.send_date_selection{
        width:100%;
    }
    div.send_date_selection a.btn,
    div.send_date_selection a.btn.half,
    div.send_date_selection a.btn.full{
        width:100%;
        margin:4px 0;
    }
}
@media only screen and (max-width: 356px){
    main div.content{
        width:100%;
    }
}
</style>

        <form id="sds_area" class="info" action="<?php echo $URL; ?>" method="post">
<?php
    if(isset($_SESSION["send_date"]) && $_SESSION["send_date"] == ""){
        unset($_SESSION["send_date"]);
    }
    if(isset($_SESSION["send_date"])){
        $data = $_SESSION["send_date"]->data();
    }else{
        $data["sdid"]="";
        $data["shift"]="";
        $data["n2s"]="";
    }
?>
                <input id="shift_input" name="shift" value="<?php echo $data["shift"]; ?>" class="hide" type="text">
                <input id="id_input" name="id" value="<?php echo $data["sdid"]; ?>" class="hide" type="text">
                <input id="alt_input" name="alt" value="<?php echo $data["n2s"]; ?>" class="hide" type="text">
                <div class="send_date_selection">
                
<?php
    if($_SESSION["address"]->data()["county"] == "شهر تهران"){
?>
<?php
    $timestamp = time();
    $sd_start = getVarFromDB("sd_setting","value","flag","sd_start");
    $sd_length = getVarFromDB("sd_setting","value","flag","sd_length");
    $sd_limit = getVarFromDB("sd_setting","value","flag","sd_limit");
    $start_date = $timestamp + $sd_start*24*3600;
    $end_date = $timestamp + ($sd_start+$sd_length+2)*24*3600;
    $start_ts = date("Y-m-d H:i:s",$start_date);
    $end_ts = date("Y-m-d H:i:s",$end_date);
    
    // $end_ts = '2020-02-15 00:00:00';
    
    $st = "SELECT id,date,state,order_no FROM send_date WHERE date>'$start_ts' AND date<'$end_ts' AND order_no < $sd_limit ORDER BY date ASC";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $k = 0;
    $days = array();
    include_once $GLOBALS['bu']."modules/jdf.php"; 

    while($row = $res->fetch_assoc())
    {
        $id = $row["id"];
        $state = $row["state"];
        
        $st3 = "SELECT COUNT(id) as cid FROM orders WHERE recieve_date = '".$row["date"]."' AND state > 0 GROUP BY recieve_date";
        
        
        $order_no = 0;
        $cres = return_sel_sql($st3)->fetch_assoc();
        if(isset($cres['cid']))
        $order_no = $cres['cid'];
        // $order_no = return_sel_sql($st3)->fetch_assoc()['cid'];
        
        // echo $order_no_2."<br>";
        
        $datets = strtotime($row["date"]);
        $date = cd_show_date($datets);
        // $order_no = $row["order_no"];
        // echo $order_no."<br><br>";
        $days[$date] = [$id,$state,$datets,$order_no];
    }
    $show_turns = array();
    
    $pickup_hour = (int)getVarFromDB("sd_setting","value","flag","pickup");
    $active_transporter = getVarFromDB("sd_setting","value","flag","tp_id");
    $st = "SELECT * FROM sd_shifts WHERE transporter_id = $active_transporter AND del_flag = 0 ORDER BY start_hour ASC,id ASC";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $shifts = array();
    while($row = $res->fetch_assoc())
    {
        $shifts[$row["id"]] = [$row["name"],(int)$row["start_hour"],(int)$row["end_hour"]];
    }
    foreach($days as $date=>$value){
        switch($value[1]){
            case "official":
                if(isset($show_turns[$date])){
                    foreach($shifts as $shift =>$v){
                        if(isset($show_turns[$date][$shift])){
                            if($show_turns[$date][$shift][1] == "send"){
                                if($show_turns[$date][$shift][3] > 0 && $show_turns[$date][$shift][3] < 2){
                                    $show_turns[$date][$shift][1] = "send_postpone";
                                    $date_p1_ts = $show_turns[$date][$shift][2]+24*3600;
                                    $date_p1 = cd_show_date($date_p1_ts);
                                    $show_turns[$date_p1][$shift] = [$show_turns[$date][$shift][0],"send",$date_p1_ts,$show_turns[$date][$shift][3]+1,$show_turns[$date][$shift][4]];
                                }else{
                                    $show_turns[$date][$shift][1] = "official";
                                    $date_p1_ts = $show_turns[$date][$shift][2]+24*3600;
                                    $date_p1 = cd_show_date($date_p1_ts);
                                    $show_turns[$date_p1][$shift][1] = "official";
                                }
                            }
                        }
                    }
                }else{ 
                    foreach($shifts as $shift=>$v){
                        $show_turns[$date][$shift] = $value;
                    }
                }
                break;
            case "internal":
                break;
            default:
                $date_p1_ts = $value[2];
                $date_p1 = cd_show_date($date_p1_ts);
                foreach($shifts as $shift=>$v){
                    if($v[1]>$pickup_hour){
                        $show_turns[$date_p1][$shift] = [$value[0],"send",$date_p1_ts,0,$value[3]];
                    }
                }
                // send for next day
                $date_p2_ts = $date_p1_ts+24*3600;
                $date_p2 = cd_show_date($date_p2_ts);
                foreach($shifts as $shift=>$v){
                        $show_turns[$date_p2][$shift] = [$value[0],"send",$date_p2_ts,1,$value[3]];
                }
                break;
        }
    }
?>
<table>
    <tbody>
<?php
    $k = 0;
    foreach($show_turns as $date=>$turn){
        if($k > $sd_length)break;
        else{$k++;}
        
        $thisdate = 0;
        foreach($shifts as $shift=>$v){
            if(isset($turn[$shift]) && $turn[$shift][1] == "send" && $turn[$shift][4]<$sd_limit){
                $thisdate = 1;   
            }
        }
        if($thisdate == 1){
?>
            <tr>
                <th>
                    <?php echo $date; ?>
                </th>
<?php
            foreach($shifts as $shift=>$v){
                
                
                
                $v_name = $v[0];
                $v_time = $v[1]." - ".$v[2];
                if(isset($turn[$shift]) && ( $turn[$shift][1] == "send") && $turn[$shift][4]<$sd_limit){
                    $sdid = $turn[$shift][0];
                    $n2s = $turn[$shift][3];
                    if(! ($sdid==541 && $shift == 16)){
                    $selected = "";
                    if(isset($_SESSION["send_date"]) && $_SESSION["send_date"]->sdid == $sdid && $_SESSION["send_date"]->n2s == $n2s && $_SESSION["send_date"]->shift == $shift){
                        $selected = " selected ";
                    }
                    echo "<td>";
                    echo "
                            <a class='btn mid fr $selected' onclick='select_date(this)' id='$sdid' alt='$n2s' name='$shift' >$v_name ($v_time)</a>
                        ";
                    echo "</td>";
                
                    }
                }else{
                    echo "<td></td>";
                }
            }
?>
            </tr>
<?php
        }
    }
?>
    </tbody>
</table>

<?php
    if(isset($_SESSION["send_date"]) && $_SESSION["send_date"]->sdid == "courier" && $_SESSION["send_date"]->shift == "3"){$selected = " selected ";}else{$selected="";}
    // if(isset($_SESSION["a_logged"])){
    echo "
    <a class='btn full  fr $selected' onclick='select_date(this)' id='courier' alt='0' name='3' >ارسال خارج از نوبت با پیک - برای هماهنگی با شما تماس خواهیم گرفت.</a>
";
    // }T
?>
<?php
    }else{
        if($_SESSION["cart"]->has_category("پکیج ولنتاین") > 1000){
            $timestamp = time();
            $sd_start = getVarFromDB("sd_setting","value","flag","sd_start");
            $sd_length = getVarFromDB("sd_setting","value","flag","sd_length");
            $sd_limit = getVarFromDB("sd_setting","value","flag","sd_limit");
            $start_date = $timestamp + ($sd_start+2)*24*3600;
            $end_date = $timestamp + ($sd_start+$sd_length+2)*24*3600;
            $start_ts = date("Y-m-d H:i:s",$start_date);
            $end_ts = date("Y-m-d H:i:s",$end_date);
            // $end_ts = '2020-02-15 00:00:00';
            
            $st = "SELECT id,date,state,order_no FROM send_date WHERE date>'$start_ts' AND date<'$end_ts'";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "E";
                exit;
            }
            $res = $st->get_result();
            $k = 0;
            $days = array();
            include_once $GLOBALS['bu']."modules/jdf.php"; 

            while($row = $res->fetch_assoc())
            {
                $id = $row["id"];
                $state = $row["state"];
                $datets = strtotime($row["date"]);
                $date = cd_show_date($datets);
                $order_no = $row["order_no"];
                $days[$date] = [$id,$state,$datets,$order_no];
            }
            foreach($days as $date=>$value){
                switch($value[1]){
                    case "official":
                        break;
                    case "internal":
                        break;
                    default:
                        $n2s = $value[0];
                        $sdid = "post";
                        $shift = 2;
                        $shift_name = $date;
                        $selected = "";
                        if(isset($_SESSION["send_date"]) && $_SESSION["send_date"]->sdid == $sdid && $_SESSION["send_date"]->n2s == $n2s && $_SESSION["send_date"]->shift == $shift){
                            $selected = " selected ";
                        }
                        echo "<a class='btn half fr $selected' onclick='select_date(this)' id='$sdid' alt='$n2s' name='$shift' >$shift_name</a>";
                        
                        break;
                }
            }
        }else{
            
        
?>
<?php
    $tp_id = getVarFromDB("transporters","id","name","پست");
    $st = "SELECT id,name,price FROM sd_shifts WHERE transporter_id = 1 AND del_flag = 0";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row = $res->fetch_assoc())
    {
        $shift = $row["id"];
        $price = $row["price"];
        $sdid = "post";
        $shift_name = $row["name"];
        
        if(isset($_SESSION["send_date"]) && $_SESSION["send_date"]->sdid == $sdid && $_SESSION["send_date"]->shift == $shift){$selected = " selected ";}else{$selected="";}
        
        echo "<a class='btn full fr $selected' onclick='select_date(this)' id='$sdid' alt='0' name='$shift' >$shift_name</a>";
    }
    }
?>
<?php
    }
?>

<?php
if($_SESSION["cart"]->has_category("پکیج ولنتاین") > 1000 && $_SESSION["address"]->data()["county"] != "شهر تهران"){
}else{

?>
           <br>
           <div class="cb"></div> 
                </div>
                <div class="cb"></div>
                <div class="checkout_nav_row">
                    <a class="btn middle" onclick="sub_show('clear','<?php echo cart_step_back_level(); ?>')">
                        <?php echo cart_step_back_text(); ?>
                    </a>
                    <input name="submit" type="submit" class="btn middle" name="submit" value="<?php echo cart_step_next_text(); ?>">
                </div>
<?php
}
?>
            </form>
</div>
</main>

<script src="<?php echo asset_url('js/selection.js'); ?>"></script>
<script type="text/javascript">
    function select_date(item){
        if(document.getElementById("sds_area").getElementsByClassName('selected')[0])
        document.getElementById("sds_area").getElementsByClassName('selected')[0].classList.remove('selected');
        item.classList.add('selected');
        document.getElementById('id_input').value = item.id;
        document.getElementById('shift_input').value = item.getAttribute('name');
        document.getElementById('alt_input').value = item.getAttribute('alt');
        return;
    }
</script>
<?php
        }
    }
?>
