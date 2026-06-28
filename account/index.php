<?php 
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include_once $bu."modules/admin/funcs.php";
    include $bu."modules/cart/session_start.php";
    if(isset($_POST["edit"]) && $_SESSION[$cf]->level == 1 && $_SESSION[$cf]->state == "manage"){
        include $bu."account/".$_SESSION[$cf]->state."_edit.php";
    }   
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" class="" lang="en">
<?php
    include $bu."modules/main/head.php";
?>

<body>

<?php  include $bu."modules/main/header.php"; ?>

<?php
    if(!isset($_SESSION["logged"])){
        include($bu."modules/cart/rec_code.php");
    }
    if(!isset($_SESSION["logged"])){
        include($bu."modules/account/login.php");
    }
?>
<?php
    if(isset($_SESSION["logged"]))
        include "main.php";
?>

<?php  include $bu."modules/main/footer.php"; ?>

</body>
</html>