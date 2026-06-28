<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <a class="btn" onclick="sub_show('id','new')">تردد جدید</a>
        </div>  
    </div>
    <div class="cut w100p"></div>
<?php
    $column_name = "$tb.create_date";
    $where_and = 1;
    include_once $GLOBALS['bu']."modules/wdb/period_select.php";
?>
    
    <div class="cut w100p"></div>
    <div id="sect3" class="sect ">
        <div class="middle container">
<?php
    echo '
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',1".')">
                        نام 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',4".')">
                        تاریخ 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',2".')">
                        ساعت 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',3".')">
                        تردد
                    </th>                  
                </tr>
            </thead>
            <tbody>';
        
    $order_str = $_SESSION[$cf]->order_by->order_str($tb);
    $state = $_SESSION[$cf]->state;
    $st = "SELECT eid,v1,v2,v3 FROM $tb
    WHERE asf = '$state' AND del_flag = 0 $where $order_str ";
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
        $eid = $row["eid"];
        $e_name = getVarFromDB("employees","name","id",$eid);
        $time = $row["v1"];
        $hour = (int)($time/60);
        $minute = $time%60;
        $time = "$hour:$minute";
        $traffic = $row["v2"];
        switch($traffic){
            case 1:
                $traffic = "ورود";
                break;
            case 2:
                $traffic = "خروج";
        }
        $date = correctDate($row["v3"]);
        
    
        echo "<tr>
                <td>
                    $k
                </td>
                <td>
                    $e_name
                </td>
                <td>
                    $date
                </td>
                <td>
                    $time
                </td>
                <td>
                    $traffic
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
