<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where($cf);
    $tb = $_SESSION[$cf]->section;
    $atb = "admin_send_state";
    $p_title = "مدیریت ارسال";
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
                break;
        }
        
    }
    if($_SESSION[$cf]->level == 0){
        $title = $p_title;
        if(isset($_POST["state"]) && var_exist($_POST["state"],$atb,"flag")){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->state = $_POST["state"];
        }
    }
    if($_SESSION[$cf]->level == 1){
        $tb = $_SESSION[$cf]->state;
        $sub_title = getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state);
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$p_title"."</a> » ".$sub_title;
        
        if(isset($_POST["id"]) && ($_POST["id"] == "new" || var_exist($_POST["id"],$tb,"id"))){
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
        if($_SESSION[$cf]->state == "sd_shifts"){
            if(isset($_POST["transporter"]) && var_exist($_POST["transporter"],"transporters","id")){
                $_SESSION[$cf]->sql_where["transporter"] = $_POST["transporter"];
            }
            if(!isset($_SESSION[$cf]->sql_where["transporter"])){
                $_SESSION[$cf]->sql_where["transporter"] = getVarFromDB("sd_setting","value","flag","tp_id");
            }
        }
    }
    if($_SESSION[$cf]->level == 2){
        $tb = $_SESSION[$cf]->state;
        if($_SESSION[$cf]->id == "new"){
            switch($_SESSION[$cf]->state){
                case "cities":
                    $sub_title = "استان";
                    break;
                case "sd_shifts":
                    $sub_title = "نوبت";
                    break;
                case "sle_set":
                    $sub_title = "قالب لیست ارسال";
                    break;
            }
            $sub_title = "$sub_title جدید";
        }
        else{
            switch($_SESSION[$cf]->state){
                case "cities":
                    $sub_title = getVarFromDB($tb,"county","id",$_SESSION[$cf]->id);
                    break;
                case "sd_shifts":
                    $sub_title = "نوبت ".$_SESSION[$cf]->id;
                    break;
                case "sle_set":
                    $sub_title = "نمونه لیست ارسال ".$_SESSION[$cf]->id;
                    break;
            }
        } 
        
        if($_SESSION[$cf]->state == "sle_set"){
            if(isset($_POST["change"]) && $_POST["change"] == 'variable_data'){
                $_SESSION[$cf]->level = 3;
            }
        }
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$p_title".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'
        .getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state).'</a> » '.$sub_title;
    }
    
    if($_SESSION[$cf]->level == 3){
        $tb = $_SESSION[$cf]->state;
        switch($_SESSION[$cf]->state){
            case "cities":
                $sub_title = getVarFromDB($tb,"county","id",$_SESSION[$cf]->id);
                break;
            case "sd_shifts":
                $sub_title = "نوبت ".$_SESSION[$cf]->id;
                break;
            case "sle_set":
                $sub_title = "نمونه لیست ارسال ".$_SESSION[$cf]->id;
                break;
        }
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$p_title".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'
        .getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state).'</a> » <a class="curpo" onclick="sub_show('."'clear','2'".')">'.$sub_title.'</a> » تنظیم محتوای متغیر (ستون‌ها)';
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