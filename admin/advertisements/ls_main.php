<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php  
    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where($cf);
    
    $tb = $_SESSION[$cf]->section;
    
    $title_b = "مدیریت ".getVarFromDB("admin_modules","name","flag",$cf);

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
        }
        
    }
    
    if($_SESSION[$cf]->level == 0){
        $title = $title_b;
        if(isset($_POST["order"])){
            $_SESSION[$cf]->order_by->add_item($_POST["order"]);
        }
        
        if(isset($_POST["id"]) && (var_exist($_POST["id"],$tb,"id") || $_POST["id"] == "new")){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = $_POST["id"];
        }
    }
   
    
    if($_SESSION[$cf]->level == 1){
        if($_SESSION[$cf]->id == "new")$sub_title = " جدید";
        else $sub_title = getVarFromDB($tb,"name","id",$_SESSION[$cf]->id);
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b"."</a> » ".$sub_title;
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