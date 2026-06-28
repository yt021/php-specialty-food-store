<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
if($_SESSION["a_logged"]->get_level() >= 2){

    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where($cf);
    $tb = $_SESSION[$cf]->section;
//    $atb = "admin_$tb"."_state";
    $p_title = "مدیریت بهینه‌سازی جستجوگر";
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

    if($_SESSION[$cf]->level == 0){
        
        $title = $p_title;
        if(isset($_POST["order"])){
            $_SESSION[$cf]->order_by->add_item($_POST["order"]);
        }
        if(isset($_POST["mdl_id"]) && var_exist($_POST["mdl_id"],$tb,"id")){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = $_POST["mdl_id"];
        }
    }
    
    if($_SESSION[$cf]->level == 1){
        $sub_title = getVarFromDB($tb,"name_fa","id",$_SESSION[$cf]->id);
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$p_title"."</a> » "."$sub_title";
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