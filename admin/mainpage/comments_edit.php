<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

if($_SESSION[$cf]->level ==  2 && isset($_POST["reply"])){
    updateInDB("comments","reply",check_value("text",$_POST["reply"]),"id",$_SESSION[$cf]->id);
    if($_POST["reply"])
    updateInDB("comments","reply_date",date("Y-m-d H:i:s"),"id",$_SESSION[$cf]->id);
}
?>

<?php
        }
    }
?>