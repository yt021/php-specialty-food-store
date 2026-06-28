<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
if($_SESSION["a_logged"]->get_level() >= 1){

    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where($cf);
    $tb = $_SESSION[$cf]->section;
    $atb = "admin_$tb"."_state";
    $p_title = "مدیریت مطالب";
    if(isset($_POST["edit"])){
        include $bu."$module_name/$cf/edit.php";
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
    if(isset($_POST["del"]) && $_POST["del"]=='post'){
        updateInDB($tb,"del_flag",1,"id",$_SESSION[$cf]->id);
        $_SESSION[$cf]->level = 1;
        $_SESSION[$cf]->id = null;
    }
    if($_SESSION[$cf]->level == 0){
        $title = $p_title;
        if(isset($_POST["state"]) && var_exist($_POST["state"],$atb,"flag")){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->state = $_POST["state"];
        }
    }
    if($_SESSION[$cf]->level == 1){
        
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$p_title"."</a> » ".getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state);
        if(isset($_POST["order"])){
            $_SESSION[$cf]->order_by->add_item($_POST["order"]);
        }
        if(isset($_POST["post_id"]) && var_exist($_POST["post_id"],$tb,"id")){
            $_SESSION[$cf]->level = 2;
            $_SESSION[$cf]->id = $_POST["post_id"];
        }
        if(isset($_POST["new_post"])){
            $_SESSION[$cf]->level = 2;
            $_SESSION[$cf]->id = "new";
        }
    }
    
    if($_SESSION[$cf]->level == 2){
        if($_SESSION[$cf]->id == "new")$post_name="مطلب جدید";else
        $post_name = getVarFromDB($tb,"title","id",$_SESSION[$cf]->id);
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$p_title"."</a> » ".'<a class="curpo" onclick="sub_show('."'clear','1'".')">'.getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state).'</a> »'."$post_name";
        if(isset($_POST["content"])){
            $_SESSION[$cf]->level = 3;
            $_SESSION[$cf]->sub_id = "content";
        }
        if(isset($_POST["pic"])){
            $_SESSION[$cf]->level = 3;
            $_SESSION[$cf]->sub_id = $_POST["pic"];
        }
    }
    if($_SESSION[$cf]->level == 3){
        switch($_SESSION[$cf]->sub_id){
            case "content":
                $sub_title = "تغییر محتوا";
                break;
            case "single":
                $sub_title = "انتخاب تصویر اصلی";
                break;
        }
        $post_name = getVarFromDB($tb,"title","id",$_SESSION[$cf]->id);
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$p_title"."</a> » ".'<a class="curpo" onclick="sub_show('."'clear','1'".')">'.getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state).'</a> »'.$post_name."</a> » $sub_title";
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
?>
<?php
        }
    }
?>