<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where($cf);
    
    if(isset($_SESSION[$cf]->state)){
        $tb = $_SESSION[$cf]->state;
    }
    $atb = "admin_$cf"."_state";
    
    $title_b = getVarFromDB("admin_modules","name","flag",$cf);

    if(isset($_POST["edit"]) || isset($_POST["edit_img"])){
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
        }
        
    }
    
    if(isset($_SESSION[$cf]->state)){
        $tb = $_SESSION[$cf]->state;
    }
    
    if($_SESSION[$cf]->level == 0){
        $title = $title_b;
        if(isset($_POST["state"]) && var_exist($_POST["state"],$atb,"flag")){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->state = $_POST["state"];
            $tb = $_SESSION[$cf]->state;
        }
    }
   
    
    if($_SESSION[$cf]->level == 1){
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b"."</a> » ".getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state);
        
        if(isset($_POST["order"])){
            $_SESSION[$cf]->order_by->add_item($_POST["order"]);
        }
        
        if(isset($_POST["id"]) && (var_exist($_POST["id"],$tb,"id") || $_POST["id"] == "new")){
            $_SESSION[$cf]->level = 2;
            $_SESSION[$cf]->id = $_POST["id"];
        }
    }
    if($_SESSION[$cf]->level == 2){
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'.getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state).'</a> » مشاهده جزئیات';
        if(isset($_POST["pic"])){
            $_SESSION[$cf]->level = 3;
            $_SESSION[$cf]->sub_id = $_POST["pic"];
        }
    }
    if($_SESSION[$cf]->level == 3){
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'.getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state).'</a> » <a class="curpo" onclick="sub_show('."'clear','2'".')">مشاهده جزئیات</a> » انتخاب تصویر';
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