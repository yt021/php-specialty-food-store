<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <a class="btn" onclick="sub_show('new_page','new')">صفحه جدید</a>
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
                    <th class="onc" '.' onclick="sub_show('."'order','6'".')">
                        تاریخ آخرین به روز رسانی
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            //$state = getVarFromDB($atb,"id","flag",$_SESSION["orders"]->state)-1;WHERE state = $state
            $del_flag = 0;
            $st = "SELECT id,name,title,keywords,description,last_update FROM $tb WHERE del_flag = $del_flag $order_str";
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
                $title = $row["title"];
                $keywords = $row["keywords"];
                $description = $row["description"];
                $last_update = correctDate($row["last_update"]);
                

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
                        <td>
                            $last_update
                        </td>
                        <td ".'onclick ="sub_show('."'page_id','".$id."'".')" >
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