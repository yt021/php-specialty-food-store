<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $column_name = "orders.p_send_date";
    $where_and = 1;
    include_once $GLOBALS['bu']."modules/wdb/day_select.php";
?>

<div id="sect2" class="sect">
    <div class="middle container">
            <a class="btn submit" onclick="select_all_items(this)">انتخاب همه سفارش‌ها</a>
    </div>  
</div>          
<div class="cut w100p"></div>   
<div id="sect2" class="sect">
    <div class="middle container">
        <a class="btn submit half" onclick="multi_select('ms_submit')">ثبت در پست پیشرو</a>
    <a class="btn submit half" onclick="multi_select('ms_del')">حذف از پست پیشرو</a>
        
    </div>  
</div>          
<div class="cut w100p"></div>

                    <form class="date" action="<?php echo $s."admin/orders/send_list.php"; ?>" method="post">
                        <div class="form">
                            <div class="form_item date" id="date_box">
                                فایل اکسل &nbsp;&nbsp;&nbsp;&nbsp;
                                تاریخ تحویل
                                <label for="day">روز:</label> 
                                <select name="day" >
                                    <?php
                                        for($i=1;$i<=31;$i++){
                                            $selected = "";
                                            if($_SESSION[$cf]->sql_where[$date_name]["day"] == $i)$selected=" selected ";
                                            echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                        }
                                    ?>
                                </select>
                                <label for="month">ماه:</label> 
                                <select name="month" >
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
                                <select name="year" >
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
                                <label for="shift">نوبت:</label> 
                                <select name="shift">
<?php
    $active_transporter = getVarFromDB("sd_setting","value","flag","tp_id");
    $st = "SELECT id FROM sd_shifts WHERE transporter_id = $active_transporter AND del_flag = 0 ORDER BY start_hour ASC, id ASC";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $shifts = array();
    while($row = $res->fetch_assoc())
    {
        $shifts[$row["id"]] = cor_sendShift($row["id"]);
    }
    foreach($shifts as $shift=>$name){
        $selected = "";
        // if($rec_shift == $shift)$selected=" selected ";
        echo '<option '.$selected.' value="'.$shift.'">'.$name.'</option>';
    }
?>
                                </select>
                                <input type="submit" name="rec_date" value="دریافت">
                            </div>
                        </div>
                    </form>
<div class="cut w100p"></div>
<?php
    echo '
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','1'".')">
                        شناسه سفارش
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','2'".')">
                        نام مشتری
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','3'".')">
                        آدرس
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','4'".')">
                        تاریخ ثبت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','5'".')">
                        مبلغ قابل پرداخت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','6'".')">
                        تاریخ ارسال
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','7'".')">
                        تاریخ تحویل
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','8'".')">
                        نوبت تحویل
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','9'".')">
                        بارنامه
                    </th>
                    <th>
                        انتخاب
                    </th>
                    ';
            echo        '
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            $tb = "orders";
            $st = "SELECT $tb.id,users.name,addresses.address,$tb.create_date,$tb.pay_price,$tb.p_send_date,$tb.recieve_date,$tb.recieve_shift,$tb.ssmss FROM $tb 
            LEFT JOIN users ON $tb.uid = users.id
            LEFT JOIN addresses ON $tb.aid = addresses.id
            LEFT JOIN sd_shifts ON $tb.recieve_shift = sd_shifts.id
            WHERE $tb.del_flag = 0 AND $tb.state = 3 AND $tb.recieve_shift IS NOT NULL AND sd_shifts.transporter_id > 2 $where $order_str";
            
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
                $name = $row["name"];
                $address = $row["address"];
                $create_date = correctDate($row["create_date"]);
                $pay_price = price_sep($row["pay_price"]);
                $send_date = correctDate($row["p_send_date"]);
                $rec_date = correctDate($row["recieve_date"]);
                $rec_shift = $row["recieve_shift"];
                $rec_shift = cor_sendShift($rec_shift);
                $ssmss = $row['ssmss'];
                
                $scls = "";
                if($ssmss)$scls = "stated";
                
                echo "<tr class=\"$scls\">
                        <td>
                            $k
                        </td>
                        <td class=\"id_span\">$id</td>
                        <td>
                            $name
                        </td>
                        <td>
                            $address
                        </td>
                        <td>
                            $create_date
                        </td>
                        <td>
                            $pay_price
                        </td>
                        <td>
                            $send_date
                        </td>
                        <td>
                            $rec_date
                        </td>
                        <td>
                            $rec_shift
                        </td>
                        <td>
                            $ssmss
                        </td>
                        <td>
                            <span class=\"curpo chkholder icon-chk\" onclick = \"item_select(this.parentNode.parentNode);check_box_woi(this)\"></span>
                        </td>
                    </tr>";
            }
        }
        echo '</tbody>
            </table>';
?>

<script src="<?php echo $s; ?>js/multi_select.js"></script>
<style>
tr.stated{
    background:rgba(0,0,140,0.3);
}
    
</style>
<?php
        }
    }
?>
