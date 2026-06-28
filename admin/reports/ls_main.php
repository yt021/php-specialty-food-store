<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where($cf);
    $tb = "orders";
    $title_b = getVarFromDB("admin_modules","name","flag",$cf);
    $atb = "admin_reports"."_state";
    if(isset($_POST["clear"]) && $_POST["clear"]=='0')
    {
        $_SESSION[$cf] = new where($cf);
    }
    if(isset($_POST["clear"]) && $_POST["clear"]=='1')
    {
        $_SESSION[$cf]->level = 1;
        $_SESSION[$cf]->id = null;
    }
    
    include_once $GLOBALS['bu']."modules/wdb/period_select_2_if.php";
    
    if($_SESSION[$cf]->level == 0){
        $title = $title_b;
        if(isset($_POST["state"]) && var_exist($_POST["state"],$atb,"flag")){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->state = $_POST["state"];
        }
    }
    if($_SESSION[$cf]->level == 1){
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b"."</a> » ".getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state);
        if($_SESSION[$cf]->state == "charts" || $_SESSION[$cf]->state == "chart_time" || $_SESSION[$cf]->state == "time" || $_SESSION[$cf]->state == "activity"){
            include $bu."$module_name/$cf/".$_SESSION[$cf]->state."_edit.php";
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
    <div id="sect2" class="sect">
        <div class="middle container">
                <?php
                    $column_name = "orders.create_date";
                    $where_and = 1;
                    include_once $GLOBALS['bu']."modules/wdb/period_select_2.php";
                ?>
        </div>
    </div>
    <div class="cut w100p"></div>
    <div id="sect3" class="sect ">
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