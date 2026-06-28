<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        
    <form class="date" action="<?php echo $URL; ?>" method="post">
    انتخاب ماه کاری:
        <label for="month">ماه:</label> 
        <select name="month">
            <?php
                for($i=1;$i<=12;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where["date"]["month"] == $i)$selected=" selected ";
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
                for($i=1397;$i<=$year+1;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where["date"]["year"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <input type="submit" name="date" value="تایید">
    </form>
    
    <div class="cut w100p"></div>
<?php
    $month = (int)$_SESSION[$cf]->sql_where["date"]["month"];
    $year = (int)$_SESSION[$cf]->sql_where["date"]["year"];
    $sql_start_ts_str = createSQLTime($year,$month,1);
    $sql_end_ts_str = createSQLTime($year,$month+1,1);
    $where = " $tb.date > '$sql_start_ts_str' AND $tb.date < '$sql_end_ts_str'";
    if(isset($where))$where = "$where";else $where = "";

?>
<style type="text/css">
td.official{
    background-color:rgba(128,0,0,1);
    color:white !important;
}
td.internal{
    background-color:green;
    color:yellow !important;
}
td.onc:hover{
    background-color:rgba(0,0,128,1);
    color:white;
    cursor:pointer;
}
td:hover{
    cursor:not-allowed;
}
a.btn.active{
    background-color: maroon;
}
</style>
    <a id="official" class="btn half mid fr" onclick="select_state_btn(this)">انتخاب روزهای تعطیل رسمی</a>
    <a id="internal" class="btn half mid fl" onclick="select_state_btn(this)">انتخاب روزهای تعطیل کارگاه</a>
    <br><br>
    <form class="" action="<?php echo $URL; ?>" method="post">
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        هفته
                    </th>
                    <th>
                        شنبه
                    </th>
                    <th>
                        یک‌شنبه
                    </th>
                    <th>
                        دوشنبه
                    </th>
                    <th>
                        سه‌شنبه
                    </th>
                    <th>
                        چهار‌شنبه
                    </th>
                    <th>
                        پنج‌شنبه
                    </th>
                    <th>
                        جمعه
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>

<?php
    $week_names = ["اول","دوم","سوم","چهارم","پنجم","ششم","هفتم"];
    $month_end = monthNoDaysJ($month,$year);
    
    $week = 0;
    echo "<td>".$week_names[$week]."</td>";
    $first_day = CaSDate(create_ts($year,$month,1,12))["day_name"];
    $first_dof = dayoWeekJ($first_day);
    for($i=0;$i<$first_dof;$i++){
        echo "<td></td>";
    }
    
    for($i=1;$i<=$month_end;$i++){
        $sql_time = createSQLTime($year,$month,$i,12);
        $day_state = getVarFromDB($tb,"state","date",$sql_time);
        $day = CaSDate(create_ts($year,$month,$i,12))["day_name"];
        $value = "";
        if($day_state == "internal"){
            $value = "internal";
        }
        if($day_state == "official" || $day =="جمعه"){
            $value = "official";
        }
        $onc = "";
        $cursor_class="cancel";
        if($day != "جمعه"){
            $onc=" onclick='select_date(this)' ";
            $cursor_class = "onc";
        }
        $input=" <input name='day[$i]' class='hide' value='$value' type='text'>";
        echo "
            <td class='$value $cursor_class' $onc>$i $input</td>
            ";
        if($day == "جمعه"){
            $week++;
            echo "
                </tr><tr>
            ";
            echo "<td>".$week_names[$week]."</td>";    
        }
        
    }
    $first_dof = dayoWeekJ($day);
    for($i=$first_dof;$i<6;$i++){
        echo "<td></td>";
    }
    
?>
            </tbody>
        </table>
        <div class="cut"></div>
        <input class="btn" type="submit" name="edit" value="تایید">
    </form>
<script type="text/javascript">
var se_value = "";
function select_state_btn(item){
    official = document.getElementById('official');
    internal = document.getElementById('internal');
    if(item.id == "official"){
        if(internal.classList.contains('active')){
            toggle_state_btn(internal);
        }
    }else{
        if(official.classList.contains('active')){
            toggle_state_btn(official);
        }
    }
    toggle_state_btn(item);
}
function toggle_state_btn(item){
    if(item.classList.contains('active')){
        se_value = "";
        item.classList.remove('active');
    }else{
        se_value = item.id;
        item.classList.add('active');
    }
}
function select_date(item){
    input = item.getElementsByTagName('input')[0];
    if(input.value == se_value){
        input.value = "";
        item.classList.remove('official');    
        item.classList.remove('internal');
    }else{
        if(se_value != ""){
            if(item.getElementsByTagName('input')[0].value == "official" && se_value == "internal"){}
            else{
                item.getElementsByTagName('input')[0].value = se_value;
                item.classList.remove('official');    
                item.classList.remove('internal');    
                item.classList.add(se_value);
            }
        }
    }
    return;    
}
</script>
<?php
        }
    }
?>