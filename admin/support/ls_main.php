<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where("admins");
    $tb = $_SESSION[$cf]->section;
    $title_b = "مدیریت کاربران";
    if(isset($_POST["clear"])){
        switch($_POST["clear"]){
            case '0':
                $_SESSION[$cf] = new where("admins");
                break;
            case '1':
                $_SESSION[$cf]->level = 1;
                $_SESSION[$cf]->id = null;
                break;
        }
    }
    if(isset($_POST["edit"])){
        include $bu."$module_name/$cf/edit.php";
    }   
    
    if($_SESSION[$cf]->level == 0){
        $title = $title_b;
        if(isset($_POST["uid"]) && var_exist($_POST["uid"],$tb,"id") && (int)getVarFromDB($tb,"level","id",$_POST["uid"])< $_SESSION["a_logged"]->get_level()){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = $_POST["uid"];
        }
        if(isset($_POST["new_admin"])){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = "new";
        }
    }
    
    if($_SESSION[$cf]->level == 1){
        if($_SESSION[$cf]->id == "new")$username="کاربر جدید";else
        $username = getVarFromDB($tb,"username","id",$_SESSION[$cf]->id);
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b"."</a> » $username";
        
        if(isset($_POST["access"]) && $_POST["access"] == "set"){
            $_SESSION[$cf]->level = 2;
        }
        
    }
    if($_SESSION[$cf]->level == 2){
        $username = getVarFromDB($tb,"username","id",$_SESSION[$cf]->id);
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'.$username."</a> » تنظیم دسترسی";
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