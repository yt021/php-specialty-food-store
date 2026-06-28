<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where("pages");
    $tb = $_SESSION[$cf]->section;
    $p_title = "مدیریت صفحات";
    if(isset($_POST["edit"])){
        include $bu."$module_name/$cf/edit.php";
    }
    
    if(isset($_POST["clear"]) && $_POST["clear"]=='0')
    {
        $_SESSION[$cf] = new where("pages");
    }
    if(isset($_POST["clear"]) && $_POST["clear"]=='1')
    {
        $_SESSION[$cf]->level = 1;
        $_SESSION[$cf]->sub_id = null;
    }
    
    if($_SESSION[$cf]->level == 0){
        $title = $p_title;
        if(isset($_POST["page_id"]) && var_exist($_POST["page_id"],$tb,"id")){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = $_POST["page_id"];
        }
        if(isset($_POST["new_page"])){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = "new";
        }
    }
    
    if($_SESSION[$cf]->level == 1){
        if($_SESSION[$cf]->id == "new")$page_name="صفحه جدید";else
        $page_name = getVarFromDB($tb,"title","id",$_SESSION[$cf]->id);
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$p_title"."</a> » $page_name";
        if(isset($_POST["content"])){
            $_SESSION[$cf]->level = 2;
            $_SESSION[$cf]->sub_id = "content";
        }
    }
    if($_SESSION[$cf]->level == 2){
        $sub_title = "تغییر محتوا";
        $page_name = getVarFromDB($tb,"title","id",$_SESSION[$cf]->id);
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$p_title".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'.$page_name."</a> » $sub_title";
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