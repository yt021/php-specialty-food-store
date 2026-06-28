<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    unset($_SESSION['shift3_excess']);
    
    $fields = ["shift","id","alt"];
    $error = 0;
    foreach($fields as $f){
        if(!isset($_POST[$f]))
        $error=1;
    }
    $result = "no";
    $_SESSION["cart_notice"] = " خطایی رخ داده است، لطفا مجددا اقدام نمایید.";
    if($error != 1){
        if($_POST["id"] == 'post' || $_POST["id"] == 'courier' || var_exist($_POST["id"],"send_date","id")){
            $_SESSION["send_date"]=new send_date($_POST["id"],$_POST["shift"],$_POST["alt"]);
            if($_SESSION["send_date"]->sdid){
                $result = "ok";
                unset($_SESSION["cart_notice"]);
            }else{
                unset($_SESSION["send_date"]);
            }
        }
    }
?>
<?php
        }
    }
?>
