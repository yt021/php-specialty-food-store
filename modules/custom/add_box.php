<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
$result = null;
if(isset($_POST["box"]) && var_exist($_POST["box"],"products","id") && getVarFromDB("products","type","id",$_POST["box"]) == "box"){
    $_SESSION["custom"] = new order_box($_POST["box"],getVarFromDB("products_price","weight","pid",$_POST["box"]));
    $result = "ok";
}


?>
<?php
        }
    }
?>
