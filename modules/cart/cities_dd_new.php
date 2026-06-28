<?php
    $indexed = 1;
    include_once($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include_once $bu."modules/cart/cart_funcs.php";
?>
<?php
    $county = "شهر تهران";
    if(isset($_POST["county"]) && var_exist($_POST["county"],"cities","county")){
        $county = $_POST["county"];
    }
    $cities_str = getVarFromDB("cities","cities_str","county",$county);
    echo $cities_str;
?>
