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
                    if(isset($_SESSION[$cf]->sql_where["start_date"]["day"]) && $_SESSION[$cf]->sql_where["start_date"]["day"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <label for="month">ماه:</label> 
        <select name="month">
            <?php
                for($i=1;$i<=12;$i++){
                    $selected = "";
                    if(isset($_SESSION[$cf]->sql_where["start_date"]["month"]) && $_SESSION[$cf]->sql_where["start_date"]["month"] == $i)$selected=" selected ";
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
                    if(isset($_SESSION[$cf]->sql_where["start_date"]["year"]) && $_SESSION[$cf]->sql_where["start_date"]["year"] == $i)$selected=" selected ";
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
                    if(isset($_SESSION[$cf]->sql_where["end_date"]["day"]) && $_SESSION[$cf]->sql_where["end_date"]["day"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <label for="month">ماه:</label> 
        <select name="month">
            <?php
                for($i=1;$i<=12;$i++){
                    $selected = "";
                    if(isset($_SESSION[$cf]->sql_where["end_date"]["month"]) && $_SESSION[$cf]->sql_where["end_date"]["month"] == $i)$selected=" selected ";
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
                    if(isset($_SESSION[$cf]->sql_where["end_date"]["year"]) && $_SESSION[$cf]->sql_where["end_date"]["year"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <input type="submit" name="end_date" value="تایید">
    </form>
<?php
    $sq_date["start"]="";$sq_date["end"]="";
    if(isset($_SESSION[$cf]->sql_where["start_date"])){
        $sq_date["start"] = $_SESSION[$cf]->sql_where["start_date"];
    }
    if(isset($_SESSION[$cf]->sql_where["end_date"])){
        $sq_date["end"] = $_SESSION[$cf]->sql_where["end_date"];
    }
    $where = db_time_condition($column_name,$sq_date["start"],$sq_date["end"],$where_and);
?>
<?php
        }
    }
?>