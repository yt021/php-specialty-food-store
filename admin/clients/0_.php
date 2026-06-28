<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <a class="btn" onclick="sub_show('all_users','')">مشاهده تمام مشتریان</a>
        </div>  
    </div>
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
                    <th class="onc" '.' onclick="sub_show('."'order','id'".')">
                        شناسه مشتری
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','name'".')">
                        نام مشتری
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','tel'".')">
                        تلفن همراه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',8".')">
                        استان
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',9".')">
                        شهر
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','create_date'".')">
                        تاریخ عضویت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','last_login'".')">
                        تاریخ آخرین مراجعه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',7".')">
                        مرتبه خرید
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',6".')">
                        مبلغ کل خرید
                    </th>

                    <th>
                        مشاهده جزئیات
                    </th>
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION["$cf"]->order_by->order_str($tb);
            //$state = getVarFromDB($atb,"id","flag",$_SESSION["orders"]->state)-1;WHERE state = $state
            $tb2 = "orders";
            $tb3 = "addresses";
            $st = "SELECT $tb.id,$tb.name,$tb.tel,$tb.create_date,$tb.last_login,sum($tb2.cart_pure) AS stp,COUNT($tb2.cart_pure) AS ctp,$tb3.county,$tb3.city FROM $tb CROSS JOIN $tb2 CROSS JOIN $tb3 WHERE $tb3.id = $tb2.aid AND $tb2.uid = $tb.id AND $tb2.del_flag = 0 AND $tb2.state > 0 GROUP BY $tb.id $order_str";
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
                $tel = $row["tel"];
                $create_date = $row["create_date"];
                $create_date = correctDate($create_date);
                $last_login = $row["last_login"];
                $last_login = correctDate($last_login);
                $total_price = price_sep($row["stp"]);
                $total_no = $row["ctp"];
                $county = $row["county"];
                $city = $row["city"];
                
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
                            $tel
                        </td>
                        <td>
                            $county
                        </td>
                        <td>
                            $city
                        </td>
                        <td>
                            $create_date
                        </td>
                        <td>
                            $last_login
                        </td>
                        <td>
                            $total_no
                        </td>
                        <td>
                            $total_price
                        </td>
                        <td ".'onclick ="sub_show('."'uid','".$id."'".')" >
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
