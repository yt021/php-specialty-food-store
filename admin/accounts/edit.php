<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if($_SESSION[$cf]->level == 1){
        if($_SESSION[$cf]->id == "new"){
            if(isset($_POST["name"]) && check_value("text",$_POST["name"]) && isset($_POST["username"]) && check_value("text",$_POST["username"]) && !var_exist($_POST["username"],$tb,"username") && isset($_POST["new_pass"]) && isset($_POST["cnf_new_pass"]) && $_POST["new_pass"] === $_POST["cnf_new_pass"] && $_POST["new_pass"] && isset($_POST["level"])){
                $data["level"] = min((int)$_POST["level"],$_SESSION["a_logged"]->get_level(),0);
                $st = "INSERT INTO $tb (name,username,password,level) VALUES (?,?,?,?)";
                $st = $mysqli->prepare($st);
                $st->bind_param('ssss',check_value('text',$_POST["name"]),check_value('text',$_POST["username"]),$_POST["new_pass"],$data["level"]);
                $st->execute();
            }
            }else{
                if(isset($_POST["new_pass"]) && isset($_POST["cnf_new_pass"]) && $_POST["new_pass"] === $_POST["cnf_new_pass"] && $_POST["new_pass"]){
                    updateInDB($tb,"password",$_POST["cnf_new_pass"],"id",$_SESSION[$cf]->id);
                }
                if(isset($_POST["name"]) && check_value("text",$_POST["name"]))
                updateInDB($tb,"name",check_value("text",$_POST["name"]),"id",$_SESSION[$cf]->id);
                if(isset($_POST["username"]) && check_value("text",$_POST["username"]) && !var_exist($_POST["username"],$tb,"username"))
                updateInDB($tb,"username",check_value("text",$_POST["username"]),"id",$_SESSION[$cf]->id);
                if(isset($_POST["level"]))
                updateInDB($tb,"level",$_POST["level"],"id",$_SESSION[$cf]->id);
            }
            $_SESSION[$cf]->level = 0;
    }else if($_SESSION[$cf]->level == 2){
        updateInDB($tb,"access_str",check_value("text",$_POST["edit"]),"id",$_SESSION[$cf]->id);
        $_SESSION[$cf]->level = 1;
    }
?>
<?php
        }
    }
?>