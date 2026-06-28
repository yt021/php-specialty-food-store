<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <a class="btn" onclick="sub_show('all_users','')">مشاهده تمام مشتریان</a>
        <br>
        <a class="btn" href="users_list.php" target="_blank">دریافت فایل لیست مشتریان</a>
        </div>  
    </div>
    <div class="cut w100p"></div>
<div id="sect2" class="sect">
    <div class="middle container">
        جستجو بر اساس تلفن همراه:
        <form class="date" action="<?php echo $URL; ?>" method="post">
            <label for="tel">تلفن همراه:</label> 
            <input name="tel" type="text" value="">
            <input type="submit" name="find_client" value="تایید">
        </form>
<style type="text/css">
form.date input[type="text"] {
    border: 1px solid gray;
    width: 300px;
    float: none;
}
</style>
<?php
    if(isset($_SESSION[$cf]->id)){
        if(var_exist($_SESSION[$cf]->id,$tb,"tel")){
?>
        <div class="cut w100p"></div>
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        شناسه مشتری
                    </th>
                    <th>
                        نام مشتری
                    </th>
                    <th>
                        تلفن همراه
                    </th>
                    <th>
                        تاریخ عضویت
                    </th>
                    <th>
                        تاریخ آخرین مراجعه
                    </th>
                    <th>
                        مشاهده جزئیات
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
<?php
    $tel = $_SESSION[$cf]->id;
    $id = getVarFromDB("users","id","tel",$tel);
    $name = getVarFromDB("users","name","tel",$tel);
    $create_date = correctDate(getVarFromDB("users","create_date","tel",$tel));
    $last_login = correctDate(getVarFromDB("users","last_login","tel",$tel));
    
    
    echo "          <td ".'class="id_span"'.">
                        $id
                    </td>
                    <td>
                        $name
                    </td>
                    <td>
                        $tel
                    </td>
                    <td>
                        $create_date
                    </td>
                    <td>
                        $last_login
                    </td>
                    <td ".'onclick ="sub_show('."'uid','".$id."'".')" >
                        <span class="curpo icon-i"></span>'."
                    </td>";
?>
                </tr>
            </tbody>
        </table>
<?php
//    if(getVarFromDB($tb,"del_flag","id",$id) == 1){
//        echo "<br>این مشتری حذف شده است.<br>";
//    }    
?>
<?php
        }else{
            echo "<br>مشتری با تلفن همراه مورد نظر وجود ندارد.<br>";
        }
    }
?>

    <div class="cut w100p"></div>
<?php

        $cp = $_SESSION[$cf]->sql_where['cp'];
        $mp = floor($nor/$page_limit);
    if($mp){        
        echo '<ul class="page_buttons">';
        if($cp>0){
            echo '
                    <li onclick ="sub_show('."'cp','".($cp-1)."'".')">
                        «
                    </li>';
        
        if($cp>3){
            echo '
                    <li onclick ="sub_show('."'cp','".(0)."'".')">
                        1
                    </li>
                    <div class="fr">...</div>
                ';
        }
        
            echo '
                <li onclick ="sub_show('."'cp','".($cp-1)."'".')">
                    '.($cp).'
                </li>';
        }
        
            echo '
                <li class="selected">
                    '.($cp+1).'
                </li>';
        if($cp<$mp){
            echo '
                <li onclick ="sub_show('."'cp','".($cp+1)."'".')">
                    '.($cp+2).'
                </li>';
            if($cp<$mp-3){
                echo '        
                    <div class="fr">...</div>
                    <li onclick ="sub_show('."'cp','".($mp)."'".')">
                        '.($mp+1).'
                    </li>';
            }
            echo '
                <li onclick ="sub_show('."'cp','".($cp+1)."'".')">
                    »
                </li>
            ';
        }
        echo '</ul>';
    }
?>
<style type="text/css">
ul.page_buttons li{
    display: block;
    float: right;
    width: 40px;
    height: 40px;
    border: 2px solid maroon;
    text-align: center;
    padding: 6px 0;
    margin: 5px 10px;
    border-radius: 5px;
    cursor:pointer;
}
ul.page_buttons li:hover {
    background-color: rgba(128, 0, 0, 0.2);
}
</style>
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
                        شناسه مشتری
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','name'".')">
                        نام مشتری
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','tel'".')">
                        تلفن همراه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',8".')">
                        استان
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',9".')">
                        شهر
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','create_date'".')">
                        تاریخ عضویت
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','last_login'".')">
                        تاریخ آخرین مراجعه
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',7".')">
                        مرتبه خرید
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order',6".')">
                        مبلغ کل خرید
                    </th>

                    <th>
                        مشاهده جزئیات
                    </th>
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION["$cf"]->order_by->order_str($tb);
            $offset = $cp*$page_limit;
            //$state = getVarFromDB($atb,"id","flag",$_SESSION["orders"]->state)-1;WHERE state = $state
            $tb2 = "orders";
            $tb3 = "addresses";
            $st = "SELECT $tb.id,$tb.name,$tb.tel,$tb.create_date,$tb.last_login,sum($tb2.cart_pure) AS stp,COUNT($tb2.cart_pure) AS ctp,$tb3.county,$tb3.city FROM $tb CROSS JOIN $tb2 CROSS JOIN $tb3 WHERE $tb3.id = $tb2.aid AND $tb2.uid = $tb.id AND $tb2.del_flag = 0 AND $tb2.state > 0 GROUP BY $tb.id $order_str LIMIT $page_limit OFFSET $offset";
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
                $create_date = $row["create_date"];
                $create_date = correctDate($create_date);
                $last_login = $row["last_login"];
                $last_login = correctDate($last_login);
                $total_price = price_sep($row["stp"]);
                $total_no = $row["ctp"];
                $county = $row["county"];
                $city = $row["city"];
                
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
                            $county
                        </td>
                        <td>
                            $city
                        </td>
                        <td>
                            $create_date
                        </td>
                        <td>
                            $last_login
                        </td>
                        <td>
                            $total_no
                        </td>
                        <td>
                            $total_price
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
