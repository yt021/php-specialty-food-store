<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
        if(isset($_POST["edit"])){
            if(isset($_POST["email"])){
                updateInDB("users","email",check_value("text",$_POST["email"]),"id",$_SESSION["logged"]->uid);
                $_SESSION[$cf]->level = 1;
                $_SESSION["user"] = db_get_user($_SESSION["logged"]->uid);
            }
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)){
                $ipAddress = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
            }
            $browser = $_SERVER['HTTP_USER_AGENT'];
            if(
                isset($_POST["new_pass"]) && 
                isset($_POST["cnf_new_pass"]) && 
                $_POST["new_pass"] === $_POST["cnf_new_pass"] && 
                isset($_POST["old_pass"]) && 
                check_value("text",$_POST["old_pass"]) == getVarFromDB("users","password","id",$_SESSION["logged"]->uid) && 
                $_POST["new_pass"] &&
                $ipAddress === getVarFromDB("users_password_attempt","ip","tel",$_SESSION["user"]->data()["tel"],"id DESC") &&
                $browser === getVarFromDB("users_password_attempt","browser","tel",$_SESSION["user"]->data()["tel"],"id DESC") 
            ){
                updateInDB("users","password",check_value("text",$_POST["cnf_new_pass"]),"id",$_SESSION["logged"]->uid);
                
            }
            
        }

?>
<?php
        }
    }
?>