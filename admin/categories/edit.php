<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if($_SESSION[$cf]->level == 1){
        if($_SESSION[$cf]->id == "new"){
            if(isset($_POST["name"]) && check_value('text',$_POST["name"]) && isset($_POST["section"]) && check_value('text',$_POST["section"])){
                $name = check_value('text',$_POST["name"]);
                $section = check_value('text',$_POST["section"]);
                $show_order = 0;
                if(isset($_POST["show_order"]))
                $show_order = (int)check_value('text',$_POST["show_order"]);
                $st = "INSERT INTO $tb (name,section,show_order) VALUES (?,?,?)";
                $st = $mysqli->prepare($st);
                $st->bind_param('sss',$name,$section,$show_order);
                $st->execute();
                
                $_SESSION[$cf]->id = last_id($tb);
            }
        }else{
        if(isset($_POST["name"]))updateInDB($tb,"name",$_POST["name"],"id",$_SESSION[$cf]->id);
        if(isset($_POST["section"]))updateInDB($tb,"section",$_POST["section"],"id",$_SESSION[$cf]->id);
        if(isset($_POST["show_order"]))updateInDB($tb,"show_order",$_POST["show_order"],"id",$_SESSION[$cf]->id);
        }
    }
?>
<?php
        }
    }
?>
