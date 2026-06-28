<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <a class="btn" onclick="sub_show('new_admin','new')">کاربر جدید</a>
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
                        شناسه کاربر
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','username'".')">
                        نام کاربری
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','name'".')">
                        نام
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','create_date'".')">
                        تاریخ شروع
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','last_login'".')">
                        تاریخ آخرین ورود
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','level'".')">
                        سطح
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            $level = $_SESSION["a_logged"]->get_level();
            $st = "SELECT id,username,name,create_date,last_login,level FROM $tb WHERE level < $level $order_str";
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
                $username = $row["username"];
                $create_date = $row["create_date"];
                $create_date = correctDate($create_date);
                $last_login = $row["last_login"];
                $last_login = correctDate($last_login);
                $level = $row["level"];

                echo "<tr>
                        <td>
                            $k
                        </td>
                        <td>
                            $id
                        </td>
                        <td>
                            $username
                        </td>
                        <td>
                            $name
                        </td>
                        <td>
                            $create_date
                        </td>
                        <td>
                            $last_login
                        </td>
                        <td>
                            $level
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