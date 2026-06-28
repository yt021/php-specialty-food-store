<?php
    if(isset($indexed) && $indexed){
?>
<?php
for($i=1;$i<=31;$i++){
    $selected = "";
    if($_SESSION[$cf]->sql_where[$date_name]["day"] == $i)$selected=" selected ";
    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
}
?>
<?php
    }
?>