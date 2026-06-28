<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $price_lim = 10000; // Toman
?>

<?php
    if(!isset($_SESSION[$cf]->sub_id)){
?>
        <div class="ssrv">
            <div class="ssrv_title">مشخصات محتوا</div>        
            <div class="ssrv_dtl">
                <form action="<?php echo $URL; ?>" method="post" enctype="multipart/form-data">
                    
<?php
    include_once $bu."modules/cart/cart_funcs.php";
?>
                    <div class="upload w300 fr">
                        <input required type="file" name="file">
                        <div class="gallery"></div>
                        <div class="cb"></div>
                        <label class="btn">فایل‌ را انتخاب کنید یا در این قسمت رها کنید .</label>
                    </div>
                    <div class="form fr has_upload">
                    </div>
            </div>
        </div>
        <input type="submit" name="edit" class="btn" value="ثبت">
        </form>
        <script src="<?php echo $s; ?>js/file_upload.js"></script>
<?php
    }else{
?>
<?php
    $dir = $bu."admin/orders/pay_excel/";
    $st = "SELECT file_name FROM orders_pay_xlsx WHERE id = ?";
    $st = $mysqli->prepare($st);
    $st->bind_param('s',$_SESSION[$cf]->sub_id);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    $file_name = $row["file_name"];
    $file_path = $dir.$file_name;
    
    $pay_refs = array();
    include_once $bu."modules/SimpleXLSX.php";
    if($xlsx = SimpleXLSX::parse($file_path)){
        $k = 0;
        // $j = 0;
        foreach($xlsx->rows() as $kr=>$row){
            // if($k>8){
                if($row[31] == "کارت" && (int)$row[6] == 0){
                    //price
                    $pay_refs[$k][0] = ((int)$row[3])/10;
                    // text
                    $text = $row[7];
                    $text = substr($text,stripos($text,"انتقال از کارت") + 27);
                    $pay_refs[$k][4] = ((int)$text)%10000;
                    $text = substr($text,stripos($text,"شماره مرجع")+20);
                    $pay_refs[$k][1] = (int)$text;
                    $text = substr($text,stripos($text,"شماره پيگيري") + 24);
                    $pay_refs[$k][2] = (int)$text;
                    // row
                    $pay_refs[$k][3] = $row[32];
                    if($pay_refs[$k][1] == 0 || $pay_refs[$k][2] == 0){
                        $text = $row[7].$xlsx->rows()[$kr+8][7];
                        $text = substr($text,stripos($text,"انتقال از کارت") + 27);
                        $pay_refs[$k][4] = ((int)$text)%10000;
                        $text = substr($text,stripos($text,"شماره مرجع")+20);
                        $pay_refs[$k][1] = (int)$text;
                        $text = substr($text,stripos($text,"شماره پيگيري") + 24);
                        $pay_refs[$k][2] = (int)$text;
                        
                    }
                    $k++;
                }
            // }
            
        }
    }
    unset($xlsx);
?>
<style type="text/css">
tr.nf{background-color:rgba(100,100,100,0.5);}
tr.exc{background-color:rgba(0,100,0,0.5);}
</style>
<form action="<?php echo $URL; ?>" method="post">
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
                تاریخ ثبت
            </th>
            <th class="onc"  onclick="sub_show('order','4')">
                مبلغ قابل پرداخت
            </th>
            <th class="onc"  onclick="sub_show('order','5')">
                شماره مرجع / پیگیری ثبت شده
            </th>
            <th class="onc"  onclick="sub_show('order','6')">
                شماره کارت ثبت شده
            </th>
            <th>
                مبلغ پرداخت شده (از فایل)
            </th>
            <th>
                4 رقم شماره کارت (از فایل)
            </th>
            <th>
                شماره مرجع (از فایل)
            </th>
            <th>
                شماره پیگیری (از فایل)
            </th>
            <th>
                تأیید پرداخت 
            </th>
        </tr>
    </thead>
    <tbody>
<?php
{
    {
    $order_str = $_SESSION[$cf]->order_by->order_str($tb);
    $sql_state = 0;
    $state_where = "";
    $state_where = " AND $tb.create_date > NOW() - INTERVAL 10 DAY ";
    $st = "SELECT 
    $tb.id,
    users.name,
    $tb.create_date,
    $tb.pay_price,
    $tb.pay_id,
    $tb.pay_request_auth
    FROM $tb 
    LEFT JOIN users ON $tb.uid = users.id
    WHERE $tb.del_flag = 0 AND 
    $tb.state = $sql_state AND 
    
    admin_state = 1 
    $state_where $order_str";
    
    // ($tb.pay_id IS NOT NULL || $tb.pay_request_auth IS NOT NULL) AND
    
    
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    }
    $res = $st->get_result();
    $k = 0;
    while($row = $res->fetch_assoc())
    {
        $k++;
        $id = $row["id"];
        $name = $row["name"];
        $submit_date = correctDate($row["create_date"]);
        $price = (int)$row["pay_price"];
        $price_show = price_sep($price);
        $pay_id = (int)$row["pay_id"];
        $card = (int)$row["pay_request_auth"];
        
        $excel_card="";
        $excel_ref="";
        $excel_id="";
        $excel_price="";
        $confirm_state = 0;
        $class=" nf "; // not found in excel
        // search for match
        foreach($pay_refs as $key=>$value){
            if( abs($value[0]-$price)<$price_lim && ($value[1] == $pay_id || $value[2] == $pay_id || $value[4] == $card)){
                $excel_ref = $value[1];
                $excel_id = $value[2];
                $excel_price = $value[0];
                $excel_card = $value[4];
                $confirm_state = 1;       //confirm
                $class = "";
                unset($pay_refs[$key]);
                break;
            }
        }
        $excel_price_show = ($excel_price === "") ? "" : price_sep($excel_price);
        $cnf = "<input name='pay[$id]' type='text' value='0' class='hide'><span class='icon icon-chk curpo' onclick='toggle_conf(this)'></span>";
        if($confirm_state == 1){
            $cnf = "<input name='pay[$id]' type='text' value='$excel_ref-$excel_id' class='hide'><span class='icon icon-chkfl curpo' onclick='toggle_conf(this)'></span>";
        }
        
        echo "
        <tr class='$class'>
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
                $submit_date
            </td>
            <td>
                $price_show
            </td>
            <td>
                $pay_id
            </td>
            <td>
                $card
            </td>
            <td>
                $excel_price_show
            </td>
            <td>
                $excel_card
            </td>
            <td>
                $excel_ref
            </td>
            <td>
                $excel_id
            </td>
            <td>
                $cnf
            </td>
        </tr>";
    }
    foreach($pay_refs as $key=>$value){
        $k++;
        $row_id = $value[3];
        $excel_ref =$value[1];
        $excel_id =$value[2];
        $excel_card = $value[4];
        $price =$value[0];
        $price_show = price_sep($price);
        $class = " exc ";
        echo "
        <tr class='$class'>
            <td>
                $k
            </td>
            <td ".'class="id_span"'.">
                $row_id
            </td>
            <td>
                
            </td>
            <td>
                
            </td>
            <td>
            </td>
            <td>
            </td>
            <td>
            </td>
            <td>
                $price_show
            </td>
            <td>
                $excel_card
            </td>
            <td>
                $excel_ref
            </td>
            <td>
                $excel_id
            </td>
            <td>
            </td>
        </tr>";
        
    }
}
?>
    </tbody>
</table>
    <div class="cut"></div>
    <input type="submit" name="pay_ref" class="btn" value="ثبت">
</form>
<script>
    function toggle_conf(item){
        input = item.parentNode.getElementsByTagName('input')[0];
        if(item.classList.contains('icon-chkfl')){
            input.value = 0;
            item.classList.remove('icon-chkfl');
            item.classList.add('icon-chk');
        }else{
            input.value = 1;
            item.classList.add('icon-chkfl');
            item.classList.remove('icon-chk');
        }
    }
    
</script>
<?php
    }
?>
<?php
        }
    }
?>
