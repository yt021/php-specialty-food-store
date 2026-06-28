<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <a class="btn" onclick="sub_show('id','new')">تبلیغ جدید</a>
        </div>  
    </div>
    <div class="cut w100p"></div>

    <div id="sect3" class="sect ">
        <div class="middle container">
<?php

if(isset($_POST["id_del"]) && var_exist($_POST["id_del"],$tb,"id")){
    $del_flag = (int)getVarFromDB($tb,"del_flag","id",$_POST["id_del"]);
    $del_flag = 1 - $del_flag;
    updateInDB($tb,"del_flag",$del_flag,"id",$_POST["id_del"]);
}

    echo '
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',1".')">
                        شناسه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',2".')">
                        نام 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',3".')">
                        تاریخ ایجاد 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',4".')">
                        تاریخ تبلیغ 
                    </th>
                    <th>
                        مجموع هزینه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',5".')">
                        هزینه نقدی
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',6".')">
                        هزینه غیر نقدی
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',7".')">
                        شناسه سفارش مربوطه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',8".')">
                        تعداد مخاطب تبلیغ
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',9".')">
                        تعداد مشاهده تبلیغ
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',10".')">
                        میزان جذب مخاطب
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>
                    <th>
                        حذف
                    </th>                    
                </tr>
            </thead>
            <tbody>';
    $tb2="orders";        
    $order_str = $_SESSION[$cf]->order_by->order_str($tb);
    $st = "SELECT $tb.id,name,$tb.create_date,ad_date,cost_direct,$tb2.sale_total,cost_oid,ad_current_followers,ad_view,follower_increase FROM $tb
    LEFT JOIN $tb2 ON $tb2.id = $tb.cost_oid 
    WHERE $tb.del_flag = 0 $order_str ";
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
        $create_date = correctDate($row["create_date"]);
        $ad_date = correctDate($row["ad_date"]);
        $cost_direct = $row["cost_direct"];
        $cost_indirect = $row["sale_total"];
        $cost_total = $cost_direct+$cost_indirect;
        $cost_oid = $row["cost_oid"];
        $acf = $row["ad_current_followers"];
        $ad_view = $row["ad_view"];
        $f_increase = $row["follower_increase"];
    
        echo "<tr>
                <td>
                    $k
                </td>
                <td>
                    $id
                </td>
                <td>
                    $name
                </td>
                <td>
                    $create_date
                </td>
                <td>
                    $ad_date
                </td>
                <td>
                    $cost_total
                </td>
                <td>
                    $cost_direct
                </td>
                <td>
                    $cost_indirect
                </td>
                <td>
                    $cost_oid
                </td>
                <td>
                    $acf
                </td>
                <td>
                    $ad_view
                </td>
                <td>
                    $f_increase
                </td>
                <td ".'onclick ="sub_show('."'id','".$id."'".')" >
                    <span class="curpo icon-i"></span>'."
                </td>
                <td ".'onclick ="sub_show('."'id_del','".$id."'".')" >
                    <span class="curpo icon-x"></span>'."
                </td>                        
            </tr>";
        }
        echo '</tbody>
            </table>';
?>

<?php
        }
    }
?>
