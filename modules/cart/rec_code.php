<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
if(isset($_SESSION["a_logged"]) && isset($_POST["tel"])){
    if(!($tel = check_value("tel",$_POST["tel"]))){
        $_SESSION["cart_notice"] = "Eشماره تلفن را به صورت صحیح وارد نمایید.";
    }else{
        if(var_exist($tel,"users","tel")){
            $code = getVarFromDB("users","password","tel",$tel);
        }else{
            $code = generate_code();
        }
        if(db_new_user_attempt($tel,$code,1)){
            if(var_exist($tel,"users","tel")){
                $uid = getVarFromDB("users","id","tel",$tel);
                $_SESSION["user"] = db_get_user($uid);
                $dummy["create_code"] = $code;
                $_SESSION["logged"] = new logged($uid,$dummy);
                if(isset($cfs) && isset($_SESSION[$cfs])){$_SESSION[$cfs]->level++;}
                $result = "ok";
            }else{
                $_SESSION["tel"] = $tel;
                $_SESSION["create_code"] = $code;
                $result = "ok";
            }
        }
    }
}else{
    if(isset($_POST["tel"]) && isset($_POST["create_code"])){
        $tel = check_value("tel",$_POST["tel"]);
        $code = check_value("text",$_POST["create_code"]);
        if($tel == $_SESSION["tel"]){
            $upa_id = getVarFromDB("users_password_attempt","id","tel",$tel,"id DESC");
            
            if(strtotime(getVarFromDB("users_password_attempt","create_date","id",$upa_id))<(db_time_now() - 180)){
                $_SESSION["cart_notice"] = "Aبازه زمانی طولانی شده است. مجددا اقدام نمایید.";
                $result="";
            }else{
                if((int)getVarFromDB("users_password_attempt","state","id",$upa_id) === 1){
                    $_SESSION["cart_notice"] = "Eکد تایید وارد شده منقضی شده است. مجددا اقدام نمایید.";
                }else{
                    if($code == getVarFromDB("users_password_attempt","code","id",$upa_id)){
                        
                        updateInDB("users_password_attempt","state",1,"id",$upa_id,"id DESC");
                        
                        if(var_exist($tel,"users","tel")){
                            $uid = getVarFromDB("users","id","tel",$tel);
                            $_SESSION["user"] = db_get_user($uid);
                            $dummy["create_code"] = $code;
                            $_SESSION["logged"] = new logged($uid,$dummy);
                            updateInDB("users","last_login",date("Y-m-d H:i:s"),"id",$uid);
                            if(isset($cfs) && isset($_SESSION[$cfs])){$_SESSION[$cfs]->level++;}
                            $result = "ok";
                        }else{
                            $_SESSION["tel"] = $tel;
                            $_SESSION["create_code"] = $code;
                            $result = "ok";
                        }
                    }else{
                        if(var_exist($tel,"users","tel")){
                            $_SESSION["cart_notice"] = "Eگذرواژه وارد شده اشتباه است. مجددا اقدام نمایید.";
                        }else{
                            $_SESSION["cart_notice"] = "Eکد تایید وارد شده اشتباه است. مجددا اقدام نمایید.";
                        }
                    }
                }
            }
        }
    }
}
?>
<?php
        }
    }
?>
