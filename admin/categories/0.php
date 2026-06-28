<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <a class="btn" onclick="sub_show('new_cat','new')">دسته‌بندی جدید</a>
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
                        نام دسته
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','3'".')">
                        گروه مطالب
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','4'".')">
                        ترتیب نمایش
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            $section = $_SESSION[$cf]->state; 
            $del_flag = 0;
            $st = "SELECT id,name,section,show_order FROM $tb WHERE del_flag = $del_flag $order_str";
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
                $section = $row["section"];
                $section = getVarFromDB("admin_categories_groups","name","flag",$section);
                $show_order = $row["show_order"];
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
                            $section
                        </td>
                        <td>
                            $show_order
                        </td>
                        <td ".'onclick ="sub_show('."'cat_id','".$id."'".')" >
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