<?php
require_once __DIR__ . '/../pinket/helpers.php';

    if(isset($indexed)){

        if($indexed == 1){?>

<?php

    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where($cf);

    $tb = $_SESSION[$cf]->section;

    $atb = "admin_$tb"."_state";

    $title_b = "مدیریت سفارش‌ها";

    

    // From Other Modules

    if(isset($_POST["oid"]))

    {

        if(var_exist($_POST["oid"],$tb,"id")){

            $_SESSION[$cf]->level = 2;

            $_SESSION[$cf]->id = $_POST["oid"];

            $_SESSION[$cf]->state = get_ms_flag($cf,getVarFromDB($tb,"state","id",$_SESSION[$cf]->id));

        }

    }

    

    if(isset($_POST["clear"])){

        switch($_POST["clear"]){

            case '0':

                $_SESSION[$cf] = new where($cf);

                break;

            case '1':

                $_SESSION[$cf]->level = 1;

                $_SESSION[$cf]->id = null;

                $_SESSION[$cf]->sub_id = null;

                break;

            case '3':

                $_SESSION[$cf]->level = 3;

                $_SESSION[$cf]->id = null;

                break;

            case '4':

                $_SESSION[$cf]->level = 4;

                $_SESSION[$cf]->id = null;

                break;

            case '5':

                $_SESSION[$cf]->level = 5;

                $_SESSION[$cf]->id = null;

                break;

        }

    }

    

    if(isset($_POST["post_ref"])){
        include $bu."$module_name/$cf/post_ref.php";
    }

    if(isset($_POST["pay_ref"])){

        include $bu."$module_name/$cf/pay_ref.php";

    }

    if(isset($_POST["snappay_reconcile"])){

        include $bu."$module_name/$cf/snappay_reconcile.php";

    }

    if(isset($_POST["edit"])){

        include $bu."$module_name/$cf/edit.php";

    }

    

    if($_SESSION[$cf]->level == 0){

        $title = $title_b;

        if(isset($_POST["state"]) && check_state($cf,$_POST["state"])){

            $_SESSION[$cf]->level = 1;

            $_SESSION[$cf]->state = $_POST["state"];

        }

        if(isset($_POST["find_order"]) && isset($_POST["id"])){
             // Try to find by unified_id first, then fallback to id
             $id = $_POST["id"];
             $id_check = var_exist($id, $tb, "unified_id");
             if ($id_check) {
                 $_SESSION[$cf]->id = getVarFromDB($tb, "id", "unified_id", $id);
             } else {
                 $_SESSION[$cf]->id = $id;
             }
        }

        if(isset($_POST["show_find_order"]) && (var_exist($_POST["show_find_order"],$tb,"unified_id") || var_exist($_POST["show_find_order"],$tb,"id"))){
            $id = $_POST["show_find_order"];
            if (var_exist($id, $tb, "unified_id")) {
                $_SESSION[$cf]->id = getVarFromDB($tb, "id", "unified_id", $id);
            } else {
                $_SESSION[$cf]->id = $id;
            }
            $_SESSION[$cf]->level = 2;
            $_SESSION[$cf]->state = get_ms_flag($cf,getVarFromDB($tb,"state","id",$_SESSION[$cf]->id));
        }

    }

    

    if($_SESSION[$cf]->level == 1){

        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b"."</a> » ".get_ms_name($cf,$_SESSION[$cf]->state);

        if(isset($_POST['show_all'])){

            if(isset($_SESSION[$cf]->sql_where['admin_state']))unset($_SESSION[$cf]->sql_where['admin_state']);

            else $_SESSION[$cf]->sql_where['admin_state'] = 1;

        }

        if(isset($_POST["id"])){
            // Accept both numeric and pk-... unified_id
            $id = $_POST["id"];
            if (var_exist($id, $tb, "unified_id")) {
                $_SESSION[$cf]->id = getVarFromDB($tb, "id", "unified_id", $id);
            } else if (var_exist($id, $tb, "id")) {
                $_SESSION[$cf]->id = $id;
            } else {
                $_SESSION[$cf]->id = null;
            }
            $_SESSION[$cf]->level = 2;
            $_SESSION[$cf]->state = get_ms_flag($cf,getVarFromDB($tb,"state","id",$_SESSION[$cf]->id));
        }

        if(isset($_POST["order"])){

            $_SESSION[$cf]->order_by->add_item($_POST["order"]);

        }

        if(isset($_POST['cp'])){ 

            // if(abs($_POST['cp']-$_SESSION[$cf]->offset)==1){

                $_SESSION[$cf]->offset = $_POST['cp'];

            // }

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

        // page limit

        if(isset($_POST["cf_submit"],$_POST['page_limit'])){

            $_SESSION[$cf]->offset = 0;

            if($_POST['page_limit'] == "all"){

                 $_SESSION[$cf]->page_limit = 'all';

            }

            if(check_number($_POST['page_limit'])){

                 $_SESSION[$cf]->page_limit = (int)$_POST['page_limit'];

            }

        }

        

        if(isset($_POST["ms_submit"])){
            $ids = get_str_index($_POST["ms_submit"],",")[1];
            foreach($ids as $oid){
                $state = getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state);
                updateInDB($tb,"state",$state,"id",$oid);
                // Notify Pinket if this is a Pinket order
                $order = getRow($mysqli, "orders", "id = ?", [$oid], "i");
                if ($order && !empty($order['unified_id']) && !ctype_digit($order['unified_id'])) {
                    require_once $bu . "admin/pinket/status-webhook.php";
                    updateOrderStatusInPinket($oid);
                }
                if($_SESSION[$cf]->state == "new"){
                    updateInDB($tb,"payment_date",date("Y-m-d H:i:s"),"id",$oid);
                    include_once $bu."modules/wdb/log_funcs.php";
                    $uid = getVarFromDB($tb,'uid','id',$oid);
                    $name = getVarFromDB('users','name','id',$uid);
                    $tel = getVarFromDB('users','tel','id',$uid);
                    send_sms_temp($tel,'Sabt',$name,$oid);
                }
            }
        }

    }

    if($_SESSION[$cf]->level == 2){
        $unified_id = getVarFromDB($tb, "unified_id", "id", $_SESSION[$cf]->id);
        if (!$unified_id) $unified_id = $_SESSION[$cf]->id;
        $title = '<a class="curpo" onclick="sub_show(\'clear\',\'0\')">'."$title_b".'</a> » <a class="curpo" onclick="sub_show(\'clear\',\'1\')">'
        .get_ms_name($cf,$_SESSION[$cf]->state).'</a> » سفارش '.$unified_id;
        $_SESSION[$cf]->sub_id = getVarFromDB($tb,"uid","id",$_SESSION[$cf]->id);
        

        if(isset($_POST['change_aid']) && var_exist($_POST['change_aid'],'addresses','id') && getVarFromDB('addresses','uid','id',$_POST['change_aid']) == getVarFromDB($tb,'uid','id',$_SESSION[$cf]->id)){

            updateInDB($tb,'aid',$_POST['change_aid'],'id',$_SESSION[$cf]->id);

        }

    }

    if($_SESSION[$cf]->level == 3){

        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'.get_ms_name($cf,$_SESSION[$cf]->state).'</a> » ثبت شماره مرسوله پستی';

    }

    if($_SESSION[$cf]->level == 4){

        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'.get_ms_name($cf,$_SESSION[$cf]->state).'</a> » لیست ارسال تهران';

                

        $date_name="list_date";

        if(isset($_POST[$date_name])){

            $_SESSION[$cf]->sql_where[$date_name]["day"] = $_POST["day"];

            $_SESSION[$cf]->sql_where[$date_name]["month"] = $_POST["month"];

            $_SESSION[$cf]->sql_where[$date_name]["year"] = $_POST["year"];

        }

        if(!isset($_SESSION[$cf]->sql_where[$date_name])){

            $_SESSION[$cf]->sql_where[$date_name]=CaSDate(time());

        }

        // sabt dar api peyk

        if(isset($_POST["ms_submit"])){

            include('ppt_api_submit.php');

        }

        if(isset($_POST["ms_del"])){

            include('ppt_api_del.php');

        }

        

        

        

    }

    if($_SESSION[$cf]->level == 5){

        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'.get_ms_name($cf,$_SESSION[$cf]->state).'</a> » ثبت شماره پیگیری / مرجع پرداخت';

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
