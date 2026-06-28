<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
$result = null;
if(isset($_SESSION["custom"]) && is_a($_SESSION["custom"],"order_box"))
if($_SESSION["custom"]->content->number() == $_SESSION["custom"]->capacity){
    $result = "ok";
}else{
    $_SESSION["code_error"]="E"."هنوز جعبه پر نشده است، با افزودن میوه های بیشتر، جعبه را پر کنید و پس از پر شدن آن، دکمه تأیید را فشار دهید.";
}


?>
<?php
        }
    }
?>
