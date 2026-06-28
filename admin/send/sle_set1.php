<?php
    if(isset($indexed)){
        if($indexed == 1){?>
            <a class="btn" onclick="sub_show('id','new')">قالب جدید لیست ارسال</a>
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
                    <th class="onc" '.' onclick="sub_show('."'order','1'".')">
                        شناسه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','2'".')">
                        ارسال‌کننده
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','3'".')">
                        تاریخ ثبت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','4'".')">
                        تعداد ردیف ثابت
                    </th>
                    <th>
                        دریافت پرونده نمونه
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            $st = "SELECT id,transporter_id,create_date,file_name,static_rows FROM $tb ";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "E";
                exit;
            }
            $res = $st->get_result();
            $k = 0;
            $dir = $s."admin/send/excel_files/";
            while($row = $res->fetch_assoc())
            {
                $k++;
                $id = $row["id"];
                $transporter_id = $row["transporter_id"];
                $transporter = getVarFromDB("transporters","name","id",$transporter_id);
                $create_date = correctDate($row["create_date"]);
                $file_path = $dir.$row["file_name"];
                $st_rows = $row["static_rows"];
                
                echo "<tr>
                        <td>
                            $k
                        </td>
                        <td>
                            $id
                        </td>
                        <td>
                            $transporter
                        </td>
                        <td>
                            $create_date
                        </td>
                        <td>
                            $st_rows
                        </td>
                        <td>
                            <a href='$file_path'>
                                <span class='curpo icon-a_s'></span>
                            </a>
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