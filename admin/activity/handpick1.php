<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $state = $_SESSION[$cf]->state;
    if(isset($_POST["id_del"]) && var_exist($_POST["id_del"],$tb,"id")){
        $del_flag = (int)getVarFromDB($tb,"del_flag","id",$_POST["id_del"]);
        $del_flag = 1 - $del_flag;
        updateInDB($tb,"del_flag",$del_flag,"id",$_POST["id_del"]);
    }
?>
        <a class="btn" onclick="sub_show('id','new')">فعالیت جدید</a>
        </div>  
    </div>
    <div class="cut w100p"></div>
<?php
    $column_name = "$tb.create_date";
    $where_and = 1;
    include_once $GLOBALS['bu']."modules/wdb/period_select.php";
?>
    <div class="cut w100p"></div>
    <form class="date" action="<?php echo $URL; ?>" method="post">
        انتخاب شخص:
        <select name="eid">
<?php
    $st = "SELECT id,name FROM employees
    WHERE del_flag = 0";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row = $res->fetch_assoc())
    {
        $name = $row["name"];
        $id = $row["id"];
        $selected = "";
        if($_SESSION[$cf]->sql_where["eid"] == $id)$selected=" selected ";
?>
            <option <?php echo $selected; ?> value="<?php echo $id; ?>"><?php echo $name; ?></option>
<?php
    }
?>
        </select>
        <input type="submit" name="eid_submit" value="تایید">
    </form>
<?php
    $eid = $_SESSION[$cf]->sql_where["eid"];
?>
    <div class="cut w100p"></div>
    <div id="sect3" class="sect ">
        <div class="middle container">
<?php
$th="";
if($_SESSION["a_logged"]->get_level() >= 2){
    $th = '
    <th>
        مشاهده جزئیات
    </th>
    <th>
        حذف
    </th>  
    ';
}
    echo '
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',1".')">
                        نام 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',4".')">
                        تاریخ 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',2".')">
                        محصول 
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',3".')">
                        وزن اولیه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',5".')">
                        ضایعات
                    </th>
                    <th>
                        وزن دستچین شده
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',6".')">
                        تامین‌کننده
                    </th>
                    '.$th.'
                </tr>
            </thead>
            <tbody>';
        
    $order_str = $_SESSION[$cf]->order_by->order_str($tb);
    
    $st = "SELECT id,eid,v1,v2,v3,v4,v5 FROM $tb
    WHERE eid = $eid AND asf = '$state' AND del_flag = 0 $where $order_str ";
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
        $eid = $row["eid"];
        $e_name = getVarFromDB("employees","name","id",$eid);
        $date = correctDate($row["v3"]);
        
        $pid = $row["v1"];
        $p_name =  getVarFromDB("products","name","id",$pid);
        
        
        $t_weight = $row["v2"];
        $w_weight = $row["v4"];
        $s_weight = $t_weight - $w_weight;
        
        $sid = $row["v5"];
        $s_name =  getVarFromDB("suppliers","name","id",$sid);
$td="";
if($_SESSION["a_logged"]->get_level() >= 2){
    $td ="
    <td ".'onclick ="sub_show('."'id','".$id."'".')" >
        <span class="curpo icon-i"></span>'."
    </td>
    <td ".'onclick ="sub_show('."'id_del','".$id."'".')" >
        <span class="curpo icon-x"></span>'."
    </td> 
    ";
}         
    
        echo "<tr>
                <td>
                    $k
                </td>
                <td>
                    $e_name
                </td>
                <td>
                    $date
                </td>
                <td>
                    $p_name
                </td>
                <td>
                    $t_weight
                </td>
                <td>
                    $w_weight
                </td>
                <td>
                    $s_weight
                </td>
                <td>
                    $s_name
                </td>
                $td
            </tr>";
        }
        echo '</tbody>
            </table>';
?>

<?php
        }
    }
?>
