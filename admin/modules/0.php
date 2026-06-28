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
                        نام صفحه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','3'".')">
                        عنوان صفحه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','4'".')">
                        کلمات کلیدی
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','5'".')">
                        توضیحات
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            $st = "SELECT id,name_fa,title,keywords,description FROM $tb $order_str";
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
                $name = $row["name_fa"];
                $title = $row["title"];
                $keywords = $row["keywords"];
                $description = $row["description"];
                
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
                            $title
                        </td>
                        <td>
                            $keywords
                        </td>
                        <td>
                            $description
                        </td>
                        <td ".'onclick ="sub_show('."'mdl_id','".$id."'".')" >
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