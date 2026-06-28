<?php 
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/cart/session_start.php";
    
    // if(!isset($_SESSION["a_logged"])){header("Location:$s");}
    
    $cfs = "custom_page";
    if(isset($_SERVER["HTTP_REFERER"])){
        $origin =  $_SERVER["HTTP_REFERER"];
        $origin = substr($origin,0,strripos($origin,"/"));
        $origin = substr($origin,strripos($origin,"/")+1);
        if($origin != "custom"){
            $_SESSION[$cfs] = new where_u($cfs);
        }
    }else{
        $_SESSION[$cfs] = new where_u($cfs);
    }
    if(!isset($_SESSION[$cfs]))$_SESSION[$cfs] = new where_u($cfs);
    
    if(isset($_GET,$_GET['it']) && $_GET['it'] == 'valen'){
        $_SESSION["custom_agree"] = 1;
        $_SESSION["custom"] = new order_box(76,getVarFromDB("products_price","weight","pid",76));
        $_SESSION[$cfs]->level = 2;
    }
//  Level 0: Agreement
    if($_SESSION[$cfs]->level > 0)
        if(isset($_SESSION["custom_agree"])){}else{
            $_SESSION[$cfs]->level = 0;
            header("Location:$s"."custom/");
            die;
    }else{
        if(isset($_SESSION["custom_agree"])){
            $_SESSION[$cfs]->level = 2;
        }
    }
//  Level 1: Box Selection
    if($_SESSION[$cfs]->level > 1)
        if(isset($_SESSION["custom"])){}else{
            $_SESSION[$cfs]->level = 1;
            header("Location:$s"."custom/");
            die;
    }
//  Level 2: Fruit Selection
    if($_SESSION[$cfs]->level > 2)
        if(isset($_SESSION["custom"]->content) && $_SESSION["custom"]->content->number() == $_SESSION["custom"]->content->capacity){}else{
            $_SESSION[$cfs]->level = 2;
            header("Location:$s"."custom/");
            die;
    }
//  Level 3: Submit Receipt
//  Level 4: Add to Cart + Options

    if(isset($_POST["empty"])){
            $_SESSION["custom"]->content = new box_cart($_SESSION["custom"]->capacity);
            $_SESSION[$cfs]->level = 2;
            header("Location:$s"."custom/");
            die;
    }
    if(isset($_POST["change_content"])){
            $_SESSION[$cfs]->level = 2;
            header("Location:$s"."custom/");
            die;
    }
    if(isset($_POST["change_box"])){
        unset($_SESSION["custom"]);
        $_SESSION[$cfs]->level = 1;
        header("Location:$s"."custom/");
        die;
    }
    if(isset($_POST["submit"])){
        switch($_SESSION[$cfs]->level){
            case 0:
                $_SESSION["custom_agree"] = 1;
                $_SESSION["custom"] = new order_box(76,getVarFromDB("products_price","weight","pid",76));
                $_SESSION[$cfs]->level = 2;
                // $_SESSION[$cfs]->level++;
                break;
            case 1:
                include $bu."modules/custom/add_box.php";
                
                if($result == "ok"){
                    $_SESSION[$cfs]->level++;
                }
                break;
            case 2:
                include $bu."modules/custom/check_box.php";
                if($result == "ok"){
                    $_SESSION[$cfs]->level++;
                }
                break;
            case 3:
                include $bu."modules/custom/submit_box.php";
                if($result == "ok"){
                    $_SESSION[$cfs]->level++;
                }
                break;
        }
        header("Location:$s"."custom/");
        die;
    }else
        
    if(isset($_POST["clear"])){
        switch($_POST["clear"]){
            case '0':
//            case '1':
//            case '2':
            case '3':
//            case '4':
//            case '5':
//            case '6':
                if($_SESSION[$cfs]->level > $_POST["clear"])
                $_SESSION[$cfs]->level = $_POST["clear"];
                break;
        }
        header("Location:$s"."custom/");
        die;
    }
    else{
?>

<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" class="" lang="en">


<?php
    include $bu."modules/main/head.php";
?>

<body>

<?php  include $bu."modules/main/header.php"; ?>
<?php
       
?>

<?php
    include $bu."$module_name/".$_SESSION[$cfs]->level.".php";
?>

<?php  include $bu."modules/main/footer.php"; ?>
<script type="text/javascript">
function sub_show(key,value){
    var form = document.createElement("form");
    var input = document.createElement("input");
    
    form.method = "POST";
    form.action = "<?php echo $URL; ?>";
    
    input.value = value;
    input.name = key;
    form.appendChild(input);
    
    document.body.appendChild(form);
    form.submit();
}
</script>
</body>
</html>
<?php
    }
?>