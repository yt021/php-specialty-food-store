<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    include_once $bu."modules/cart/cart_funcs.php";
    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where("products");
    $tb = $_SESSION[$cf]->section;
    $title = "مدیریت محصولات";
    if(isset($_POST["edit"])){
        include $bu."$module_name/$cf/edit.php";
    }
    if(isset($_POST["pid"])){
        if($_POST["pid"] == "new"){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = $_POST["pid"];
            $_SESSION[$cf]->sub_id = null;
        }else{
            if(var_exist($_POST["pid"],$tb,"id")){
                $_SESSION[$cf]->level = 1;
                $_SESSION[$cf]->id = $_POST["pid"];
                $_SESSION[$cf]->sub_id = null;
            }
        }
    }
    if(isset($_POST["img_str"]) && isset($_SESSION[$cf]->sub_id) && $_SESSION[$cf]->id != "new")
    {
        if($_SESSION[$cf]->sub_id == "single"){
            if(var_exist($_POST["img_str"],"content","id") && getVarFromDB("content","type","id",$_POST["img_str"]) == "Image"){
                updateInDB($tb,"first_img_id",$_POST["img_str"],"id",$_SESSION[$cf]->id);
                // --- Pinket Sync after main image change ---
                require_once __DIR__ . '/../pinket/products.php';
                file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [LS_MAIN] main image changed, syncing products to Pinket\n", FILE_APPEND);
                $result = sendProductsToPinket($mysqli);
                file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [LS_MAIN] main image Pinket API result: " . json_encode($result) . "\n", FILE_APPEND);
                $_SESSION[$cf]->level = 1;
                $_SESSION[$cf]->sub_id = null;
            }
        }
        if($_SESSION[$cf]->sub_id == "multi"){
            updateInDB($tb,"img_str",$_POST["img_str"],"id",$_SESSION[$cf]->id);
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->sub_id = null;
        }
    }
    if(isset($_POST["clear"]) && $_POST["clear"]=='0'){
        $_SESSION[$cf] = new where("products");
    }
    if(isset($_POST["clear"]) && $_POST["clear"]=='1'){
        $_SESSION[$cf]->level = 1;
        $_SESSION[$cf]->sub_id = null;
    }
    
    if(isset($_POST["content_text"]) && isset($_POST["edit"])){
        updateInDB($tb,"content",$_POST["content_text"],"id",$_SESSION[$cf]->id);
        $_SESSION[$cf]->level = 1;
        $_SESSION[$cf]->sub_id = null;
    }
    
    if($_SESSION[$cf]->level == 0){
        if(isset($_POST["pid"]) && var_exist($_POST["pid"],$tb,"id")){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = $_POST["pid"];
        }
        if(isset($_POST["order"])){
            $_SESSION[$cf]->order_by->add_item($_POST["order"]);
        }
        
        if(isset($_POST["id_show"]) && var_exist($_POST["id_show"],$tb,"id")){
            $show_flag = (int)getVarFromDB($tb,"state","id",$_POST["id_show"]);
            $show_flag = 1 - $show_flag;
            updateInDB($tb,"state",$show_flag,"id",$_POST["id_show"]);
            // --- Pinket Sync after وضعیت ناموجودی ---
            require_once __DIR__ . '/../pinket/products.php';
            file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [LS_MAIN] وضعیت ناموجودی used, syncing products to Pinket\n", FILE_APPEND);
            $result = sendProductsToPinket($mysqli);
            file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [LS_MAIN] وضعیت ناموجودی Pinket API result: " . json_encode($result) . "\n", FILE_APPEND);
        }
        if(isset($_POST["id_del"]) && var_exist($_POST["id_del"],$tb,"id")){
            $del_flag = (int)getVarFromDB($tb,"del_flag","id",$_POST["id_del"]);
            $del_flag = 1 - $del_flag;
            updateInDB($tb,"del_flag",$del_flag,"id",$_POST["id_del"]);
            // --- Pinket Sync after حذف ---
            require_once __DIR__ . '/../pinket/products.php';
            file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [LS_MAIN] حذف used, syncing products to Pinket\n", FILE_APPEND);
            $result = sendProductsToPinket($mysqli);
            file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [LS_MAIN] حذف Pinket API result: " . json_encode($result) . "\n", FILE_APPEND);
        }
        
        if(isset($_POST["del_flag"])){
            $_SESSION[$cf]->del_flag = 1-$_SESSION[$cf]->del_flag;
        }
        
    }
    
    if($_SESSION[$cf]->level == 1){
        $pid = $_SESSION[$cf]->id;
        if($pid == "new"){$pname = "محصول جدید";}else{
        $pname = getVarFromDB($tb,"name","id",$pid);}
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'.$title."</a> » ".$pname;
        if(isset($_POST["pic"]) && ($_POST["pic"] == "single" || $_POST["pic"] == "multi")){
            $_SESSION[$cf]->level = 2;
            $_SESSION[$cf]->sub_id = $_POST["pic"];
        }
        
        if((isset($_POST["img_right"]) || isset($_POST["img_left"]) || isset($_POST["img_del"]))&& $img_str = getVarFromDB($tb,"img_str","id",$_SESSION[$cf]->id)){
            $images = get_str_index($img_str,",")[1];
            if(isset($_POST["img_right"])){
                $f = get_str_index($img_str,",",$_POST["img_right"])[0];
                if($f !== null && $f != 0){
                    $a = $images[$f-1];
                    $images[$f-1]=$images[$f];
                    $images[$f] = $a;
                }
                
            }
            if(isset($_POST["img_left"])){
                $f = get_str_index($img_str,",",$_POST["img_left"])[0];
                if($f !== null && $f != sizeof($images)-1){
                    $a = $images[$f+1];
                    $images[$f+1]=$images[$f];
                    $images[$f] = $a;
                }
            }
            if(isset($_POST["img_del"])){
                $f = get_str_index($img_str,",",$_POST["img_del"])[0];
                if($f !== null){
                    array_splice($images,$f,1);
                }
            }
            $img_str = "";
            foreach($images as $image_id){
                $img_str .= $image_id.",";
            }
            updateInDB($tb,"img_str",$img_str,"id",$_SESSION[$cf]->id);
        }
        
        if(isset($_POST["detail"])){
            $_SESSION[$cf]->level = 2;
            $_SESSION[$cf]->sub_id = "detail";
        }
        
    }
        
    if($_SESSION[$cf]->level == 2){
        if($_SESSION[$cf]->sub_id == "multi"){
            $sub_title = "انتخاب سایر تصاویر";
        }else if($_SESSION[$cf]->sub_id == "single"){
            $sub_title = "انتخاب تصویر اصلی";
        }else if($_SESSION[$cf]->sub_id == "detail"){
            $sub_title = "تغییر توضیحات";
        }
        $pid = $_SESSION[$cf]->id;
        if($pid == "new"){$pname = "محصول جدید";}else{
        $pname = getVarFromDB($tb,"name","id",$pid);}
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."مدیریت محصولات".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'.$pname."</a> » $sub_title";
    }
    
    
if($_POST){
    header("Location:$URL");
    die;
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