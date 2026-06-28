<?php
    if(isset($indexed) && $indexed){
?>
<?php
include_once $GLOBALS['bu']."modules/jdf.php";
$year = (int)jdate("Y","","","","en");
for($i=1397;$i<=$year;$i++){
    $selected = "";
    if($_SESSION[$cf]->sql_where[$date_name]["year"] == $i)$selected=" selected ";
    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
}
?>
<?php
    }
?>