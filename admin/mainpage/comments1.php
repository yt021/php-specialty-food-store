<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

if(isset($_POST["cid_show"]) && var_exist($_POST["cid_show"],$tb,"id")){
    $show_flag = (int)getVarFromDB($tb,"show_flag","id",$_POST["cid_show"]);
    $show_flag = 1 - $show_flag;
    updateInDB($tb,"show_flag",$show_flag,"id",$_POST["cid_show"]);
}

    echo '
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','id'".')">
                        شناسه پیام
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','name'".')">
                        نام 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','tel'".')">
                        تلفن
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','email'".')">
                        رایانامه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','create_date'".')">
                        تاریخ ثبت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','comment'".')">
                        متن پیام
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','show_flag'".')">
                        وضعیت نمایش
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','reply'".')">
                        متن پاسخ
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','reply_date'".')">
                        زمان پاسخ
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>
                </tr>
            </thead>
            <tbody>';
            
    $order_str = $_SESSION[$cf]->order_by->order_str($tb);
    $st = "SELECT * FROM $tb $order_str";
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
        $comment = $row["comment"];
        $create_date = correctDate($row["create_date"]);
        $show_flag = $row["show_flag"];
        if($show_flag == 1)$show_flag = "icon-chkfl";else $show_flag = "icon-chk";
        $reply = $row["reply"];
        $reply_date = correctDate($row["reply_date"]);
        
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
                            $comment
                        </td>
                        <td ".'onclick ="sub_show('."'cid_show','".$id."'".')" >
                            <span class="curpo '.$show_flag.'"></span>'."
                        </td>
                        <td>
                            $reply
                        </td>
                        <td>
                            $reply_date
                        </td>
                        <td ".'onclick ="sub_show('."'id','".$id."'".')" >
                            <span class="curpo icon-i"></span>'."
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