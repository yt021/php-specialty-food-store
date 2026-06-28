<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where("orders");
    $tb = $_SESSION[$cf]->section;
    $atb = "admin_$tb"."_state";
    $title = "مدیریت سفارش‌ها";
    if(isset($_POST["clear"]) && $_POST["clear"]=='0')
    {
        $_SESSION[$cf] = new where("orders");
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
            updateInDB($tb,"payment_date",date("Y-m-d H:i:s"),"id",$_SESSION[$cf]->id);
//            updateInDB($tb,"payment_state","1","id",$_SESSION[$cf]->id);
        }
        $_SESSION[$cf]->level = 1;
        $_SESSION[$cf]->id = null;
    }
    if(isset($_POST["del"]) && $_POST["del"]=='order'){
        updateInDB($tb,"del_flag",1,"id",$_SESSION[$cf]->id);
        $_SESSION[$cf]->level = 1;
        $_SESSION[$cf]->id = null;
    }
    if($_SESSION[$cf]->level == 0){
        if(isset($_POST["state"]) && var_exist($_POST["state"],$atb,"flag")){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->state = $_POST["state"];
        }
    }
    
    if($_SESSION[$cf]->level == 1){
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."مدیریت سفارش‌ها"."</a> » ".getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state);
        if(isset($_POST["oid"]) && var_exist($_POST["oid"],$tb,"id")){
            $_SESSION[$cf]->level = 2;
            $_SESSION[$cf]->id = $_POST["oid"];
        }
        if(isset($_POST["order"])){
            $_SESSION[$cf]->order_by->add_item($_POST["order"]);
        }
        if(isset($_POST["start_date"]) || isset($_POST["end_date"])){
            if(isset($_POST["start_date"]))$date = "start_date";
            if(isset($_POST["end_date"]))$date = "end_date";
            $_SESSION[$cf]->sql_where[$date]["day"] = $_POST["day"];
            $_SESSION[$cf]->sql_where[$date]["month"] = $_POST["month"];
            $_SESSION[$cf]->sql_where[$date]["year"] = $_POST["year"];
        }
        if(isset($_POST["ms"])){
            $ids = get_str_index($_POST["ms"],",")[1];
            foreach($ids as $oid){
                $ostate = getVarFromDB($tb,"state","id",$oid);
                $ostate = (int)$ostate+1;
                updateInDB($tb,"state",$ostate,"id",$oid);
            }
        }
    }
    if($_SESSION[$cf]->level == 2){
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."مدیریت سفارش‌ها".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'
        .getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state).'</a> » سفارش '.$_SESSION[$cf]->id;
        $_SESSION[$cf]->sub_id = getVarFromDB($tb,"uid","id",$_SESSION[$cf]->id);
        if(isset($_POST["order_detail"]) && check_value("text",$_POST["order_detail"])){
            updateInDB($tb,"order_detail",check_value("text",$_POST["order_detail"]),"id",$_SESSION[$cf]->id);
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
            include $bu."$module_name/$cf/".$_SESSION["orders"]->level.".php";
        ?>
        </div>  
    </div>
</div>

<?php
        }
    }
?>