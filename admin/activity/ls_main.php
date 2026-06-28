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
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b"."</a> » ".getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state);
        
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
        if(!isset($_SESSION[$cf]->sql_where["start_date"]) && !isset($_SESSION[$cf]->sql_where["end_date"])){
            $_SESSION[$cf]->sql_where["start_date"] = CaSDate(time());
            $_SESSION[$cf]->sql_where["start_date"]["day"] = 1;
            $_SESSION[$cf]->sql_where["end_date"] = $_SESSION[$cf]->sql_where["start_date"];
            if($_SESSION[$cf]->sql_where["end_date"]["month"] == 12){
                $_SESSION[$cf]->sql_where["end_date"]["year"]++;
                $_SESSION[$cf]->sql_where["end_date"]["month"]=1;
            }else{
                $_SESSION[$cf]->sql_where["end_date"]["month"]++;
            }
        }
        
        $date_name = "show_log";
        $show_log = "";
        if(isset($_POST[$date_name])){
            $show_log = "show";
            $_SESSION[$cf]->sql_where[$date_name]["day"] = 1;
            $_SESSION[$cf]->sql_where[$date_name]["month"] = $_POST["month"];
            $_SESSION[$cf]->sql_where[$date_name]["year"] = $_POST["year"];
        }
        if(!isset($_SESSION[$cf]->sql_where[$date_name])){
            $_SESSION[$cf]->sql_where[$date_name] = CaSDate(time());
        }
        
        if(isset($_POST["eid"]) && var_exist($_POST["eid"],"employees","id")){
            $_SESSION[$cf]->sql_where["eid"] = $_POST["eid"];
        }
        if(!isset($_SESSION[$cf]->sql_where["eid"])){
            $_SESSION[$cf]->sql_where["eid"] = 0;
        }
        
        
        if(isset($_POST["id"]) && (var_exist($_POST["id"],$tb,"id") || $_POST["id"] == "new")){
            $_SESSION[$cf]->level = 2;
            $_SESSION[$cf]->id = $_POST["id"];
        }
    }
   
    
    if($_SESSION[$cf]->level == 2){
        if($_SESSION[$cf]->id == "new")$sub_title = " جدید";
        else $sub_title = $_SESSION[$cf]->id;
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'
        .getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state).'</a> » فعالیت '.$sub_title;
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