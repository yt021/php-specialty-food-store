<?php
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/admin/session_start.php";
?>

<html>

<?php
    include $bu."modules/admin/head.php";
?>

<body onscroll="" onload="">
    <?php
        if(!isset($_SESSION["a_logged"])){
            include($bu."modules/admin/admin_login.php");
        }
    ?>
    <!--Right Side Bar-->
    
    <?php
    if(isset($_SESSION["a_logged"])){
        include($bu."modules/admin/rsm.php");
    ?>
    <!--Main Div-->
    <?php
        include($bu."admin/$cf/ls_main.php");
    }
    ?>
</body>

<script type="text/javascript">

</script>
</html>
