<?php
    $indexed = 1;
    include_once($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include_once $bu."modules/cart/cart_funcs.php";
?>
<?php if(isset($data["address"]["city"]))echo $data["address"]["city"];else echo "انتخاب شهر"; ?>,
<?php
    $county = false;
    if(isset($_POST["county"])){
        $county = check_value("text",$_POST["county"]);
    }
    if($county == false){$county = "شهر تهران";}
    
    $cities_str = getVarFromDB("cities","cities_str","county",$county);
    $cities = get_str_index($cities_str,",")[1];
    for($c_i=0;$c_i<sizeof($cities);$c_i++){
        echo $cities[$c_i].",";
    }
?>

