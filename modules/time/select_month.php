<?php
    if(isset($indexed) && $indexed){
?>
<?php
include_once $GLOBALS['bu']."modules/jdf.php";
for($i=1;$i<=12;$i++){
    $selected = "";
    if($_SESSION[$cf]->sql_where[$date_name]["month"] == $i)$selected=" selected ";
    $timetext = jmktime(1,0,0,$i,1,1397);
    $month = jdate("F",(int)$timetext,"","","en");
    echo '<option '.$selected.' value="'.$i.'">'.$month.'</option>';
}
?>
<?php
    }
?>