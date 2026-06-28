<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $column_name = "orders.p_send_date";
    $where_and = 1;
    include_once $GLOBALS['bu']."modules/wdb/period_select.php";
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
                        آدرس
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','4'".')">
                        تاریخ ثبت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','5'".')">
                        مبلغ قابل پرداخت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','6'".')">
                        تاریخ ارسال (شهر تهران)
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','7'".')">
                        تاریخ تحویل (شهر تهران)
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','8'".')">
                        نوبت تحویل
                    </th>
                    ';
            echo        '
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            $tb = "orders";
            $st = "SELECT $tb.id,users.name,addresses.address,$tb.create_date,$tb.pay_price,$tb.p_send_date,$tb.recieve_date,$tb.recieve_shift FROM $tb 
            LEFT JOIN users ON $tb.uid = users.id
            LEFT JOIN addresses ON $tb.aid = addresses.id
            WHERE $tb.del_flag = 0 AND $tb.recieve_shift IS NOT NULL $where $order_str";
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
                $address = $row["address"];
                $create_date = correctDate($row["create_date"]);
                $pay_price = price_sep($row["pay_price"]);
                $send_date = correctDate($row["p_send_date"]);
                $rec_date = correctDate($row["recieve_date"]);
                $rec_shift = $row["recieve_shift"];
                $rec_shift = cor_sendShift($rec_shift);

                
                echo "<tr>
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
                            $address
                        </td>
                        <td>
                            $create_date
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
                        <td>
                            $rec_shift
                        </td>
                        ";
                echo    "</tr>";
            }
        }
        echo '</tbody>
            </table>';
?>
<?php
        }
    }
?>
