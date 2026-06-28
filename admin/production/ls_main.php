<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where("orders");
    $tb = $_SESSION[$cf]->section;
    $atb = "admin_$tb"."_type";
    $title_b = "مدیریت تولیدات";
    if(isset($_POST["clear"]) && $_POST["clear"]=='0')
    {
        $_SESSION[$cf] = new where("orders");
    }
    if(isset($_POST["clear"]) && $_POST["clear"]=='1')
    {
        $_SESSION[$cf]->level = 1;
        $_SESSION[$cf]->id = null;
    }
    if($_SESSION[$cf]->level == 0){
        $title = $title_b;
        if(isset($_POST["state"]) && var_exist($_POST["state"],$atb,"flag")){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->state = $_POST["state"];
        }
    }
    
    if($_SESSION[$cf]->level == 1){
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'.$title_b."</a> » ".getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state);
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
        
        // county_filter
        $items = array("all"=>"همه","tehran"=>"تهران","others"=>"شهرستان‌ها");
        if(isset($_POST["cf_submit"],$_POST["county_filter"],$items[$_POST["county_filter"]])){
            $_SESSION[$cf]->sql_where["county_filter"] = $_POST["county_filter"];
        }
        if(!isset($_SESSION[$cf]->sql_where["county_filter"])){
            $_SESSION[$cf]->sql_where["county_filter"] = "all";
        }
        // date_base
        $items = array("create_date"=>"تاریخ ثبت","p_send_date"=>"تاریخ ارسال ");
        if(isset($_POST["cf_submit"],$_POST["date_base"],$items[$_POST["date_base"]])){
            $_SESSION[$cf]->sql_where["date_base"] = $_POST["date_base"];
        }
        if(!isset($_SESSION[$cf]->sql_where["county_filter"])){
            $_SESSION[$cf]->sql_where["date_base"] = "create_date";
        }
        
        
    }
    if($_SESSION[$cf]->level == 2){
//        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."مدیریت سفارش‌ها".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'.getVarFromDB($atb,"name","flag",$_SESSION[$cf]->state).'</a> » مشاهده جزئیات';
//        $_SESSION[$cf]->sub_id = getVarFromDB($tb,"uid","id",$_SESSION[$cf]->id);

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