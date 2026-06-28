<?php
    if(isset($indexed)){
        if($indexed == 1){?>
            <a class="btn" onclick="sub_show('pid','new')">محصول جدید</a>
        </div>  
    </div>
    <div class="cut w100p"></div>

    <div id="sect3" class="sect ">
        <div class="middle container">
<?php

if(isset($_POST["id_show"]) && var_exist($_POST["id_show"],$tb,"id")){
    echo "here";
    $show_flag = (int)getVarFromDB($tb,"state","id",$_POST["id_show"]);
    $show_flag = 1 - $show_flag;
    updateInDB($tb,"state",$show_flag,"id",$_POST["id_show"]);
}
if(isset($_POST["id_del"]) && var_exist($_POST["id_del"],$tb,"id")){
    $del_flag = (int)getVarFromDB($tb,"del_flag","id",$_POST["id_del"]);
    $del_flag = 1 - $del_flag;
    updateInDB($tb,"del_flag",$del_flag,"id",$_POST["id_del"]);
}

if(isset($_POST["del_flag"])){
    $_SESSION[$cf]->del_flag = 1-$_SESSION[$cf]->del_flag;
}

if($_SESSION[$cf]->del_flag == 1)echo "<h3>حذف شده ها</h3>";

    echo '
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','1'".')">
                        شناسه محصول
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','2'".')">
                        نام محصول
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','8'".')">
                        نوع
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','3'".')">
                        دسته‌بندی
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','4'".')">
                        وزن‌ها
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','5'".')">
                        قیمت روز
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','6'".')">
                        تاریخ آخرین به روز رسانی قیمت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','total_no'".')">
                        تعداد خرید
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','7'".')">
                        ترتیب نمایش
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','state'".')">
                        وضعیت ناموجودی
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'del_flag','-'".')">
                        حذف
                    </th>
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            //$state = getVarFromDB($atb,"id","flag",$_SESSION["orders"]->state)-1;WHERE state = $state
            $del_flag = $_SESSION[$cf]->del_flag;
            $tb2 = $tb."_price";
            $st = "SELECT $tb.id,$tb.name,$tb.category,$tb2.weight,$tb2.price,$tb2.start_time,$tb.show_order,$tb.type,$tb.state,$tb.del_flag FROM $tb CROSS JOIN $tb2 WHERE $tb.del_flag = $del_flag AND $tb.id = $tb2.pid AND $tb2.start_time = (SELECT MAX($tb2.start_time) FROM $tb2 WHERE $tb2.pid = $tb.id ) $order_str";
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
                $type = $row["type"];
                $type = getVarFromDB('admin_orders_type','name','flag',$type);
                $category = $row["category"];
                $weight = $row["weight"];
                $price = $row["price"];
                $price_show = (string)$price;
                $discount_row = product_discount_get_active($id);
                if($discount_row !== false){
                    $weight_list = product_discount_csv_to_array((string)$weight);
                    $base_price_list = product_discount_csv_to_array((string)$price);
                    if(sizeof($base_price_list) > 0){
                        $prepared = product_discount_prepare_price_lists($base_price_list,$discount_row,$weight_list);
                        if(isset($prepared["price"]) && is_array($prepared["price"])){
                            $price_show = implode(",",$prepared["price"]);
                        }
                        if(!empty($prepared["has_discount"])){
                            $price_show .= ' <span style="color:#0a8f08;">%</span>';
                        }
                    }else{
                        $discount_price = product_discount_apply_to_weight_price((float)$price,(string)$weight,$discount_row);
                        if((int)$discount_price !== (int)$price){
                            $price_show = (string)$discount_price.' <span style="color:#0a8f08;">%</span>';
                        }
                    }
                }
                $last_update = correctDate($row["start_time"]);
                $show_order = $row["show_order"];
                $state = $row["state"];
                if($state == 1)$state = "icon-chkfl";else $state = "icon-chk";
                $del_flag = $row["del_flag"];
                
                $st = "SELECT SUM(number) FROM sub_orders CROSS JOIN orders WHERE sub_orders.pid = ? AND sub_orders.oid = orders.id AND orders.del_flag = 0";
                $st = $mysqli->prepare($st);
                $st->bind_param("s",$id);
                if(!$st->execute()){
                    echo "E";
                    exit;
                }
                $st->store_result();
                $st->bind_result($total_no);
                $st->fetch();


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
                            $type
                        </td>
                        <td>
                            $category
                        </td>
                        <td>
                            $weight
                        </td>
                        <td>
                            $price_show
                        </td>
                        <td>
                            $last_update
                        </td>
                        <td>
                            $total_no
                        </td>
                        <td>
                            $show_order
                        </td>
                        <td ".'onclick ="sub_show('."'id_show','".$id."'".')" >
                            <span class="curpo '.$state.'"></span>'."
                        </td>
                        <td ".'onclick ="sub_show('."'pid','".$id."'".')" >
                            <span class="curpo icon-i"></span>'."
                        </td>
                        <td ".'onclick ="sub_show('."'id_del','".$id."'".')" >
                            <span class="curpo icon-x"></span>'."
                        </td>
                    </tr>";
            }
        }
        echo '</tbody>
            </table>';
?>

<?php
        }
    }
?>
