<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

$state = getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state);
if($state == 1){
    if(!isset($_SESSION[$cf]->sql_where["start_date"]) && !isset($_SESSION[$cf]->sql_where["end_date"])){
        $_SESSION[$cf]->sql_where["start_date"] = CaSDate(time()-7*24*3600);
        $_SESSION[$cf]->sql_where["end_date"] = CaSDate(time()+1*24*3600);
    }
}
if($state == 5){
    if(!isset($_SESSION[$cf]->sql_where["start_date"]) && !isset($_SESSION[$cf]->sql_where["end_date"])){
        $_SESSION[$cf]->sql_where["start_date"] = CaSDate(time());
        $_SESSION[$cf]->sql_where["start_date"]["day"] = 1;
        $_SESSION[$cf]->sql_where["end_date"] = $_SESSION[$cf]->sql_where["start_date"];
        if($_SESSION[$cf]->sql_where["end_date"]["month"] == 12){
            $_SESSION[$cf]->sql_where["end_date"]["year"]++;
            $_SESSION[$cf]->sql_where["end_date"]["month"]=1;
        }else{
            $_SESSION[$cf]->sql_where["end_date"]["month"]++;
        }
    }
}    
    if(!isset($_SESSION[$cf]->sql_where["date_base"]))$_SESSION[$cf]->sql_where["date_base"] = "create_date";
    $column_name = "orders.".$_SESSION[$cf]->sql_where["date_base"];
    $where_and = 1;
    include_once $GLOBALS['bu']."modules/wdb/period_select.php";
?>
    </div>  
</div>          
<div class="cut w100p"></div>
<div id="sect2" class="sect">
    <div class="middle container">
        <form class="date" action="<?php echo $URL; ?>" method="post">
            <label>فیلتر تهران / شهرستان:</label>
                <select name="county_filter">
                    <?php
                        $items = array("all"=>"همه","tehran"=>"تهران","others"=>"شهرستان‌ها");
                        foreach($items as $key=>$value){
                            $selected = "";
                            if($_SESSION[$cf]->sql_where["county_filter"] == $key)$selected=" selected ";
                            echo '<option '.$selected.' value="'.$key.'">'.$value.'</option>';
                        }
                    ?>
                </select>
                <label>مبنای تاریخ:</label>
                <select name="date_base">
                    <?php
                        $items = array("create_date"=>"تاریخ ثبت","p_send_date"=>"تاریخ ارسال ");
                        foreach($items as $key=>$value){
                            $selected = "";
                            if($_SESSION[$cf]->sql_where["date_base"] == $key)$selected=" selected ";
                            echo '<option '.$selected.' value="'.$key.'">'.$value.'</option>';
                        }
                    ?>
                </select>
                <label>تعداد ردیف در صفحه:</label>
                <select name="page_limit">
                    <?php
                        $items = array("10"=>"10","30"=>"30","50"=>"50","all"=>"همه");
                        foreach($items as $key=>$value){
                            $selected = "";
                            if($_SESSION[$cf]->page_limit == $key)$selected=" selected ";
                            echo '<option '.$selected.' value="'.$key.'">'.$value.'</option>';
                        }
                    ?>
                </select>
            <input type="submit" name="cf_submit" value="تأیید">
        </form>
    </div>  
</div>          
<?php
    $cf_where = "";
    if(isset($_SESSION[$cf]->sql_where["county_filter"])){
        switch($_SESSION[$cf]->sql_where["county_filter"]){
            case "tehran":
                $cf_where = " AND addresses.county = 'شهر تهران' ";
                break;
            case "others":
                $cf_where = " AND NOT addresses.county = 'شهر تهران' ";
                break;
        }
    }
?>
<div class="cut w100p"></div> 
<?php
if($state == 1){
?>
<div id="sect2" class="sect">
    <div class="middle container">
        <a class="btn submit" onclick="sub_show('clear','5')">ثبت شماره مرجع / پیگیری پرداخت</a>
    </div>  
</div>          
<div class="cut w100p"></div>
<?php
    $btn = "";
    if(isset($_SESSION[$cf]->sql_where['admin_state'])){
        $btn = "عدم ";
    }
?>
<div id="sect2" class="sect">
    <div class="middle container">
        <a class="btn submit" onclick="sub_show('show_all','1')"><?php echo $btn; ?>نمایش همه سفارش‌های جدید</a>
    </div>  
</div>          
<div class="cut w100p"></div>
<?php
}
?>    
<?php
if($state == 3){
?>
<div id="sect2" class="sect">
    <div class="middle container">
        <a class="btn submit" target="_blank" href="send_detail_all.php" onclick="multi_select('ms_sdp','orders/send_detail_all.php','blank');return false;">چاپ تمامی مشخصات ارسال</a>
    </div>  
</div>          
<div class="cut w100p"></div>
<?php
}
?>
<?php
if($state == 4){
?>
<div id="sect2" class="sect">
    <div class="middle container">
        <a class="btn submit" onclick="sub_show('clear','4')">لیست ارسال تهران</a>
    </div>  
</div>          
<div class="cut w100p"></div>
<div id="sect2" class="sect">
    <div class="middle container">
        <a class="btn submit" onclick="sub_show('clear','3')">ثبت شماره مرسوله پستی از پرونده</a>
    </div>  
</div>          
<div class="cut w100p"></div>
<?php
}
?>
<?php
if($state < 4){
?>
<div id="sect2" class="sect">
    <div class="middle container">
            <a class="btn submit" onclick="select_all_items(this)">انتخاب همه سفارش‌ها</a>
    </div>  
</div>          
<div class="cut w100p"></div>        
<?php
}
?>


<ul class="page_buttons cb">
<?php             
    $order_str = $_SESSION[$cf]->order_by->order_str($tb);
    $sql_state = $state-1;
    $state_where = "";
    if($state == 4){
        $state_where = " AND $tb.create_date > '2019-10-10' ";
    }
    if($state == 1){
        // $state_where = " AND $tb.create_date > NOW() - INTERVAL 10 DAY ";
        if(!isset($_SESSION[$cf]->sql_where['admin_state'])){
            $state_where .= " AND $tb.admin_state = 1 ";
        }
    }
    
    if($_SESSION[$cf]->page_limit == "all"){
        $page_limit = "";
        $offset_t = "";
        $offset = 0;
    }else{
    
    // total rows calc
    $st = "SELECT COUNT($tb.id) as nor FROM $tb 
    LEFT JOIN users ON $tb.uid = users.id
    LEFT JOIN addresses ON $tb.aid = addresses.id
    WHERE $tb.del_flag = 0 AND $tb.state = $sql_state $state_where $where $cf_where";
    
    
    $res = return_sel_sql($st);
    $nor = $res->fetch_assoc()['nor'];
    $page_limit = $_SESSION[$cf]->page_limit;
    $max = ceil($nor/$page_limit)-1;
    $_SESSION[$cf]->offset = min($_SESSION[$cf]->offset,$max);
    $_SESSION[$cf]->offset = max($_SESSION[$cf]->offset,0);
    $offset = $page_limit*$_SESSION[$cf]->offset;
    
    $page_limit = " LIMIT $page_limit ";
    $offset_t = " OFFSET $offset ";
    
    
    $cp = $_SESSION[$cf]->offset;
    if($cp>0){
            echo '
                    <li onclick ="sub_show('."'cp','0'".')">
                        1
                    </li>';
    }
    if($cp>1){
            echo '
                    <li class="dot">...</li>
                    <li onclick ="sub_show('."'cp','".($cp-1)."'".')">
                        «
                    </li>';
    }
    echo '
                    <li class="selected">
                        '.($cp+1).'
                    </li>';
    if($cp < $max - 1){
            echo '
                    <li onclick ="sub_show('."'cp','".($cp+1)."'".')">
                        »
                    </li>
                    <li class="dot">...</li>
                    ';
    }
    if($cp < $max){
            echo '
                    <li onclick ="sub_show('."'cp','".($max+1)."'".')">
                        '.($max+1).'
                    </li>
                    ';
    }
?>

</ul>
<style type="text/css">
ul.page_buttons{
    display:table;
    
}
ul.page_buttons li{
    display: block;
    float: right;
    width: 40px;
    height: 40px;
    border: 2px solid maroon;
    text-align: center;
    padding: 6px 0;
    margin: 5px 10px;
    border-radius: 5px;
    cursor:pointer;
}
ul.page_buttons li.dot{
    border:0;
}
ul.page_buttons li:hover {
    background-color: rgba(128, 0, 0, 0.2);
}
ul.page_buttons li.selected{
    background-color:rgb(128,0,0);
    color:white;
}
</style>

<?php } ?>


<div id="sect3" class="sect">
    <div class="middle container">
        <div class="user_hint orders_row_legend">
            <span class="legend_item"><span class="legend_swatch hd"></span> سفارش دارای توضیحات</span>
            <span class="legend_item"><span class="legend_swatch oq"></span> ارسال خارج از نوبت</span>
            <span class="legend_item"><span class="legend_swatch oq_f"></span> ارسال نامشخص/نیازمند بررسی</span>
            <span class="legend_item"><span class="legend_swatch row_as"></span> پرداخت ثبت‌شده توسط ادمین</span>
            <span class="legend_item"><span class="legend_swatch sp_changed"></span> سفارش اسنپ‌پی تغییر/لغو شده</span>
            <span class="legend_item"><span class="legend_swatch today"></span> تاریخ ارسال امروز</span>
            <span class="legend_item"><span class="legend_swatch tomorrow"></span> تاریخ ارسال فردا</span>
        </div>
<?php
if($state == 4){
?>
<form action="<?php echo $URL; ?>" method="post">
<?php
}
?>
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th class="onc"  onclick="sub_show('order','1')">
                        شناسه سفارش
                    </th>
                    <th class="onc"  onclick="sub_show('order','2')">
                        نام مشتری
                    </th>
                    <th class="onc"  onclick="sub_show('order','3')">
                        استان مشتری
                    </th>
                    <th class="onc"  onclick="sub_show('order','4')">
                        نام گیرنده
                    </th>
                    <th class="onc"  onclick="sub_show('order','5')">
                        تاریخ ثبت
                    </th>
<?php
    if($state == 4){
?>
                    <th>
                        روش پرداخت
                    </th>
                    <th class="onc"  onclick="sub_show('order','9')">
                        ارسال
                    </th>
                    <th class="onc"  onclick="sub_show('order','10')">
                        تحویل
                    </th>
                    <th>
                        شماره مرسوله پستی 
                    </th>
                    <th>
                        پیک ویژه (خارج از نوبت)
                    </th>
                    <th>
                        ارسال متفرقه 
                    </th>
                    <th>
                        <?php echo getVarFromDB("transporters","name","id",getVarFromDB("sd_setting","value","flag","tp_id")); ?>
                    </th>
                    
                    
                    <th>
                        مشاهده جزئیات
                    </th>
<?php
    }else{
?>
                    <th class="onc"  onclick="sub_show('order','6')">
                        تاریخ پرداخت
                    </th>
                    <th class="onc"  onclick="sub_show('order','7')">
                        جمع سبد خرید (با اعمال تخفیف)
                    </th>
                    <th class="onc"  onclick="sub_show('order','8')">
                        مبلغ قابل پرداخت
                    </th>
                    <th>
                        روش پرداخت
                    </th>

                    <th class="onc"  onclick="sub_show('order','9')">
                        ارسال 
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>
<?php
    }
?>
<?php
if($state < 4){
?>
                    <th>
                        انتخاب
                    </th>
<?php
    }
?>
<?php
if($state == 5){
?>
                    <th>
                        ارسال‌کننده
                    </th>
<?php
}
?>
<?php
if($state == 1){
?>
                    <th>
                       شناسه پرداخت
                    </th>
<?php
}
?>
                 </tr>
            </thead>
            <tbody>
<?php
        {
            
            
            $st = "SELECT $tb.id, $tb.unified_id, users.name, addresses.county, addresses.rec_name, $tb.create_date, $tb.payment_date, $tb.cart_price, $tb.pay_price, $tb.p_send_date, $tb.recieve_date, $tb.recieve_shift, $tb.order_detail, $tb.post_ref_id, $tb.pay_request_auth, $tb.admin_state FROM $tb 
            LEFT JOIN users ON $tb.uid = users.id
            LEFT JOIN addresses ON $tb.aid = addresses.id
            WHERE $tb.del_flag = 0 AND $tb.state = $sql_state $state_where $where $cf_where $order_str $page_limit $offset_t";

            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "E";
                exit;
            }
            $res = $st->get_result();

            $rows = [];
            $oids = [];
            while($row = $res->fetch_assoc()){
                $rows[] = $row;
                $oids[] = (int)$row["id"];
            }

            $snappay_oids = [];
            $snappay_changed_oids = [];
            if (file_exists($bu."modules/snappay/snappay_db.php")) {
                require_once $bu."modules/snappay/snappay_db.php";
                if (function_exists('snappay_tx_oids_set')) {
                    $snappay_oids = snappay_tx_oids_set($oids);
                }
                if (function_exists('snappay_event_oids_set')) {
                    $snappay_changed_oids = snappay_event_oids_set($oids);
                }
            }

            $k = $offset;
            $today = correctDate_ts(time());
            $tomorrow = correctDate_ts(time()+24*3600);
            // echo $today." - $tomorrow<br><br>";
            foreach($rows as $row)
            {
                
                
                $k++;
                $id = $row["id"];
                $raw_unified_id = $row["unified_id"] ?? null;
                $unified_id = $raw_unified_id ? $raw_unified_id : $id;
                $name = $row["name"];
                $county = $row["county"];
                $rec_name = $row["rec_name"];
                $submit_date = correctDate($row["create_date"]);
                $payment_date = correctDate($row["payment_date"]);
                $cart_pure = price_sep($row["cart_price"]);
                $pay_price = price_sep($row["pay_price"]);
                $send_date = correctDate($row["p_send_date"]);
                $rec_date = correctDate($row["recieve_date"]);
                $rec_shift = $row["recieve_shift"];
                $tp_id = getVarFromDB("sd_shifts","transporter_id","id",$rec_shift);
                $is_manual_pay = ((int)$row["admin_state"] === 1);

                $pay_method = "درگاه پرداخت";
                if ($raw_unified_id && !ctype_digit((string)$raw_unified_id)) {
                    $pay_method = "Pinket";
                } else if (isset($snappay_oids[(int)$id])) {
                    $pay_method = "اسنپ‌پی";
                } else if ($is_manual_pay) {
                    $pay_method = "توسط ادمین";
                }
                
                $td_sd = "";
                if($send_date == $tomorrow)$td_sd = " tomorrow ";
                if($send_date == $today)$td_sd = " today ";
                
                $special ="";
                
                
                $st = "SELECT count(pid) AS cp FROM sub_orders WHERE oid = $id AND del_flag = 0 AND pid > 44 AND pid < 47";
                $st = $mysqli->prepare($st);
                if(!$st->execute()){
                    echo "E";
                    exit;
                }
                $st->store_result();
                $st->bind_result($value);
                $st->fetch();
                if($st->num_rows == 1){
                    if($value > 0){
                        $special = " special ";
                    }
                }
                
                $recieve="";
                $oq = "";$hd="";$as="";
                $sp_changed = "";
                if($row["admin_state"]){
                    $as = "td_as";
                    if($state == 1){
                        $as = "row_as";
                    }
                }
                if($sql_state == 2 && isset($snappay_changed_oids[(int)$id])){
                    $sp_changed = " sp_changed ";
                }
                
                if($tp_id == 1){
                    $send = cor_sendShift($rec_shift);
                    if($send_date){
                        if($state == 4){
                            $recieve = $send;
                            $send = $send_date;
                        }else{
                            $send = cor_sendShift($rec_shift)." - ".$send_date;
                        }
                    }
                }else if($tp_id == 2){
                    if($send_date){
                        $send = $send_date;
                        $oq = " oq ";
                    }else{
                        $send = "نامشخص!";
                        $oq = " oq_f ";
                    }
                    $recieve = cor_sendShift($rec_shift);
                }else{
                    $send=$send_date;
                    $recieve=getVarFromDB("sd_shifts","name","id",$rec_shift)." ".$rec_date;
                }
                
                
//                if($tp_id == 2){
////                    $rec_date = "پیک خارج از نوبت";
//                    $oq = " oq ";
//                }
                if($row["order_detail"]){
                    $hd = " hd ";
                }
                
                $post_ref_id = $row["post_ref_id"];
                $pay_req_auth = $row["pay_request_auth"];
                if($pay_req_auth)$pay_req_auth = (int)substr($pay_req_auth,2);
                
                echo "<tr class='$hd $oq $as $sp_changed'>
                        <td class='$as'>
                            $k
                        </td>
                        <td><span class='id_span' style='display:none;'>$id</span>$unified_id</td>
                        <td>$name</td>
                        <td>$county</td>
                        <td>$rec_name</td>
                        <td>$submit_date</td>";
if($state == 4){
    echo "

                        <td>
                            $pay_method
                        </td>
                        <td class='$td_sd'>
                            $send
                        </td>
                        <td>
                            $recieve
                        </td>
                        <td>
                            <input name='post[$id]' type='text'>
                        </td>
                        <td>
                            <input class='hide' name='courier[$id]' type='checkbox'>
                            <span class='curpo chkholder icon-chk' onclick = 'box_select(this);'></span>
                        </td>
                        <td>
                            <input class='hide' name='other[$id]' type='checkbox'>
                            <span class='curpo chkholder icon-chk' onclick = 'box_select(this);'></span>
                        </td>
                        <td>
                            <input class='hide' name='tehran[$id]' type='checkbox'>
                            <span class='curpo chkholder icon-chk' onclick = 'box_select(this);'></span>
                        </td>
                        <td onclick=\"sub_show('id','$unified_id')\">
                            <span class=\"curpo icon-i\"></span>"."
                        </td>";
}else{
                echo "
                        <td>
                            $payment_date
                        </td>
                        <td>
                            $cart_pure
                        </td>
                        <td>
                            $pay_price
                        </td>
                        <td>
                            $pay_method
                        </td>
                        <td class='$td_sd'>
                            $send
                        </td>
                        <td onclick=\"sub_show('id','$unified_id')\">
                            <span class=\"curpo icon-i\"></span>"."
                        </td>";
}
                        
if($state < 4){
    echo
                        "<td ".'>
                            <span class="curpo chkholder icon-chk" onclick = "item_select(this.parentNode.parentNode);check_box_woi(this)"></span>'."
                        </td>";
}
if($state == 5){
    if(strlen($post_ref_id) == 24){
        $post_ref_id = "پست - کد: $post_ref_id";
    }else{
        $post_ref_id = getVarFromDB("transporters","name","id",$post_ref_id);
    }
    echo
                        "<td>
                            $post_ref_id
                        </td>";
}
if($state == 1){
    echo
                        "<td>
                            $pay_req_auth
                        </td>";
}
    echo            "</tr>";
            }
        }
?>
            </tbody>
        </table>
<?php
if($state == 4){
?>
    <div class="cut"></div>
    <input type="submit" name="post_ref" class="btn" value="ثبت">
</form>
<script type="text/javascript">
function box_select(item){
    if(item.classList.contains("icon-chk")){
        item.classList.add("icon-chkfl");
        item.classList.remove("icon-chk");
        item.parentNode.getElementsByTagName('input')[0].checked = true;
    }else if(item.classList.contains("icon-chkfl")){
        item.classList.remove("icon-chkfl");
        item.classList.add("icon-chk");
        item.parentNode.getElementsByTagName('input')[0].checked = false;
    }
}
</script>
<?php
}
?>
       
<?php
if($state < 4){
?>            
    </div>
</div>          
<div class="cut w100p"></div> 
<div id="sect4" class="sect">
    <div class="middle container"> 
        <a class="btn submit" onclick="multi_select('ms_submit')">تایید گروهی به مرحله بعد</a>
<script src="<?php echo $s; ?>js/multi_select.js"></script>
<?php
}
?>

<?php
        }
    }
?>
