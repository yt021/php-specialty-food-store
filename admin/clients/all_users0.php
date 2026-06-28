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
                        شناسه مشتری
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','2'".')">
                        نام مشتری
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','3'".')">
                        تلفن همراه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','4'".')">
                        رایانامه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','5'".')">
                        تاریخ عضویت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','6'".')">
                        تاریخ آخرین مراجعه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','7'".')">
                        شناسه شبکه اجتماعی
                    </th>
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION["$cf"]->order_by->order_str($tb);
            $tb = "users";
            $st = "SELECT id,name,tel,email,create_date,last_login,social FROM $tb $order_str";
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
                $email = $row["email"];
                $social = $row["social"];
                $create_date = $row["create_date"];
                $create_date = correctDate($create_date);
                $last_login = $row["last_login"];
                $last_login = correctDate($last_login);
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
                            $email
                        </td>
                        <td>
                            $create_date
                        </td>
                        <td>
                            $last_login
                        </td>
                        <td>
                            $social
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