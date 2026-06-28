<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
if(isset($_POST["edit"]) && $_SESSION[$cf]->level ==  2){
    if($_SESSION[$cf]->id == "new"){
        if(isset($_POST["data"]) && check_value("text",$_POST["data"])){
            $data = check_value("text",$_POST["data"]);
            $st = "INSERT INTO $tb (data) VALUES (?)";
            $st = $mysqli->prepare($st);
            $st->bind_param('s',$data);
            $st->execute();
            
            $id = last_id($tb);
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = null;
        }
    }else{
        $fields = ["data"];
        foreach($fields as $field){
            if(isset($_POST[$field]) && check_value("text",$_POST[$field])){
                updateInDB($tb,$field,check_value("text",$_POST[$field]),"id",$_SESSION[$cf]->id);
            }
        }
    }
}
if(isset($_POST["edit_img"]) && $_SESSION[$cf]->level ==  3){
    if($_SESSION[$cf]->sub_id ==  "single"){
        if(var_exist($_POST["edit_img"],"content","id"))
        updateInDB($tb,"image_id",$_POST["edit_img"],"id",$_SESSION[$cf]->id);
        $_SESSION[$cf]->level = 2;
    }
}
?>

<?php
        }
    }
?>