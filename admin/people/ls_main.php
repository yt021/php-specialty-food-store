<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where($cf);
    $tb = $_SESSION[$cf]->section;
    $atb = "admin_$tb"."_state";
    $title_b = getVarFromDB("admin_modules","name","flag",$cf);
    if(isset($_POST["edit"])){
        include $bu."$module_name/$cf/".$_SESSION[$cf]->state."_edit.php";
    }    
    
    if(isset($_POST["clear"]))
    {
        switch($_POST["clear"]){
            case '0':
                $_SESSION[$cf] = new where($cf);
                break;
            case '1':
                $_SESSION[$cf]->level = 1;
                $_SESSION[$cf]->id = null;
                break;
            case '2':
                $_SESSION[$cf]->level = 2;
                $_SESSION[$cf]->sub_id = null;
                break;
        }
        
    }
    if($_SESSION[$cf]->level == 0){
        $title = $title_b;
        if(isset($_POST["state"]) && var_exist($_POST["state"],$atb,"flag")){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->state = $_POST["state"];
        }
    }
    if($_SESSION[$cf]->level == 1){
        $tb = $_SESSION[$cf]->state;
        $sub_title = getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state);
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b"."</a> » ".$sub_title;
        
        if(isset($_POST["id"]) && (var_exist($_POST["id"],$tb,"id") || $_POST["id"] == "new")){
            $_SESSION[$cf]->level = 2;
            $_SESSION[$cf]->id = $_POST["id"];
        }
        
        if(isset($_POST["order"])){
            $_SESSION[$cf]->order_by->add_item($_POST["order"]);
        }
        if(isset($_POST["date"])){
            $_SESSION[$cf]->sql_where["date"]["month"] = $_POST["month"];
            $_SESSION[$cf]->sql_where["date"]["year"] = $_POST["year"];
        }
        if($tb == "send_date"){
            if(!isset($_SESSION[$cf]->sql_where["date"])){
                $_SESSION[$cf]->sql_where["date"]["month"] = CaSDate(time())["month"];
                $_SESSION[$cf]->sql_where["date"]["year"] = CaSDate(time())["year"];
            }
        }
    }
    if($_SESSION[$cf]->level == 2){
        $tb = $_SESSION[$cf]->state;
        if($_SESSION[$cf]->id == "new"){
            switch($_SESSION[$cf]->state){
                case "admins":
                    $sub_title = "کاربر";
                    break;
                case "employees":
                    $sub_title = "کارکن";
                    break;
                case "suppliers":
                    $sub_title = "تامین‌کننده";
                    break;
                case "transporters":
                    $sub_title = "ارسال‌کننده";
                    break;
                
            }
            $sub_title = "$sub_title جدید";
        }
        else $sub_title = getVarFromDB($tb,"name","id",$_SESSION[$cf]->id);

        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'
        .getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state).'</a> » '.$sub_title;
        if(isset($_POST["access"]) && $_POST["access"] == "set"){
            $_SESSION[$cf]->level = 3;
        }
    }
    
    if($_SESSION[$cf]->level == 3){
        $tb = $_SESSION[$cf]->state;
        $username = getVarFromDB($tb,"name","id",$_SESSION[$cf]->id);
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'.getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state).'</a> » <a class="curpo" onclick="sub_show('."'clear','2'".')">'.$username."</a> » تنظیم دسترسی";
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
            include $bu."$module_name/$cf/".$_SESSION[$cf]->state.$_SESSION[$cf]->level.".php";
        ?>
        </div>  
    </div>
</div>
<?php
        }
    }
?>