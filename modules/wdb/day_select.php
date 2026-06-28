<?php
if(isset($indexed)){
    if($indexed == 1){?>
    <form class="date" action="<?php echo $URL; ?>" method="post">
    انتخاب تاریخ
        <label for="day">روز:</label> 
        <select name="day">
            <?php
                for($i=1;$i<=31;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where[$date_name]["day"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <label for="month">ماه:</label> 
        <select name="month">
            <?php
                for($i=1;$i<=12;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where[$date_name]["month"] == $i)$selected=" selected ";
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
                    if($_SESSION[$cf]->sql_where[$date_name]["year"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <input type="submit" name="<?php echo $date_name; ?>" value="تایید">
    </form>
    <div class="cut w100p"></div>
<?php
    $sq_date["start"] = "";
    $sq_date["end"] = "";
    if(isset($_SESSION[$cf]->sql_where[$date_name])){
        $sq_date["start"] = $_SESSION[$cf]->sql_where[$date_name];
        $sq_date["end"] = CaSDate(strtotime(cSQLTimefS($sq_date["start"]))+25*3600);
    }
    $where = db_time_condition($column_name,$sq_date["start"],$sq_date["end"],$where_and);
?>
<?php
        }
    }
?>