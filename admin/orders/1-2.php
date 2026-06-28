<?php
    if(isset($indexed)){
        if($indexed == 1){?>
    <form class="date" action="<?php echo $URL; ?>" method="post">
    انتخاب شروع بازه زمانی:
        <label for="day">روز:</label> 
        <select name="day">
            <?php
                for($i=1;$i<=31;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where["start_date"]["day"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <label for="month">ماه:</label> 
        <select name="month">
            <?php
                for($i=1;$i<=12;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where["start_date"]["month"] == $i)$selected=" selected ";
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
                    if($_SESSION[$cf]->sql_where["start_date"]["year"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <input type="submit" name="start_date" value="تایید">
    </form>
    <form class="date" action="<?php echo $URL; ?>" method="post">
    انتخاب پایان بازه زمانی:
                <label for="day">روز:</label> 
        <select name="day">
            <?php
                for($i=1;$i<=31;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where["end_date"]["day"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <label for="month">ماه:</label> 
        <select name="month">
            <?php
                for($i=1;$i<=12;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where["end_date"]["month"] == $i)$selected=" selected ";
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
                    if($_SESSION[$cf]->sql_where["end_date"]["year"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <input type="submit" name="end_date" value="تایید">
    </form>
    <div class="cut w100p"></div>
<?php
    if(isset($_SESSION[$cf]->sql_where["start_date"])){
        $sql_timestamp_str = createSQLTime($_SESSION[$cf]->sql_where["start_date"]["year"],$_SESSION[$cf]->sql_where["start_date"]["month"],$_SESSION[$cf]->sql_where["start_date"]["day"]);
        
        $where = " $tb.create_date > '$sql_timestamp_str'";
    }
    if(isset($_SESSION[$cf]->sql_where["end_date"])){
        $sql_timestamp_str = createSQLTime($_SESSION[$cf]->sql_where["end_date"]["year"],$_SESSION[$cf]->sql_where["end_date"]["month"],$_SESSION[$cf]->sql_where["end_date"]["day"]);
        
        if(isset($where))$where .= " AND ";else $where ="";
        $where .= " $tb.create_date < '$sql_timestamp_str'";
    }
    if(isset($where))$where = " AND $where";else $where = "";
?>
<?php
if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state) == 3){
?>
        <a class="btn submit" target="_blank" href="send_detail_all.php">چاپ تمامی مشخصات ارسال</a>
    </div>  
</div>          
<div class="cut w100p"></div>
<div id="sect2" class="sect">
    <div class="middle container"> 
<?php
}
?>
<?php
if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state) < 4){
?>
            <a class="btn submit" onclick="select_all_items(this)">انتخاب همه سفارش‌ها</a>
    </div>  
</div>          
<div class="cut w100p"></div>
<div id="sect3" class="sect">
    <div class="middle container">         
<?php
}
?>
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
                        استان مشتری
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','4'".')">
                        تاریخ ثبت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','5'".')">
                        تاریخ پرداخت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','6'".')">
                        جمع سبد خرید (با اعمال تخفیف)
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','7'".')">
                        مبلغ قابل پرداخت
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>';
if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state) == 1){
    echo
                    '<th>
                        شناسه پرداخت
                    </th>';
}                    
if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state) < 4){
    echo
                    '<th>
                        انتخاب
                    </th>';
}
            echo        '
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            $state = getVarFromDB($atb,"id","flag",$_SESSION["orders"]->state)-1;
            $st = "SELECT $tb.id,users.name,addresses.county,$tb.create_date,$tb.payment_date,$tb.cart_price,$tb.pay_price,$tb.pay_request_auth FROM $tb 
            LEFT JOIN users ON $tb.uid = users.id
            LEFT JOIN addresses ON $tb.aid = addresses.id
            WHERE $tb.del_flag = 0 AND $tb.state = $state $where $order_str";
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
                $county = $row["county"];
                $submit_date = correctDate($row["create_date"]);
                $payment_date = correctDate($row["payment_date"]);
                $cart_pure = price_sep($row["cart_price"]);
                $pay_price = price_sep($row["pay_price"]);
                $pay_req_auth = substr($row["pay_request_auth"],27);
                
                
                echo "<tr>
                        <td>
                            $k
                        </td>
                        <td ".'class="id_span"'.">
                            $id
                        </td>
                        <td>
                            $name
                        </td>
                        <td>
                            $county
                        </td>
                        <td>
                            $submit_date
                        </td>
                        <td>
                            $payment_date
                        </td>
                        <td>
                            $cart_pure
                        </td>
                        <td>
                            $pay_price
                        </td>
                        <td ".'onclick ="sub_show('."'oid','".$id."'".')" >
                            <span class="curpo icon-i"></span>'."
                        </td>";
if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state) == 1){
    echo
                    "
                        <td>
                            $pay_req_auth
                        </td>
                    ";
}                          
if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state) < 4){
    echo
                        "<td ".'>
                            <span class="curpo chkholder icon-chk" onclick = "item_select(this.parentNode.parentNode);check_box_woi(this)"></span>'."
                        </td>";
}
                echo    "</tr>";
            }
        }
        echo '</tbody>
            </table>';
?>
<?php
if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state) < 4){
?>            
    </div>  
</div>          
<div class="cut w100p"></div>
    <div id="sect4" class="sect">
        <div class="middle container"> 
        <a class="btn submit" onclick="multi_select()">تایید گروهی به مرحله بعد</a>
<script src="<?php echo $s; ?>js/multi_select.js"></script>
<?php
}
?>
<?php
        }
    }
?>
