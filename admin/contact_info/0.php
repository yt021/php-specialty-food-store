<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <a class="btn" onclick="sub_show('id','new')">افزودن اطلاعات تماس</a>
        </div>  
    </div>
    <div class="cut w100p"></div>

    <div id="sect3" class="sect ">
        <div class="middle container">
<?php
if(isset($_POST["id_show"]) && var_exist($_POST["id_show"],$tb,"id")){
    $footer_sf = (int)getVarFromDB($tb,"footer_sf","id",$_POST["id_show"]);
    $footer_sf = 1 - $footer_sf;
    updateInDB($tb,"footer_sf",$footer_sf,"id",$_POST["id_show"]);
}
if(isset($_POST["admin_contact"]) && var_exist($_POST["admin_contact"],$tb,"id")){
    $footer_ac = (int)getVarFromDB($tb,"footer_ac","id",$_POST["admin_contact"]);
    $footer_ac = 1 - $footer_ac;
    updateInDB($tb,"footer_ac",$footer_ac,"id",$_POST["admin_contact"]);
}
if(isset($_POST["id_del"]) && var_exist($_POST["id_del"],$tb,"id")){
    $del_flag = (int)getVarFromDB($tb,"del_flag","id",$_POST["id_del"]);
    $del_flag = 1 - $del_flag;
    updateInDB($tb,"del_flag",$del_flag,"id",$_POST["id_del"]);
}

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
                    <th class="onc" '.' onclick="sub_show('."'order','name'".')">
                        عنوان 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','value'".')">
                        مقدار 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','link'".')">
                        لینک 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','create_date'".')">
                        تاریخ ثبت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','footer_sf'".')">
                        نمایش در فوتر سایت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','footer_ac'".')">
                        نمایش در ارتباط با ادمین (فوتر سایت)
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>
                    <th>
                        حذف
                    </th>                    
                </tr>
            </thead>
            <tbody>';
            
    $order_str = $_SESSION[$cf]->order_by->order_str($tb);
    $st = "SELECT * FROM $tb WHERE del_flag = 0 $order_str ";
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
        $value = $row["value"];
        $link = $row["link"];
        $create_date = correctDate($row["create_date"]);
        $footer_sf = $row["footer_sf"];
        if($footer_sf == 1)$footer_sf = "icon-chkfl";else $footer_sf = "icon-chk";
        $footer_ac = $row["footer_ac"];
         if($footer_ac == 1)$footer_ac = "icon-chkfl";else $footer_ac = "icon-chk";
       
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
                            $value
                        </td>
                        <td>
                            $link
                        </td>
                        <td>
                            $create_date
                        </td>
                        <td ".'onclick ="sub_show('."'id_show','".$id."'".')" >
                            <span class="curpo '.$footer_sf.'"></span>'."
                        </td>
                        <td ".'onclick ="sub_show('."'admin_contact','".$id."'".')" >
                            <span class="curpo '.$footer_ac.'"></span>'."
                        </td>
                        <td ".'onclick ="sub_show('."'id','".$id."'".')" >
                            <span class="curpo icon-i"></span>'."
                        </td>
                        
                        <td ".'onclick ="sub_show('."'id_del','".$id."'".')" >
                            <span class="curpo icon-x"></span>'."
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
