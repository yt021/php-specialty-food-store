<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <a class="btn" onclick="sub_show('id','new')">کارکن جدید</a>
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


    echo '
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','id'".')">
                        شناسه کارکن
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','name'".')">
                        نام
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','hire_date'".')">
                        تاریخ شروع به فعالیت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','fire_date'".')">
                        تاریخ اتمام همکاری
                    </th>
                    <th>
                        تاریخ آخرین فعالیت
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
            $del_flag = $_SESSION[$cf]->del_flag;
            $st = "SELECT id,name,hire_date,fire_date FROM $tb WHERE del_flag = $del_flag $order_str";
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
                $hire_date = correctDate($row["hire_date"]);
                $fire_date = correctDate($row["fire_date"]);
                $last_activity = correctDate(getVarFromDB("activity","create_date","eid",$id,"create_date DESC"));

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
                            $hire_date
                        </td>
                        <td>
                            $fire_date
                        </td>
                        <td>
                            $last_activity
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