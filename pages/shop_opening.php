<?php 
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    if($file_name == "index"){
        header("Location:$s");
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

<main>
    <div class="content">
        <?php
            echo getVarFromDB("pages","content","name","$file_name");
        ?>
    </div>
    <div class="content">
         <video style="width:80%;margin:auto;display:block;" controls>
              <source src="<?php echo $s; ?>content/shop_opening.mp4" type="video/mp4">
            Your browser does not support the video tag.
            </video> 
        
    </div>
</main>



<?php  include $bu."modules/main/footer.php"; ?>

</body>
</html>