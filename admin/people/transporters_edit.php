<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
if(isset($_POST["edit"])){
    $tb = $_SESSION[$cf]->state;
    $fields = ["name"];
    $error = true;
    foreach($fields as $f){
        if(isset($_POST[$f]) && check_value("text",$_POST[$f])){}
        else{$error = false;}
    }
    
    if($error){
        $name = $_POST["name"];
        $show_title = $_POST["show_title"];
        $detail = $_POST["detail"];
        
        if($_SESSION[$cf]->id == "new" && !var_exist($_POST["name"],$tb,"name")){
            $st = "INSERT INTO $tb (name,show_title,detail) VALUES (?,?,?)";
            $st = $mysqli->prepare($st);
            $st->bind_param('sss',$name,$show_title,$detail);
            $st->execute();

            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = null;
        }else{
            $fields = ["name","show_title","detail"];
            foreach ($fields as $f){
                updateInDB($tb,$f,$_POST[$f],"id",$_SESSION[$cf]->id);
            }
        }
    }
}
?>

<?php
        }
    }
?>