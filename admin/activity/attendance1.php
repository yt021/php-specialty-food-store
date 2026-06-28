<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $state = $_SESSION[$cf]->state;
    if(isset($_POST["id_del"]) && var_exist($_POST["id_del"],$tb,"id")){
        $del_flag = (int)getVarFromDB($tb,"del_flag","id",$_POST["id_del"]);
        $del_flag = 1 - $del_flag;
        updateInDB($tb,"del_flag",$del_flag,"id",$_POST["id_del"]);
    }
    
    $fields[1] = ["hour","minute"];
    $labels = array(
        "hour"=>"ساعت",
        "minute"=>"دقیقه",
    );
    $traffic_labels = ["","ورود","خروج"];
    $row = array(
        "date"=>CaSDate(time()),
        "hour"=>jdate("G",time(),"","","en"),
        "minute"=>jdate("i",time(),"","","en"),
        "traffic"=>1,
        "eid"=>$_SESSION[$cf]->sql_where["eid"]
    );
    
    
?>
    <form action="<?php echo $URL ?>" method="post">
        <div class="ssrv">
            <div class="ssrv_title">تردد جدید</div>        
            <div class="ssrv_dtl">
                <div class="form fr">
                    <div class="form_item  ">
                        <label>شخص: </label>
                        <select name="eid">
                        <?php
    $st = "SELECT id,name FROM employees
    WHERE del_flag = 0";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row2 = $res->fetch_assoc())
    {
        $name = $row2["name"];
        $id = $row2["id"];
        $selected = "";
        if($row["eid"] == $id)$selected=" selected ";
?>
        <option <?php echo $selected; ?> value="<?php echo $id; ?>"><?php echo $name; ?></option>
<?php
    }
?>
                        </select>
                    </div>
                    <div class="form_item date" id="date_box">
                        تاریخ
                        <label for="day">روز:</label> 
                        <select name="day">
                            <?php
                                for($i=1;$i<=31;$i++){
                                    $selected = "";
                                    if($row["date"]["day"] == $i)$selected=" selected ";
                                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                }
                            ?>
                        </select>
                        <label for="month">ماه:</label> 
                        <select name="month">
                            <?php
                                for($i=1;$i<=12;$i++){
                                    $selected = "";
                                    if($row["date"]["month"] == $i)$selected=" selected ";
                                    include_once $GLOBALS['bu']."modules/jdf.php";
                                    $timetext = jmktime(1,0,0,$i,1,1397);
                                    $month = jdate("F",(int)$timetext,"","","en");
                                    echo '<option '.$selected.' value="'.$i.'">'.$month.'</option>';
                                }
                            ?>
                        </select>
                        <label for="year">سال:</label> 
                        <select name="year">
                            <?php
                                include_once $GLOBALS['bu']."modules/jdf.php";
                                $year = (int)jdate("Y","","","","en");
                                for($i=1397;$i<=$year;$i++){
                                    $selected = "";
                                    if($row["date"]["year"] == $i)$selected=" selected ";
                                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                }
                            ?>
                        </select>
                    </div>
<?php
    foreach($fields[1] as $f){
?>
        <div class="form_item ">
            <label><?php echo $labels[$f]; ?>: </label>
            <input name="<?php echo $f; ?>" type="text" value="<?php echo $row[$f]; ?>">
        </div>
<?php
    }
?>                    
                    <div class="form_item  ">
                        <label>تردد: </label>
                        <select name="traffic">
                            <?php
                                for($i=1;$i<=2;$i++){
                                    $selected = "";
                                    if($row["traffic"] == $i)$selected=" selected ";
                                    echo '<option '.$selected.' value="'.$i.'">'.$traffic_labels[$i].'</option>';
                                }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>        
        <input name="new_act" class="hide" value="1">
        <input type="submit" name="edit" class="btn" value="ثبت">
    </form>
        
        </div>  
    </div>
    <div class="cut w100p"></div>
    <div id="sect3" class="sect ">
        <div class="middle container">
            <h3 class="tac">عملکرد ماهیانه</h3>
            <br>
            <form class="" action="<?php echo $URL; ?>" method="post">
            <div class="form_item date">
        انتخاب شخص:
        <select name="eid">
<?php
    $st = "SELECT id,name FROM employees
    WHERE del_flag = 0";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row = $res->fetch_assoc())
    {
        $name = $row["name"];
        $id = $row["id"];
        $selected = "";
        if($_SESSION[$cf]->sql_where["eid"] == $id)$selected=" selected ";
?>
            <option <?php echo $selected; ?> value="<?php echo $id; ?>"><?php echo $name; ?></option>
<?php
    }
?>
        </select>
        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        انتخاب ماه کاری:
        <label for="month">ماه:</label> 
        <select name="month">
            <?php
                for($i=1;$i<=12;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where[$date_name]["month"] == $i)$selected=" selected ";
                    include_once $GLOBALS['bu']."modules/jdf.php";
                    $timetext = jmktime(1,0,0,$i,1,1397);
                    $month = jdate("F",(int)$timetext,"","","en");
                    echo '<option '.$selected.' value="'.$i.'">'.$month.'</option>';
                }
            ?>
        </select>
        <label for="year">سال:</label> 
        <select name="year">
            <?php
                include_once $GLOBALS['bu']."modules/jdf.php";
                $year = (int)jdate("Y","","","","en");
                for($i=1397;$i<=$year;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where[$date_name]["year"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        </div>
        <input class="btn" type="submit" name="show_log" value="مشاهده عملکرد">
    </form>
<?php
    $month = (int)$_SESSION[$cf]->sql_where[$date_name]["month"];
    $year = (int)$_SESSION[$cf]->sql_where[$date_name]["year"];
    $sql_start_ts_str = createSQLTime($year,$month,1);
    $sql_end_ts_str = createSQLTime($year,$month+1,1);
    $where = " $tb.v3 >= '$sql_start_ts_str' AND $tb.v3 < '$sql_end_ts_str'";
    if(isset($where))$where = "AND $where";else $where = "";
?>
            <!--<a class="btn half" onclick="document.getElementById('progress_div').classList.remove('hide')">مشاهده عملکرد</a>-->
    
        </div>  
    </div>
    <div class="cut w100p"></div>
<?php
    $eid = $_SESSION[$cf]->sql_where["eid"];
    if($eid == 0){}else{
?>
    <div id="progress_div" class="sect">
        <div class="middle container">

        
            <h3 class="tac">خلاصه عملکرد</h3>
            <br>
            <table class="tracking">
                <thead>
                    <tr>
                        <th>روزهای حضور</th>
                        <th>ساعت حضور</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
<?php
    $st = "SELECT count(*) as value FROM (SELECT v3 FROM $tb
    WHERE eid = $eid AND asf = '$state' AND del_flag = 0 $where GROUP BY v3) AS sub_query";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $days = $res->fetch_assoc()["value"];
    echo "<td>$days</td>";
?>
<?php
    $st = "SELECT sum(v1) as sv1,v2 FROM $tb
    WHERE eid = $eid AND asf = '$state' AND del_flag = 0 $where GROUP BY v2";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $mins = 0;
    while($row = $res->fetch_assoc()){
        switch($row["v2"]){
            case 1:
                $mins -= $row["sv1"];
                break;
            case 2:
                $mins += $row["sv1"];
                break;
        }
        
    }
    $hours = ceil($mins/60);
    echo "<td>$hours</td>";
?>
                    </tr>
                </tbody>
            </table>
    <div class="cut w100p"></div>
    
<style type="text/css">
    td.t_in{
        background-color:rgba(52, 204, 69, 0.5);
    }
    td.t_out{
        background-color:rgba(251, 118, 1, 0.5);
    }
    tr.internal{
/*        background-color:rgba(255, 196, 57, 0.4);*/
    }
    tr.official{
        background-color:rgba(166, 4, 4, 0.4);
    }

</style>
            <h3 class="tac">عملکرد ماهیانه</h3>
            <br>
            <table class="tracking">
                <thead>
                    <tr>
                        <th>تاریخ</th>
                        <th>روز</th>
                        <th>1</th>
                        <th>2</th>
                        <th>3</th>
                        <th>4</th>
                        <th>5</th>
                        <th>6</th>
                        <th>7</th>
                        <th>8</th>
                    </tr>
                </thead>
                <tbody>
<?php
    $month_end = monthNoDaysJ($month,$year);
    for($day = 1;$day<=$month_end;$day++){
        $p_start = createSQLTime($year,$month,$day);
        $p_end = createSQLTime($year,$month,$day+1);
        $p_where = " $tb.v3 >= '$p_start' AND $tb.v3 < '$p_end'";
        
        $sql_time = createSQLTime($year,$month,$day,12);
        $day_state = getVarFromDB("send_date","state","date",$sql_time);
        
        $day_name = CaSDate(create_ts($year,$month,$day,12))["day_name"];
        $date_string = correctDate_rqm($p_start);
        
        echo "
            <tr class='$day_state'>
                <td>$date_string</td>
                <td>$day_name</td>
        ";        
        
        $st = "SELECT v1,v2 FROM $tb
        WHERE eid = $eid AND asf = '$state' AND del_flag = 0 AND $p_where ORDER BY v1 ASC";
        $st = $mysqli->prepare($st);
        if(!$st->execute()){
            echo "E";
            exit;
        }
        $res = $st->get_result();
        $k=0;
        while($row = $res->fetch_assoc())
        {
            $k++;
            $time = $row["v1"];
            $hour = (int)($time/60);
            $minute = $time%60;
            if($minute<10)$minute="0$minute";
            $time = "$hour:$minute";
            $traffic = $row["v2"];
            switch($traffic){
                case 1:
                    $traffic = "t_in";
                    break;
                case 2:
                    $traffic = "t_out";
            }
            echo "
                <td class='$traffic'>
                    $time
                </td>
            ";
        }
        for($space = $k;$space<8;$space++){
            echo "
                <td></td>
            ";
        }
        echo "</tr>";
    }
?>        
                </tbody>
            </table>
    <div class="cut w100p"></div>
<?php
$th="";
if($_SESSION["a_logged"]->get_level() >= 2){
    $th = '
    <th>
        مشاهده جزئیات
    </th>
    <th>
        حذف
    </th>  
    ';
}
    echo '
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',4".')">
                        تاریخ 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',2".')">
                        ساعت 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',3".')">
                        تردد
                    </th>
                    '.$th.'                  
                </tr>
            </thead>
            <tbody>';
        
    $order_str = $_SESSION[$cf]->order_by->order_str($tb);
    
    $st = "SELECT id,v1,v2,v3 FROM $tb
    WHERE eid = $eid AND asf = '$state' AND del_flag = 0 $where $order_str ";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $k = 0;
    while($row = $res->fetch_assoc())
    {
        $k++;
        $id = $row["id"];
        $time = $row["v1"];
        $hour = (int)($time/60);
        $minute = $time%60;
        if($minute<10)$minute="0$minute";
        $time = "$hour:$minute";
        $traffic = $row["v2"];
        switch($traffic){
            case 1:
                $traffic = "ورود";
                break;
            case 2:
                $traffic = "خروج";
        }
        $date = correctDate($row["v3"]);
$td="";
if($_SESSION["a_logged"]->get_level() >= 2){
    $td ="
    <td ".'onclick ="sub_show('."'id','".$id."'".')" >
        <span class="curpo icon-i"></span>'."
    </td>
    <td ".'onclick ="sub_show('."'id_del','".$id."'".')" >
        <span class="curpo icon-x"></span>'."
    </td> 
    ";
}        
    
        echo "<tr>
                <td>
                    $k
                </td>
                <td>
                    $date
                </td>
                <td>
                    $time
                </td>
                <td>
                    $traffic
                </td>
                $td                    
            </tr>";
        }
        echo '</tbody>
            </table>';
?>
<?php
    }
?>
<?php
        }
    }
?>
