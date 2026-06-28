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
    $fields[1] = ["hire_date","fire_date"];
    $fields[2] = ["day","month","year"];
    foreach ($fields[1] as $f1){
        foreach ($fields[2] as $f2){
            if(isset($_POST[$f1][$f2]) && check_value("text",$_POST[$f1][$f2])){}
        else{$error = false;}
        }
        if($_POST[$f1]["year"] == 1397 && $_POST[$f1]["month"] == 1 && $_POST[$f1]["day"] == 1)
        $data[$f1] = NULL;
        else{$data[$f1] = cSQLTimefS($_POST[$f1]);}
    }
    if($error){
        $name = $_POST["name"];
        if($_SESSION[$cf]->id == "new" && !var_exist($_POST["name"],$tb,"name")){
            $st = "INSERT INTO $tb (name) VALUES (?)";
            $st = $mysqli->prepare($st);
            $st->bind_param('s',$name);
            $st->execute();
            $id = last_id($tb);
            foreach ($fields[1] as $f1){
                updateInDB($tb,$f1,$data[$f1],"id",$id);
            }
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = null;
        }else{
            $fields = ["name","hire_date","fire_date"];
            $data["name"] = $_POST["name"];
            foreach ($fields as $f){
                updateInDB($tb,$f,$data[$f],"id",$_SESSION[$cf]->id);
            }
        }
    }
}
?>

<?php
        }
    }
?>