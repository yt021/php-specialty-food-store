<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <div class="ssrv">
                <div class="ssrv_title">مشخصات خریدار</div>        
                <div class="ssrv_dtl">
                    <form>
                        <div class="form fr">
<?php
    $oid = $_SESSION[$cf]->id;
    include_once $bu."modules/cart/cart_funcs.php";
    $uid = getVarFromDB($tb,"uid","id",$oid);
    $aid = getVarFromDB($tb,"aid","id",$oid);
    $user = db_get_user($uid);
    $address = db_get_address($aid);
    $addresses = db_get_all_address($uid);
    ?>  
    
    <?php

    echo '              
                            <div class="form_item">
                                <label>نام:</label>
                                <input disabled type="text" value="'.$user->data()["name"].'">
                            </div>
                            <div class="form_item">
                                <label>تلفن همراه:</label>
                                <input disabled type="text" value="'.$user->data()["tel"].'">
                            </div>
                            <div class="form_item">
                                <label>رایانامه:</label>
                                <input disabled type="text" value="'.$user->data()["email"].'">
                            </div>
                    ';
?>
                        <a class="btn" onclick="sub_show('uid','<?php echo $_SESSION["$cf"]->sub_id;?>','clients/')">مشاهده حساب</a> 
                        
                        </div>
                    </form>
                </div>
            </div>
            <div class="ssrv">
                <div class="ssrv_title">آدرس خریدار</div>        
                <div class="ssrv_dtl">
                    <form>
                        <div class="form fr">
<?php
    echo '              
                            <div class="form_item">
                                <label>استان:</label>
                                <input disabled type="text" value="'.$address->data()["county"].'">
                            </div>
                            <div class="form_item">
                                <label>شهر:</label>
                                <input disabled type="text" value="'.$address->data()["city"].'">
                            </div>
                            <div class="form_item">
                                <label>آدرس:</label>
                                <input disabled type="text" value="'.$address->data()["address"].'">
                            </div>
                            <div class="form_item">
                                <label>کد پستی:</label>
                                <input disabled type="text" value="'.$address->data()["post_code"].'">
                            </div>
                    ';
?>
                            <a class="btn" onclick="show_addresses(this)">تغییر آدرس (انتخاب)</a>
                        </div>
<!--/\\\\\\\\\\\\\\\\\\\\\\\\\\\/-->
    <div class="hide addresses_holder">
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th>
                        استان
                    </th>
                    <th>
                        شهر
                    </th>
                    <th>
                        آدرس 
                    </th>
                    <th>
                        کد پستی
                    </th>
                    <th>
                        نام گیرنده
                    </th>
                    <th>
                        تلفن گیرنده
                    </th>
                    <th>
                        تحویل به نگهبان
                    </th>
                    <th>
                        انتخاب
                    </th>
                </tr>
            </thead>
            <tbody>
<?php    
    $addresses = db_get_all_address($uid);
    $i=0;
    foreach($addresses as $address){
        $data_a = $address->data();
        $i++;
?>
                <tr>
                    <td>
                        <?php echo $i;?> 
                    </td>
                    <td>
                        <?php echo $data_a["county"];?>
                    </td>
                    <td>
                        <?php echo $data_a["city"];?>
                    </td>
                    <td>
                        <?php echo $data_a["address"];?>
                    </td>
                    <td>
                        <?php echo $data_a["post_code"];?>
                    </td>
                    <td>
                        <?php echo $data_a["rec_name"];?>
                    </td>
                    <td>
                        <?php echo $data_a["rec_tel"];?>
                    </td>
                    <td>
<?php
    $class = "icon-chk";
    if($data_a["janitor"] == "yes")$class = "icon-chkfl";
?>
                        <span class="checkbox <?php echo $class; ?>"></span>
                    </td>
                    <td >
                            <a onclick ="sub_show('change_aid','<?php echo $data_a["aid"] ?>')" class="btn middle">انتخاب </a>
                    </td>
                </tr>
<?php
    }
?>
            </tbody>
        </table>
        برای تغییر یا افزودن آدرس، وارد حساب کاربری مشتری شوید.
    </div>
<!--/\\\\\\\\\\\\\\\\\\\\\\\\\\\/-->
                        
                    </form>
                </div>
            </div>
<script type="text/javascript">
function show_addresses(item){
    item.parentNode.classList.add('hide');
    item.parentNode.parentNode.getElementsByClassName('addresses_holder')[0].classList.remove('hide');
}
</script>
<style type="text/css">
div.addresses_holder{
    padding:2px 10px;
}
div.addresses_holder table{
    height:150px;
    overflow-y:scroll;
    display:block;
}
div.addresses_holder tbody{
    height:75px;
    overflow-y:scroll;
}
div.addresses_holder td,div.addresses_holder th{
    font-size:12px;
    padding:4px 8px;
}
div.addresses_holder a.btn.middle{
    font-size: 12px;
    padding:2px;
}
</style>
            <div class="cut w100p"></div>
            <div class="ssrv">
                <div class="ssrv_title">توضیحات</div>        
                <div class="ssrv_dtl">
                    <form action="<?php echo $URL; ?>" method="post">
                        <div class="form fr">
<?php
    $order_detail = getVarFromDB("orders","order_detail","id",$oid);
    ?>  
    
    <?php

    echo '              
                        <div class="form_item">
                            <label>توضیحات:</label>
                            <textarea name="order_detail" type="text" >'.$order_detail.'</textarea>
                        </div>
                    ';
?>
                        <input type="submit" class="btn" name="edit" value="ثبت">
                        </div>
                    </form>
                </div>
            </div>
<?php
    if($_SESSION["a_logged"]->get_level() == 2){
?>
            <div class="ssrv">
                <div class="ssrv_title">تخفیف و پرداخت</div>        
                <div class="ssrv_dtl">
                    <form class="" action="<?php echo $URL; ?>" method="post">
                        <div class="form fr" id="invoice_values">
<?php
    $fields[1] = ["pay_price","sale_total","cart_price","cart_pure"];
    $fields[2] = ["cart_sale","send_sale"];
    $labels = array(
        "full_price"=>"مجموع فاکتور",
        "pay_price"=>"مبلغ قابل پرداخت",
        "sale_total"=>"مجموع تخفیف",
        "cart_price"=>"مجموع سبد خرید(ناخالص)",
        "cart_pure"=>"مجموع سبد خرید(خالص)",
        "cart_sale"=>"تخفیف سبد خرید",
        "send_cost"=>"هزینه ارسال",
        "send_sale"=>"تخفیف ارسال"
    );
    foreach($fields[1] as $f){
        $invoice_value[$f] = getVarFromDB($tb,$f,"id",$_SESSION[$cf]->id);
    }
    $invoice_value["send_cost"] = 
    ($invoice_value["pay_price"] - $invoice_value["cart_pure"]) + 
    ($invoice_value["sale_total"] - ($invoice_value["cart_price"] - $invoice_value["cart_pure"]));
    
    $invoice_value["full_price"] = 
    $invoice_value["cart_price"] + 
    $invoice_value["send_cost"];
    
    $invoice_value["cart_sale"] = $invoice_value["cart_price"] - $invoice_value["cart_pure"];
    $invoice_value["send_sale"] = $invoice_value["sale_total"] - $invoice_value["cart_sale"];
?>
<?php
    $fields[1] = array_merge(["full_price"],$fields[1],["send_cost"]);
    foreach($fields[1] as $f){
?>
        <div class="form_item multi">
            <label><?php echo $labels[$f]; ?>: </label>
            <input disabled type="text" value="<?php echo $invoice_value[$f]; ?>">
        </div>
<?php
    }
?>                    
    <div class="cb"></div>
<?php
    foreach($fields[2] as $f){
?>
        <div class="form_item">
            <label><?php echo $labels[$f]; ?>: </label>
            <input name="<?php echo $f; ?>" type="text" value="<?php echo $invoice_value[$f]; ?>">
            <a class="btn small fl" style="" onclick="fill_value('<?php echo $f; ?>',this)"><span class="icon icon-chkfl"></span></a>
        </div>
<?php
    }
?>
                            <input class="hide" name="invoice" value="sale">
                            <input type="submit" class="btn" name="edit" value="ثبت">
                        </div>
                    </form>
                </div>
            </div>
<?php
    }
?>                
            <div class="ssrv">
                <div class="ssrv_title">ارسال</div>        
                <div class="ssrv_dtl">
                    <form class="" action="<?php echo $URL; ?>" method="post">
                        <div class="form fr">
                            <div class="form_item date" id="date_box">
<?php
    $dsbl_date="";$dsbl_prid="";
    if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state) == 5){$dsbl_date = "disabled";}
    else{$dsbl_prid = "disabled";}
    
    if($rec_shift = getVarFromDB($tb,"recieve_shift","id",$oid)){
        if($rec_shift < 3){
            ?>
                                <label for="shift">نوع ارسال:</label> 
                                <select name="shift" <?php echo $dsbl_date; ?>>
<?php
    $tp_id = getVarFromDB("transporters","id","name","پست");
    $st = "SELECT id,name FROM sd_shifts WHERE transporter_id = $tp_id AND del_flag = 0 ORDER BY id ASC";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $shifts = array();
    while($row = $res->fetch_assoc())
    {
        $shifts[$row["id"]] = $row["name"];
    }
    $shifts[3] = cor_sendShift(3);
    foreach($shifts as $shift=>$name){
        $selected = "";
        if($rec_shift == $shift)$selected=" selected ";
        echo '<option '.$selected.' value="'.$shift.'">'.$name.'</option>';
    }
?>
                                </select>
<?php
        }else{
            if($rec_date = getVarFromDB($tb,"recieve_date","id",$oid)){
                $date_name="rec_date";
                $_SESSION[$cf]->sql_where[$date_name]=CaSDate(strtotime($rec_date));
            }
            $date_box="";$date_btns="";
            if($rec_shift == 3){    
                $date_btns = "disabled";
            }else{
                $date_box = "disabled";
            }
?>
                                تاریخ تحویل
                                <label for="day">روز:</label> 
                                <select name="day" <?php echo $dsbl_date." ".$date_box; ?> >
                                    <?php
                                        for($i=1;$i<=31;$i++){
                                            $selected = "";
                                            if($_SESSION[$cf]->sql_where[$date_name]["day"] == $i)$selected=" selected ";
                                            echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                        }
                                    ?>
                                </select>
                                <label for="month">ماه:</label> 
                                <select name="month" <?php echo $dsbl_date." ".$date_box; ?> >
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
                                <select name="year" <?php echo $dsbl_date." ".$date_box; ?> >
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
                                <select name="shift" <?php echo $dsbl_date; ?> onchange="date_field_toggle(this[this.selectedIndex].value)">
<?php
    $active_transporter = getVarFromDB("sd_setting","value","flag","tp_id");
    $st = "SELECT id FROM sd_shifts WHERE transporter_id = $active_transporter AND del_flag = 0 ORDER BY id ASC";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $shifts = array();
    $shifts[2] = cor_sendShift(2);
    $shifts[3] = cor_sendShift(3);
    while($row = $res->fetch_assoc())
    {
        $shifts[$row["id"]] = cor_sendShift($row["id"]);
    }
    foreach($shifts as $shift=>$name){
        $selected = "";
        if($rec_shift == $shift)$selected=" selected ";
        echo '<option '.$selected.' value="'.$shift.'">'.$name.'</option>';
    }
?>
                                </select>
                            </div>
                            <div class="form_item">
                                <input id="shift_input" name="shift_" value="" class="hide" type="text">
                                <input id="id_input" name="id" value="" class="hide" type="text">
                                <input id="alt_input" name="alt" value="" class="hide" type="text">
                                <div id="date_btns" class="send_date_selection <?php echo $date_btns." ".$dsbl_date; ?>">
<?php
    $timestamp = time();
    $sd_start_user = getVarFromDB("sd_setting","value","flag","sd_start");
    $sd_start = 0;
    $sd_length = getVarFromDB("sd_setting","value","flag","sd_length");
    $sd_limit = (int)getVarFromDB("sd_setting","value","flag","sd_limit");
    $start_date = $timestamp + $sd_start*24*3600 - 2.5*3600;
    $end_date = $timestamp + ($sd_start_user+$sd_length+2)*24*3600;
    $start_ts = date("Y-m-d H:i:s",$start_date);
    $end_ts = date("Y-m-d H:i:s",$end_date);
    
    // echo $sd_limit;
    
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
        $days[$date] = [$id,$state,$datets,$order_no,$k];
        $k++;
        
    }
    $show_turns = array();
    
    $pickup_hour = (int)getVarFromDB("sd_setting","value","flag","pickup");
    $active_transporter = getVarFromDB("sd_setting","value","flag","tp_id");
    $st = "SELECT * FROM sd_shifts WHERE transporter_id = $active_transporter AND del_flag = 0";
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
                                    $show_turns[$date_p1][$shift] = [$show_turns[$date][$shift][0],"send",$date_p1_ts,$show_turns[$date][$shift][3]+1,$show_turns[$date][$shift][4],$show_turns[$date][$shift][5]];
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
                        $show_turns[$date_p1][$shift] = [$value[0],"send",$date_p1_ts,0,$value[3],$value[4]];
                    }
                }
                $date_p2_ts = $date_p1_ts+24*3600;
                $date_p2 = cd_show_date($date_p2_ts);
                foreach($shifts as $shift=>$v){
                        $show_turns[$date_p2][$shift] = [$value[0],"send",$date_p2_ts,1,$value[3],$value[4]];
                }
                break;
        }
    }
?>

<?php
$k = 0;
    foreach($show_turns as $date=>$turn){
        if($k > $sd_length)break;
        else{$k++;}
        
        $thisdate = 0;
        foreach($shifts as $shift=>$v){
            if(isset($turn[$shift]) && $turn[$shift][1] == "send"){
                $thisdate = 1;   
            }
        }
        if($thisdate == 1){
            foreach($shifts as $shift=>$v){
                $v_name = $v[0];
                $v_time = $v[1]." - ".$v[2];
                $shift_class = " shift_$shift ";
                if($rec_shift != $shift)$shift_class.=" hide ";
                if(isset($turn[$shift]) && $turn[$shift][1] == "send" && $turn[$shift][4]<$sd_limit){
                    $sdid = $turn[$shift][0];
                    $n2s = $turn[$shift][3];
                    $admin_color = "";
                    if($turn[$shift][5]<$sd_start_user)$admin_color = " admin ";
                    echo "
                            <a class='btn mid half fr $shift_class $admin_color' onclick='select_date(this)' id='$sdid' alt='$n2s' name='$shift' >$date</a>
                        ";
                }
            }
        }
    }
?>
<br>
           <div class="cb"></div> 
</div>
<script type="text/javascript">
    function select_date(item){
        if(item.parentNode.getElementsByClassName('selected')[0])
        item.parentNode.getElementsByClassName('selected')[0].classList.remove('selected');
        item.classList.add('selected');
//        date = item.innerHTML;
//        date = date.substring(0,date.indexOf("-"));
        document.getElementById('id_input').value = item.id;
        document.getElementById('shift_input').value = item.getAttribute('name');
        document.getElementById('alt_input').value = item.getAttribute('alt');
//        document.getElementById('date_input').value = date;
        return;
    }
</script>
<?php
   }
?>                               
<?php
        }
?>
                            </div>
                            <div class="form_item date">
<?php
    $post_ref_id = getVarFromDB($tb,"post_ref_id","id",$oid);
    if(strlen($post_ref_id) == 24){
        $tp_id = 1;
        $input_class = " ";
    }else{
        $tp_id = $post_ref_id;
        $post_ref_id = "";
        $input_class = " hide ";
    }
?>
                                <label>ارسال‌کننده:</label>
                                <select name="tp_id" <?php echo $dsbl_prid; ?> onchange="pr_id_toggle(this[this.selectedIndex].value)">
<?php
    $st = "SELECT id,name FROM transporters
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
        if($tp_id == $id)$selected=" selected ";
?>
                                    <option <?php echo $selected; ?> value="<?php echo $id; ?>"><?php echo $name; ?></option>
<?php
    }
?>                                    
                                </select>
                                <label class="post_ref_id <?php echo $input_class; ?>" for="post_ref_id" >کد مرسوله:</label>
                                <input class="post_ref_id <?php echo $input_class; ?>" <?php echo $dsbl_prid; ?>  name="post_ref_id" type="text" value="<?php echo $post_ref_id ?>">
<script type="text/javascript">
    function pr_id_toggle(tp_id){
        items = document.getElementsByClassName("post_ref_id");
        if(tp_id == 1){
            for(i=0;i<items.length;i++){
                items[i].classList.remove("hide");
            }
        }else{
            for(i=0;i<items.length;i++){
                items[i].classList.add("hide");
            }
        }
    }
</script>
                            </div> 
                            <input class="btn" type="submit" name="edit" value="ثبت"> 
                        </div>
                    </form>
                </div>
            </div>

<div class="cut w100p"></div>
            <h2 class="tac">سبد خرید</h2><br><br>
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th>
                        شناسه محصول
                    </th>
                    <th>
                        نام محصول
                    </th>
                    <th>
                        وزن 
                    </th>
                    <th>
                        تعداد
                    </th>
                    <th>
                        مبلغ واحد
                    </th>
                    <th>
                        مجموع
                    </th>
                </tr>
            </thead>
            <tbody>
<?php
    $cart = new cart_read($oid);
    $i = 0;
    foreach($cart->orders as $item){
        $i++;
        $name = getVarFromDB("products","name","id",$item->pid);
?>
                <tr>
                    <td>
                        <?php echo ($i);?> 
                    </td>
                    <td>
                        <?php echo $item->pid;?>
                    </td>
                    <td>
                        <?php echo $name;?>
                    </td>
                    <td>
                        <?php echo $item->weight;?>
                    </td>
                    <td>
                        <?php echo $item->no;?>
                    </td>
                    <td>
                        <?php echo price_sep($item->price);?>
                    </td>
                    <td>
                        <?php echo price_sep($item->price*$item->no); ?>
                    </td>
                </tr>
<?php
    }
?> 
                <tr>
                    <td colspan="3">
                        
                    مجموع
                        
                    </td>
                    <td>
                        <?php echo $cart->weight();?>
                    </td>
                    <td>
                        <?php echo $cart->number()?>
                    </td>
                    <td>
                        
                    </td>
                    <td>
                        <?php echo price_sep($cart->price());?>
                    </td>
                </tr>
            </tbody>
        </table>
<?php
if(getVarFromDB($tb,"state","id",$_SESSION[$cf]->id) >= 2){
?>
<br>
        <a class="btn submit" href="send_detail.php" target="blank">چاپ مشخصات ارسال</a>
<?php
 }      
?>
    </div>  
</div>          
<div class="cut w100p"></div>
<div id="sect2" class="sect">
    <div class="middle container">
        <a class="btn submit" href="invoice.php" target="_blank">مشاهده فاکتور سفارش</a>
<?php
if($submit_text = getVarFromDB($atb,"submit_text","flag",$_SESSION[$cf]->state)){
?>
    </div>
</div>          
<div class="cut w100p"></div>
<div id="sect2" class="sect">
    <div class="middle container">
        <a class="btn submit" onclick="sub_show('edit','submit')"><?php echo $submit_text; ?></a>
<?php
}      
?>
</div>  
</div>          
<div class="cut w100p"></div>
    <div id="sect2" class="sect">
        <div class="middle container">
<?php
if(getVarFromDB($tb,"state","id",$_SESSION[$cf]->id) > 0){
?>
            <a class="btn submit red" onclick="if(confirm('آیا مطمئنید?'))sub_show('edit','return');">بازگشت سفارش به مرحله قبل</a>
<?php
}
?>
            <a class="btn submit red" onclick="if(confirm('آیا از حذف سفارش مطمئنید?'))sub_show('edit','delete');">حذف سفارش</a>
            
<script type="text/javascript">
    function date_field_toggle(shift){
        if(shift == "3"){
            document.getElementById("date_btns").classList.add("disabled");
            date_box_toggle('');
        }else{
            document.getElementById("date_btns").classList.remove("disabled");
            date_box_toggle('true');
            all_btns = document.getElementById("date_btns").getElementsByClassName("btn");
            for(i=0;i<all_btns.length;i++){
                all_btns[i].classList.add("hide");
            }
            
            btns = document.getElementById("date_btns").getElementsByClassName("shift_"+shift);
            for(i=0;i<btns.length;i++){
                btns[i].classList.remove("hide");
            }
        }
        return;
    }
    function date_box_toggle(dsbl_value){
        selects = document.getElementById("date_box").getElementsByTagName("select");
        for(i=0;i<3;i++){
            selects[i].disabled = dsbl_value;
        }
    }
    
    function fill_value(name,item){
        switch(name){
            case "cart_sale":
                price = 3;
                break;
            case "send_sale":
                price = 5;
                break;
        }
        price = document.getElementById("invoice_values").getElementsByTagName("input")[price].value;
        item.parentNode.getElementsByTagName("input")[0].value = price;
        return;
    }
</script>
<?php
        }
    }
?>
