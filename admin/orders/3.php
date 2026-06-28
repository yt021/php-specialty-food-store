<?php
    if(isset($indexed)){
        if($indexed == 1){?>
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
    $dir = $bu."admin/orders/post_excel/";
    $st = "SELECT file_name FROM orders_post_xlsx WHERE id = ?";
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
    
    $post_ref_ids = array();
    
    $xlsx = false;
    $rows = false;
    $ff = pathinfo($file_name,PATHINFO_EXTENSION);
    if($ff == "xls"){
        include_once $bu."modules/SimpleXLS.php";
        $xlsx = SimpleXLS::parse($file_path);
        if($xlsx){
            $rows = $xlsx->rows();
        }else{
            $file = file_get_contents($file_path);
            if(stripos($file,'<table')>-1){
                $rows = array();
                $j=0;
                while(stripos($file,'<tr')>-1){
                    //read a row
                    $file = substr($file,stripos($file,'<tr')+3);
                    $tr = substr($file,0,stripos($file,'</tr>'));
                    $tr = substr($tr,stripos($tr,'>')+1);
                    //      echo $tr;
                    $k = 0;
                    while(stripos($tr,'<td')>-1){
                        $tr = substr($tr,stripos($tr,'<td'));
                        $tr = substr($tr,stripos($tr,'>')+1);
                        $td = substr($tr,0,stripos($tr,'</td>'));
                        $rows[$j][$k] = $td;
                        $k++;
                    }
                    //          echo implode('-',$rows[$j]);
                    // echo $rows[$j][2];
                    // echo $rows[$j][11];
                    // echo "<br>";
                    $j++;
                }
            
            }
        }
    }
    if($ff == "xlsx"){
        include_once $bu."modules/SimpleXLSX.php";
        $xlsx = SimpleXLSX::parse($file_path);
        $rows = $xlsx->rows();
    }
    if($rows){
        $k = 0;
        foreach($rows as $row){
            if($k>0){
                $post_ref_ids[$row[11]] = array(                // previously index 11
                    str_replace(" ","",$row[2]),
                    $row[7],    // pre: index 7
                    "پست ".substr($row[6],0,stripos($row[6]," ")),
                    "nf"
                );
                $post_ref_ids[$row[11]][0] = substr($post_ref_ids[$row[11]][0],0,24);
                // $post_ref_ids[$row[10]][0] = substr($post_ref_ids[$row[10]][0],0,24);
            }
            $k++;
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
                استان مشتری
            </th>
            <th class="onc"  onclick="sub_show('order','4')">
                شهر مشتری
            </th>
            <th>
                شهر (از اکسل)
            </th>
            <th class="onc"  onclick="sub_show('order','5')">
                نام گیرنده
            </th>
            <th class="onc"  onclick="sub_show('order','6')">
                تاریخ ثبت
            </th>
            <th>
                ارسال
            </th>
            <th>
                نوع پست (از اکسل)
            </th>
            <th>
                شماره مرسوله پستی 
            </th>
        </tr>
    </thead>
    <tbody>
<?php
{
    $order_str = $_SESSION[$cf]->order_by->order_str($tb);
    $sql_state = 3;
    $state_where = "";
    $state_where = " AND $tb.create_date > '2019-10-10' ";
    $st = "SELECT $tb.id, $tb.unified_id, users.name, addresses.county, addresses.city, addresses.rec_name, $tb.create_date, $tb.recieve_shift 
    FROM $tb 
    LEFT JOIN users ON $tb.uid = users.id
    LEFT JOIN addresses ON $tb.aid = addresses.id
    LEFT JOIN sd_shifts ON $tb.recieve_shift = sd_shifts.id
    WHERE $tb.del_flag = 0 AND 
    $tb.state = $sql_state AND 
    sd_shifts.transporter_id = 1
    $state_where $order_str";

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
        $unified_id = isset($row["unified_id"]) && $row["unified_id"] ? $row["unified_id"] : $id;
        $name = $row["name"];
        $county = $row["county"];
        $city = $row["city"];
        $rec_name = $row["rec_name"];
        $submit_date = correctDate($row["create_date"]);
        $send = cor_sendShift($row["recieve_shift"]);
        
        $excel_city="";
        $excel_send="";
        $excel_pri="";
        $class=" nf "; // not found in excel
        if(isset($post_ref_ids[$id])){
            $excel_city=$post_ref_ids[$id][1];
            $excel_send=$post_ref_ids[$id][2];
            $excel_pri=$post_ref_ids[$id][0];
            $post_ref_ids[$id][3] = "";
            $class = "";
        }
        
        
        echo "
        <tr class='$class'>
            <td>
                $k
            </td>
            <td class=\"id_span\">$unified_id</td>
            <td>$name</td>
            <td>$county</td>
            <td>$city</td>
            <td>$excel_city</td>
            <td>$rec_name</td>
            <td>$submit_date</td>
            <td>$send</td>
            <td>$excel_send</td>
            <td><input name='post[$id]' type='text' value='$excel_pri'></td>
        </tr>";
    }
    foreach($post_ref_ids as $oid=>$value){
        if($value[3] == "nf" && $oid){
            $excel_city=$value[1];
            $excel_send=$value[2];
            $excel_pri=$value[0];
            $class = " exc ";
            echo "
        <tr class='$class'>
            <td>
                
            </td>
            <td class=\"id_span\">$oid</td>
            <td>
                
            </td>
            <td>
                
            </td>
            <td>
                
            </td>
            <td>
                $excel_city
            </td>
            <td>
                
            </td>
            <td>
                
            </td>
            <td>
                
            </td>
            <td>
                $excel_send
            </td>
            <td>
                $excel_pri
            </td>
        </tr>";
        }
    }
}
?>
    </tbody>
</table>
    <div class="cut"></div>
    <input type="submit" name="post_ref" class="btn" value="ثبت">
</form>
<?php
    }
?>
<?php
        }
    }
?>