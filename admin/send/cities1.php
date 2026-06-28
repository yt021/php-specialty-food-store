<?php
    if(isset($indexed)){
        if($indexed == 1){?>
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
                        استان
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','3'".')">
                        بخش‌ها
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','4'".')">
                        هزینه ارسال
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            $st = "SELECT id,county,cities_str,cost FROM $tb WHERE $tb.del_flag = 0";
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
                $county = $row["county"];
                $cities_str = $row["cities_str"];
                $cities_str = str_replace(',',', ',$cities_str);
                $cost = $row["cost"];
                
                echo "<tr>
                        <td>
                            $k
                        </td>
                        <td>
                            $id
                        </td>
                        <td>
                            $county
                        </td>
                        <td>
                            $cities_str
                        </td>
                        <td>
                            $cost
                        </td>
                        <td ".'onclick ="sub_show('."'id','".$id."'".')" >
                            <span class="curpo icon-i"></span>'."
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