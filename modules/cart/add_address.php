<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

{    
    $fields = ["county","city","address","post_code","rec_name","rec_tel","rec_tel_2"];
    foreach($fields as $f){
        if(isset($_POST[$f]))$data[$f]=$_POST[$f];
        else if(isset($_SESSION["address"]))$data[$f]=$_SESSION["address"]->data()[$f];
        else $data[$f]=null;
    }
    if(isset($_POST["janitor"]))$data["janitor"] = $_POST["janitor"];
    else $data["janitor"] = null;
    $_SESSION["address"] = new address($data);
    
    if($_SESSION["address"]->data()["error"][0] == 0){
        $result = "ok";
    }   
}


?>
<?php
        }
    }
?>