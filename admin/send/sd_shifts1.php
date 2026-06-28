<?php
    if(isset($indexed)){
        if($indexed == 1){?>
                <a class="btn" onclick="sub_show('id','new')">نوبت ارسال جدید</a>
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
    
    if(isset($_POST["del_flag"])){
        $_SESSION[$cf]->del_flag = 1-$_SESSION[$cf]->del_flag;
    }

    if($_SESSION[$cf]->del_flag == 1)echo "<h3>حذف شده ها</h3>";

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
                        شناسه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','2'".')">
                        ارسال‌کننده
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','3'".')">
                        نام
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','4'".')">
                        ساعت شروع
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','5'".')">
                        ساعت پایان
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','6'".')">
                        هزینه
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
            $st = "SELECT id,transporter_id,name,start_hour,end_hour,price FROM $tb WHERE del_flag = 0 ORDER BY transporter_id ASC,start_hour ASC";
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
                $tp_name = getVarFromDB("transporters","name","id",$row["transporter_id"]);
                $name = $row["name"];
                $start_hour = $row["start_hour"];
                $end_hour = $row["end_hour"];
                $price = price_sep($row["price"]);
                
                echo "<tr>
                        <td>
                            $k
                        </td>
                        <td>
                            $id
                        </td>
                        <td>
                            $tp_name
                        </td>
                        <td>
                            $name
                        </td>
                        <td>
                            $start_hour
                        </td>
                        <td>
                            $end_hour
                        </td>
                        <td>
                            $price
                        </td>
                        <td ".'onclick ="sub_show('."'id','".$id."'".')" >
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
