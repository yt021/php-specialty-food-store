<?php
    if(isset($indexed)){
        if($indexed == 1){?>
    <form class="date" action="<?php echo $URL; ?>" method="post">
    انتخاب شروع بازه زمانی:
        <label for="day">روز:</label> 
        <select name="day">
            <?php
                for($i=1;$i<=31;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where["start_date"]["day"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <label for="month">ماه:</label> 
        <select name="month">
            <?php
                for($i=1;$i<=12;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where["start_date"]["month"] == $i)$selected=" selected ";
                    include_once $GLOBALS['bu']."modules/jdf.php";
                    $timetext = jmktime(1,0,0,$i,1,1397);
                    $month = jdate("F",(int)$timetext,"","","en");
                    echo '<option '.$selected.' value="'.$i.'">'.$month.'</option>';
                }
            ?>
        </select>
        <label for="year">سال:</label> 
        <select name="year">
            <?php
                include_once $GLOBALS['bu']."modules/jdf.php";
                $year = (int)jdate("Y","","","","en");
                for($i=1397;$i<=$year;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where["start_date"]["year"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <input type="submit" name="start_date" value="تایید">
    </form>
    <form class="date" action="<?php echo $URL; ?>" method="post">
    انتخاب پایان بازه زمانی:
                <label for="day">روز:</label> 
        <select name="day">
            <?php
                for($i=1;$i<=31;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where["end_date"]["day"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <label for="month">ماه:</label> 
        <select name="month">
            <?php
                for($i=1;$i<=12;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where["end_date"]["month"] == $i)$selected=" selected ";
                    include_once $GLOBALS['bu']."modules/jdf.php";
                    $timetext = jmktime(1,0,0,$i,1,1397);
                    $month = jdate("F",(int)$timetext,"","","en");
                    echo '<option '.$selected.' value="'.$i.'">'.$month.'</option>';
                }
            ?>
        </select>
        <label for="year">سال:</label> 
        <select name="year">
            <?php
                include_once $GLOBALS['bu']."modules/jdf.php";
                $year = (int)jdate("Y","","","","en");
                for($i=1397;$i<=$year;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where["end_date"]["year"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <input type="submit" name="end_date" value="تایید">
    </form>
    <div class="cut w100p"></div>
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
            if(isset($_SESSION[$cf]->sql_where["start_date"])){
                $sql_timestamp_str = createSQLTime($_SESSION[$cf]->sql_where["start_date"]["year"],$_SESSION[$cf]->sql_where["start_date"]["month"],$_SESSION[$cf]->sql_where["start_date"]["day"]);
                
                $where = " $tb.create_date > '$sql_timestamp_str'";
            }
            if(isset($_SESSION[$cf]->sql_where["end_date"])){
                $sql_timestamp_str = createSQLTime($_SESSION[$cf]->sql_where["end_date"]["year"],$_SESSION[$cf]->sql_where["end_date"]["month"],$_SESSION[$cf]->sql_where["end_date"]["day"]);
                
                if(isset($where))$where .= " AND ";else $where ="";
                $where .= " $tb.create_date < '$sql_timestamp_str'";
            }
            if(isset($where))$where = " AND $where";else $where = "";
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            $state = getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state);
            $st = "SELECT pid,weight,SUM(number) AS S_N FROM sub_$tb CROSS JOIN $tb WHERE sub_$tb.oid = $tb.id AND $tb.del_flag = 0 AND $tb.state = 1 $where GROUP BY 1,2 ;";
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
        }
    }
?>