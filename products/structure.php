<?php 
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    if(isset($_GET['filename'])){
        $file_name = $_GET['filename'];
    }
    
    if($file_name == "index" || $file_name == "main"){
        header("Location:$s");
        die;
    }
    include $bu."modules/cart/session_start.php";   
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" class="" lang="en">
<?php
    include $bu."modules/main/head.php";
?>

<body>
<?php  include $bu."modules/main/header.php"; ?>

<?php  include "main.php"; ?>

<?php  include $bu."modules/main/footer.php"; ?>

</body>
</html>