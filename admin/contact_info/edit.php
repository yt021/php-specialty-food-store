<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
if(isset($_POST["edit"])){
    if($_SESSION[$cf]->id == "new"){
        if(isset($_POST["name"]) && check_value("text",$_POST["name"]) 
        && isset($_POST["value"]) && check_value("text",$_POST["value"])){
            $name = check_value("text",$_POST["name"]);
            $value = check_value("text",$_POST["value"]);
            $st = "INSERT INTO $tb (name,value) VALUES (?,?)";
            $st = $mysqli->prepare($st);
            $st->bind_param('ss',$name,$value);
            $st->execute();
            
            $_SESSION[$cf]->level = 0;
            $_SESSION[$cf]->id = null;
            if(isset($_POST["link"]) && check_value("text",$_POST["link"])){
                updateInDB($tb,"link",check_value("text",$_POST["link"]),"id",last_id($tb));
            }
        }
    }else{
        $fields = ["name","value","link"];
        foreach($fields as $field){
            if(isset($_POST[$field]) && check_value("text",$_POST[$field])){
                updateInDB($tb,$field,check_value("text",$_POST[$field]),"id",$_SESSION[$cf]->id);
            }
        }
    }
}
?>

<?php
        }
    }
?>