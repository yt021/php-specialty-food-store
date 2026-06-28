<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where("admins");
    $tb = $_SESSION[$cf]->section;
    $title = "مدیریت حساب";
    if(isset($_POST["clear"]) && $_POST["clear"]=='0')
    {
        $_SESSION[$cf] = new where("admins");
    }
    if(isset($_POST["clear"]) && $_POST["clear"]=='1')
    {
        $_SESSION[$cf]->level = 1;
        $_SESSION[$cf]->id = null;
    }
    if(isset($_POST["sub"]) && $_POST["sub"]=='state')
    {
        $state = getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state);
        updateInDB($tb,"state",$state,"id",$_SESSION[$cf]->id);
        if($_SESSION[$cf]->state == "new"){
            $st = "SELECT CURRENT_TIMESTAMP;";
            $st = $mysqli->prepare($st);
            $st->execute();
            $st->store_result();
            $st->bind_result($value);
            $st->fetch();
            updateInDB($tb,"payment_date",$value,"id",$_SESSION[$cf]->id);
        }
        $_SESSION[$cf]->level = 1;
        $_SESSION[$cf]->id = null;
    }
    if($_SESSION[$cf]->level == 0){
        if(isset($_POST["edit"]) && $_POST["edit"] == "pass"){
            $_SESSION[$cf]->level = 1;
        }
    }
    
    if($_SESSION[$cf]->level == 1){
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title"."</a> » تغییر گذرواژه";
        if(isset($_POST["submit_edit"])){
            if(isset($_POST["new_pass"]) && isset($_POST["cnf_new_pass"]) && $_POST["new_pass"] === $_POST["cnf_new_pass"] && isset($_POST["old_pass"]) && $_POST["new_pass"]){
                if(password_verify($_POST["old_pass"],getVarFromDB($tb,"passhash","id",$_SESSION["a_logged"]->uid))){
//                updateInDB($tb,"password",$_POST["cnf_new_pass"],"id",$_SESSION["a_logged"]->uid);
                updateInDB($tb,"passhash",password_hash($_POST["cnf_new_pass"],PASSWORD_DEFAULT),"id",$_SESSION["a_logged"]->uid);
                $_SESSION[$cf]->level = 0;
                }
            }
        }
    }
 ?>

<div id="main" class="middle ls_main">
    <div id="sect1" class="sect">
        <div class="middle container">
            <div class="title">
                <?php
                    echo $title;
                ?>
            </div>
        </div>
    </div>

    <div class="cut w100p"></div>

    <div id="sect2" class="sect ">
        <div class="middle container">
        <?php
            include $bu."$module_name/$cf/".$_SESSION[$cf]->level.".php";
        ?>
        </div>  
    </div>
</div>

<?php
        }
    }
?>