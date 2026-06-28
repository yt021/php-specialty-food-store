<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <a class="btn" onclick="sub_show('id','new')">افزودن اسلایدر</a>
        </div>  
    </div>
    <div class="cut w100p"></div>

    <div id="sect3" class="sect ">
        <div class="middle container">
<?php

if(isset($_POST["sid_show"]) && var_exist($_POST["sid_show"],$tb,"id")){
    $show_flag = (int)getVarFromDB($tb,"show_flag","id",$_POST["sid_show"]);
    $show_flag = 1 - $show_flag;
    updateInDB($tb,"show_flag",$show_flag,"id",$_POST["sid_show"]);
}
if(isset($_POST["sid_del"]) && var_exist($_POST["sid_del"],$tb,"id")){
    $del_flag = (int)getVarFromDB($tb,"del_flag","id",$_POST["sid_del"]);
    $del_flag = 1 - $del_flag;
    updateInDB($tb,"del_flag",$del_flag,"id",$_POST["sid_del"]);
}

if(isset($_POST["del_flag"])){
    $_SESSION[$cf]->del_flag = 1-$_SESSION[$cf]->del_flag;
}

if($_SESSION[$cf]->del_flag == 1)echo "<h3>حذف شده ها</h3>";

    echo '
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','id'".')">
                        شناسه اسلایدر
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','title'".')">
                        عنوان اسلایدر 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','detail'".')">
                        متن اسلایدر
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','link'".')">
                        لینک اسلایدر
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','create_date'".')">
                        تاریخ ثبت
                    </th>
                    <th>
                        تصویر اسلایدر
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','show_flag'".')">
                        وضعیت نمایش
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'del_flag','-'".')">
                        حذف
                    </th>
                </tr>
            </thead>
            <tbody>';
            
    $order_str = $_SESSION[$cf]->order_by->order_str($tb);
    $del_flag = $_SESSION[$cf]->del_flag;
    $st = "SELECT * FROM $tb WHERE del_flag = $del_flag $order_str ";
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
        $title = $row["title"];
        $detail = $row["detail"];
        $link = $row["link"];
        $create_date = correctDate($row["create_date"]);
        $image_id = $row["image_id"];
        if($image_id)$image_fn = getVarFromDB("content","file_name","id",$image_id);else $image_fn = false;
        $show_flag = $row["show_flag"];
        if($show_flag == 1)$show_flag = "icon-chkfl";else $show_flag = "icon-chk";
        
                echo "<tr>
                        <td>
                            $k
                        </td>
                        <td>
                            $id
                        </td>
                        <td>
                            $title
                        </td>
                        <td>
                            $detail
                        </td>
                        <td>
                            $link
                        </td>
                        <td>
                            $create_date
                        </td>
                        <td>
                            <img src=".'"'.$s."content/$image_fn".'"'."/>
                        </td>
                        <td ".'onclick ="sub_show('."'sid_show','".$id."'".')" >
                            <span class="curpo '.$show_flag.'"></span>'."
                        </td>
                        <td ".'onclick ="sub_show('."'id','".$id."'".')" >
                            <span class="curpo icon-i"></span>'."
                        </td>
                        <td ".'onclick ="sub_show('."'sid_del','".$id."'".')" >
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