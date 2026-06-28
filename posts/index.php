<?php 
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
//    if($file_name == "index"){
//        header("Location:$s");
//    }
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
    if($file_name == "index")include "posts.php";
    else{
?>
<main>
    <div class="content">
        <?php
            echo getVarFromDB("posts","content","name","$file_name");
        ?>
    </div>
</main>

<?php
    }
?>

<?php  include $bu."modules/main/footer.php"; ?>

</body>
</html>