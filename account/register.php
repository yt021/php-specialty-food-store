<?php 
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include_once $bu."modules/admin/funcs.php";
    include $bu."modules/cart/session_start.php";
       
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
    if(isset($result) && $result == "ok"){
        include($bu."modules/cart/add_user_new.php");
    }
    if(isset($result) && $result == "ok"){
        $data = $_SESSION["user"]->data();
        $uid = db_new_user($data);
        $_SESSION["logged"] = new logged($uid,$data);
    }
    if(!isset($_SESSION["logged"])){
        include($bu."account/register_main.php");
    }
?>
<?php
    if(isset($_SESSION["logged"]))
        include($bu."account/register_success.php");
?>

<?php  include $bu."modules/main/footer.php"; ?>

</body>
</html>