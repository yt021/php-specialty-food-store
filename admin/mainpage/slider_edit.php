<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
if(isset($_POST["edit"]) && $_SESSION[$cf]->level ==  2){
    if($_SESSION[$cf]->id == "new"){
        if(isset($_POST["title"]) && check_value("text",$_POST["title"]) 
        && isset($_POST["detail"]) && check_value("text",$_POST["detail"])){
            $title = check_value("text",$_POST["title"]);
            $detail = check_value("text",$_POST["detail"]);
            $st = "INSERT INTO $tb (title,detail) VALUES (?,?)";
            $st = $mysqli->prepare($st);
            $st->bind_param('ss',$title,$detail);
            $st->execute();
            
            $id = last_id($tb);
            $_SESSION[$cf]->id = $id;
            if(isset($_POST["link"]) && check_value("text",$_POST["link"])){
                updateInDB($tb,"link",check_value("text",$_POST["link"]),"id",$_SESSION[$cf]->id);
            }
        }
    }else{
        $fields = ["title","detail","link"];
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