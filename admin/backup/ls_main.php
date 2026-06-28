<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

    echo session_save_path()."<br><br><br><br>";
    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where($cf);
    $tb = "backup_tables";
    $title_b = "پشتیبان‌گیری";
    if(isset($_POST["clear"])){
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
        if(isset($_POST["state"]) && check_state($cf,$_POST["state"])){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->state = $_POST["state"];
        }
    }
    if($_SESSION[$cf]->level == 1){
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b"."</a> » ".get_ms_name($cf,$_SESSION[$cf]->state);
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