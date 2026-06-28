<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if($_SESSION[$cf]->sub_id == "content"){
?>
        <h2 class="tac">محتوا</h2><br>
<?php include $bu."modules/admin/TE_toolbars.php"; ?>
<?php
    }
?>

<?php
        }
    }
?>