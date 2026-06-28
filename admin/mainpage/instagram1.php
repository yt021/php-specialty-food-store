<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <a class="btn" onclick="sub_show('id','new')">افزودن پست اینستاگرام</a>
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
                        شناسه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','data'".')">
                        محتوا 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','create_date'".')">
                        تاریخ ثبت
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>
                </tr>
            </thead>
            <tbody>';
            
    $order_str = $_SESSION[$cf]->order_by->order_str($tb);
    $st = "SELECT * FROM $tb  $order_str ";
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
        $data = $row["data"];
        $create_date = correctDate($row["create_date"]);

                echo "<tr>
                        <td>
                            $k
                        </td>
                        <td>
                            $id
                        </td>
                        <td style=".'"display:table;"'.">
                            $data
                        </td>
                        <td>
                            $create_date
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
