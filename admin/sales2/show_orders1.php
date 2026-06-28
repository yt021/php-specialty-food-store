<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

function correctType($type){
    switch($type){
        case "percentage":
        case "percent":
            return "درصد از جمع سبد خرید";
        case "fixed_amount":
        case "amount":
            return "مبلغ از سبد خرید";
        case "whole_price":
        case "final_price":
        case "fixed_price":
            return "قیمت نهایی سبد خرید";
        case "free_shipping":
        case "free_send":
        case "send":
            return "ارسال رایگان";
        case "gift":
            return "هدیه";
    }
    return $type;
}
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
                        نام خریدار
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','3'".')">
                        تاریخ سفارش
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','4'".')">
                        میزان تخفیف
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','5'".')">
                        مبلغ قابل پرداخت
                    </th>
                </tr>
            </thead>
            <tbody>';
            
        {
            $sid = $_SESSION[$cf]->id;
            $tb = "orders_sale";
            $tb2 = "orders";
            $tb3 = "users";
            $st = "SELECT $tb.oid,$tb3.name,$tb2.create_date,$tb.amount,$tb2.pay_price FROM $tb 
            LEFT JOIN $tb2 ON $tb.oid = $tb2.id
            LEFT JOIN $tb3 ON $tb.uid = $tb3.id
            WHERE $tb.sid = $sid AND $tb2.del_flag = 0 ";
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
                $oid = $row["oid"];
                $name = $row["name"];
                $create_date = correctDate($row["create_date"]);
                $amount = $row["amount"];
                $pay_price = price_sep($row["pay_price"]);
                
                echo "<tr>
                        <td>
                            $k
                        </td>
                        <td>
                            $oid
                        </td>
                        <td>
                            $name
                        </td>
                        <td>
                            $create_date
                        </td>
                        <td>
                            $amount
                        </td>
                        <td>
                            $pay_price
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
