<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(!isset($_SESSION[$cf]->sql_where["date_base"]))$_SESSION[$cf]->sql_where["date_base"] = "create_date";
    
    $column_name = "$tb.".$_SESSION[$cf]->sql_where["date_base"];
    $where_and = 1;
    include_once $GLOBALS['bu']."modules/wdb/period_select.php";
?>
    </div>  
</div>          
<div class="cut w100p"></div>
<div id="sect2" class="sect">
    <div class="middle container">
        <form class="date" action="<?php echo $URL; ?>" method="post">
            <label>فیلتر تهران / شهرستان:</label>
            <select name="county_filter">
                <?php
                    $items = array("all"=>"همه","tehran"=>"تهران","others"=>"شهرستان‌ها");
                    foreach($items as $key=>$value){
                        $selected = "";
                        if($_SESSION[$cf]->sql_where["county_filter"] == $key)$selected=" selected ";
                        echo '<option '.$selected.' value="'.$key.'">'.$value.'</option>';
                    }
                ?>
            </select>
            <label>مبنای تاریخ:</label>
            <select name="date_base">
                <?php
                    $items = array("create_date"=>"تاریخ ثبت","p_send_date"=>"تاریخ ارسال ");
                    foreach($items as $key=>$value){
                        $selected = "";
                        if($_SESSION[$cf]->sql_where["date_base"] == $key)$selected=" selected ";
                        echo '<option '.$selected.' value="'.$key.'">'.$value.'</option>';
                    }
                ?>
            </select>
            <input type="submit" name="cf_submit" value="تأیید">
        </form>
    </div>  
</div>
<?php
    $cf_where = "";
    if(isset($_SESSION[$cf]->sql_where["county_filter"])){
        switch($_SESSION[$cf]->sql_where["county_filter"]){
            case "tehran":
                $cf_where = " AND addresses.county = 'شهر تهران' ";
                break;
            case "others":
                $cf_where = " AND NOT addresses.county = 'شهر تهران' ";
                break;
        }
    }
?>
<div class="cut w100p"></div>
<div id="sect2" class="sect">
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
                        نام محصول
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','2'".')">
                        وزن
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','3'".')">
                        تعداد
                    </th>
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            $state = $_SESSION[$cf]->state;
            $st = "SELECT pid,weight,SUM(number) AS S_N FROM sub_$tb CROSS JOIN $tb LEFT JOIN addresses ON $tb.aid = addresses.id WHERE sub_$tb.oid = $tb.id AND $tb.del_flag = 0 AND $tb.state = 1 AND sub_$tb.type = '$state' $cf_where $where GROUP BY 1,2 ;";
            
            // echo $st;
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
                $id = $row["pid"];
                $name = getVarFromDB("products","name","id",$id);
                $total_no = $row["S_N"];
                $weight = $row["weight"];
                
                // Gifts
                $gift_value = '';
                $min_weight = getVarFromDB('products_price','weight','pid',$id,'start_time DESC');
                $min_weight = explode(',',$min_weight)[0];
                
                // echo $cf_where."<br><br>".$where;
                if($weight == $min_weight){
                $st = "SELECT count(orders_sale.id) as cid FROM orders_sale CROSS JOIN sales CROSS JOIN orders LEFT JOIN addresses ON orders.aid = addresses.id WHERE orders_sale.sid = sales.id AND sales.type = 'gift' AND sales.amount = $id AND orders_sale.oid = orders.id AND orders.state = 1 $cf_where $where";
                
                $gift = return_sel_sql($st)->fetch_assoc()['cid'];
                if($gift>0)$gift_value = ' +('.$gift.')';
                }
                
                $total_no .=$gift_value;
                
                echo "<tr>
                        <td>
                            $k
                        </td>
                        <td>
                            $name
                        </td>
                        <td>
                            $weight
                        </td>
                        <td>
                            $total_no
                        </td>
                    </tr>";
            }
        }
        echo '</tbody>
            </table>';
?>
<?php
    if($state == 'box'){
?>
<div class="cut"></div>
<div id="sect2" class="sect">
    <div class="middle container">
            <a class="btn submit" href="box_detail_all.php" target="blank">چاپ مشخصات جعبه ها</a>
    </div>  
</div>
<?php
    }
?>
<?php
        }
    }
?>