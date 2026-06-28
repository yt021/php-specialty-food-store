<?php
if(isset($indexed)){
    if($indexed == 1){?>
    
<?php
    $period_options = array(
        "today"=>"امروز",
        "last_month"=>"ماه جاری",
        "last_30_days"=>"سی روز گذشته",
        "last_90_days"=>"سه ماه گذشته",
        "last_6_month"=>"شش ماه گذشته",
        "last_year"=>"سال جاری",
        "period"=>"بازه انتخابی"
    );
    
    
    $hide = " hide ";
    if($_SESSION[$cf]->sql_where["period_option"] == "period")$hide = "";
?>
    <form class="date" action="<?php echo $URL; ?>" method="post">
        <div class="box_holder">
            <label class="fr" for="type">بازه زمانی: </label>
            <?php
                foreach($period_options as $key=>$f){
                    $checked = "";
                    if($_SESSION[$cf]->sql_where["period_option"] == $key)$checked = " checked ";
                    echo '
                        <div class="radio options_item'.$checked.'" onclick="option_radio(this);show_select_serie(option_value(this),'."'period_select'".');">
                            '.$f.'<input class="hide" type="radio" name="period_option" '.$checked.' value="'.$key.'">'.'
                            <div class="check_box">
                                <div class="check"></div>
                            </div>
                        </div>
                    ';
                }
            ?>
        </div>
    
    <div class="<?php echo $hide; ?>" id="period_select">
    <div class="cb cut <?php echo ""; ?>"></div>
    <div class="field">
    شروع:

        <label for="day">روز:</label> 
        <select name="date_period[start][day]">
            <?php
                for($i=1;$i<=31;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where["start_date"]["day"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <label for="month">ماه:</label> 
        <select name="date_period[start][month]">
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
        <select name="date_period[start][year]">
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
    </div>
    <div class="field">
    پایان:
        <label for="day">روز:</label> 
        <select name="date_period[end][day]">
            <?php
                for($i=1;$i<=31;$i++){
                    $selected = "";
                    if($_SESSION[$cf]->sql_where["end_date"]["day"] == $i)$selected=" selected ";
                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                }
            ?>
        </select>
        <label for="month">ماه:</label> 
        <select name="date_period[end][month]">
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
        <select name="date_period[end][year]">
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
    </div>
    </div>
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
<script src="<?php echo asset_url('js/select_options.js'); ?>" type="text/javascript"></script>
<?php
        }
    }
?>
