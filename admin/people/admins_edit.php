<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $tb = $_SESSION[$cf]->state;
    if($_SESSION[$cf]->level == 2){
        if($_SESSION[$cf]->id == "new"){
            if(isset($_POST["name"]) && check_value("text",$_POST["name"]) && isset($_POST["username"]) && check_value("text",$_POST["username"]) && !var_exist($_POST["username"],$tb,"username") && isset($_POST["new_pass"]) && isset($_POST["cnf_new_pass"]) && $_POST["new_pass"] === $_POST["cnf_new_pass"] && $_POST["new_pass"] && isset($_POST["level"])){
                echo "here<br><br><br>";
                $name = check_value('text',$_POST["name"]);
                $username = check_value('text',$_POST["username"]);
                $passhash = password_hash($_POST["cnf_new_pass"],PASSWORD_DEFAULT);
                $level = min((int)$_POST["level"],$_SESSION["a_logged"]->get_level()-1);
                echo "here<br><br><br>";
//                $st = "INSERT INTO $tb (name,username,password,level) VALUES (?,?,?,?)";
                $st = "INSERT INTO $tb (name,username,passhash,level) VALUES (?,?,?,?)";
                $st = $mysqli->prepare($st);
                $st->bind_param('ssss',$name,$username,$passhash,$level);
                $st->execute();
            }
        }else{
            if(isset($_POST["new_pass"]) && isset($_POST["cnf_new_pass"]) && $_POST["new_pass"] === $_POST["cnf_new_pass"] && $_POST["new_pass"]){
                updateInDB($tb,"passhash",password_hash($_POST["cnf_new_pass"],PASSWORD_DEFAULT),"id",$_SESSION[$cf]->id);
            }
            if(isset($_POST["name"]) && check_value("text",$_POST["name"]))
            updateInDB($tb,"name",check_value("text",$_POST["name"]),"id",$_SESSION[$cf]->id);
            if(isset($_POST["username"]) && check_value("text",$_POST["username"]) && !var_exist($_POST["username"],$tb,"username"))
            updateInDB($tb,"username",check_value("text",$_POST["username"]),"id",$_SESSION[$cf]->id);
            if(isset($_POST["level"])){
                $level = min((int)$_POST["level"],$_SESSION["a_logged"]->get_level()-1);
                updateInDB($tb,"level",$level,"id",$_SESSION[$cf]->id);
            }
        }
        $_SESSION[$cf]->level = 1;
    }else if($_SESSION[$cf]->level == 3){
        updateInDB($tb,"access_str",check_value("text",$_POST["edit"]),"id",$_SESSION[$cf]->id);
        $_SESSION[$cf]->level = 2;
    }
?>
<?php
        }
    }
?>