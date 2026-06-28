<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $column_name = "orders.create_date";
    $where_and = 1;
    include_once $GLOBALS['bu']."modules/wdb/period_select.php";
?>
<?php
if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state) == 3){
?>
        <a class="btn submit" target="_blank" href="send_detail_all.php" onclick="multi_select('ms_sdp','orders/send_detail_all.php');return false;">چاپ تمامی مشخصات ارسال</a>
    </div>  
</div>          
<div class="cut w100p"></div>
<div id="sect2" class="sect">
    <div class="middle container">
        <a class="btn submit" onclick="sub_show('clear','4')">لیست ارسال تهران</a>
    </div>  
</div>          
<div class="cut w100p"></div>
<div id="sect3" class="sect">
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
}else{
?>
            <a class="btn submit" onclick="sub_show('clear','3')">ثبت شماره مرسوله پستی</a>
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
                    <th class="onc" '.' onclick="sub_show('."'order','8'".')">
                        تاریخ ارسال (شهر تهران)
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','9'".')">
                        تاریخ تحویل (شهر تهران)
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>';
                    
if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state) < 4){
    echo
                    '<th>
                        انتخاب
                    </th>';
}else{
    echo
                    '<th>
                        کد رهگیری پست
                    </th>';
}
if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state) == 1){
    echo
                    '<th>
                       شناسه پرداخت
                    </th>';
}
            echo        '
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            $state = getVarFromDB($atb,"id","flag",$_SESSION["orders"]->state)-1;
            $st = "SELECT $tb.id,users.name,addresses.county,$tb.create_date,$tb.payment_date,$tb.cart_price,$tb.pay_price,$tb.p_send_date,$tb.recieve_date,$tb.recieve_shift,$tb.order_detail,$tb.post_ref_id,$tb.pay_request_auth FROM $tb 
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
                $send_date = correctDate($row["p_send_date"]);
                $rec_date = correctDate($row["recieve_date"]);
                $oq = "";$hd="";
                if($row["recieve_shift"] == "out_of_queue"){
                    $rec_date = "پیک خارج از نوبت";
                    $oq = " oq ";
                }
                if($row["order_detail"]){
                    $hd = " hd ";
                }
                $post_ref_id = $row["post_ref_id"];
                $pay_req_auth = $row["pay_request_auth"];
                
                echo "<tr class='$oq $hd'>
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
                        <td>
                            $send_date
                        </td>
                        <td>
                            $rec_date
                        </td>
                        <td ".'onclick ="sub_show('."'oid','".$id."'".')" >
                            <span class="curpo icon-i"></span>'."
                        </td>";
                        
if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state) < 4){
    echo
                        "<td ".'>
                            <span class="curpo chkholder icon-chk" onclick = "item_select(this.parentNode.parentNode);check_box_woi(this)"></span>'."
                        </td>";
}else{
    echo
                        "<td>
                            $post_ref_id
                        </td>";
}
if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state) == 1){
    echo
                        "<td>
                            $pay_req_auth
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
        <a class="btn submit" onclick="multi_select('ms_submit')">تایید گروهی به مرحله بعد</a>
<script src="<?php echo $s; ?>js/multi_select.js"></script>
<?php
}
?>
<?php
        }
    }
?>
