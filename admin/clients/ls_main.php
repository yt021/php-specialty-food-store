<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    include_once $bu."modules/cart/cart_funcs.php";
    if(!isset($_SESSION["$cf"]))$_SESSION["$cf"] = new where("users");
    $tb = $_SESSION["$cf"]->section;
    $title_b = "مدیریت مشتریان";
    if(isset($_POST["edit"])){
        include $bu."$module_name/$cf/edit.php";
    }
    // From Other Modules
    if(isset($_POST["uid"]))
    {
        if(var_exist($_POST["uid"],$tb,"id")){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = $_POST["uid"];
            $_SESSION[$cf]->state = null;
            $_SESSION[$cf]->sub_id = null;
        }
        
    }
    if(isset($_POST["all_users"])){
        $_SESSION[$cf]->state = "all_users";
    }
    
    if(isset($_POST["clear"]))
    {
        switch($_POST["clear"]){
            case '0':
                $_SESSION[$cf] = new where("users");
                break;
            case '1':
                $_SESSION[$cf]->level = 1;
                $_SESSION[$cf]->state = null;
                break;
            case '2':
                $_SESSION[$cf]->level = 2;
                $_SESSION[$cf]->sub_id = null;
        }
        
    }
    if($_SESSION[$cf]->level == 0){
        $page_limit = 100;
        $st = "SHOW TABLE STATUS WHERE NAME = 'users'";
        $st = $mysqli->prepare($st);
        $st->execute();
        $res = $st->get_result();
        $res = $res->fetch_assoc();
        $nor = $res['Rows'];
        if(!isset($_SESSION[$cf]->sql_where['cp']))$_SESSION[$cf]->sql_where['cp'] = 0;
        
        $title = $title_b;
        if(isset($_POST["id"]) && var_exist($_POST["id"],$tb,"id")){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = $_POST["id"];
        }
        if(isset($_POST["order"])){
            $_SESSION[$cf]->order_by->add_item($_POST["order"]);
        }
        if(isset($_POST["find_client"]) && isset($_POST["tel"])){
             $_SESSION[$cf]->id = $_POST["tel"];
        }
        if(isset($_POST["cp"])){
            $_SESSION[$cf]->sql_where['cp'] = (int)$_POST["cp"];
        }
    }
    
    if($_SESSION[$cf]->level == 1){
        $uid = $_SESSION[$cf]->id;
        $user = db_get_user($uid);
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b"."</a> » ".$user->data()["name"];
        
        if(isset($_POST["aid"]) && (var_exist($_POST["aid"],'addresses',"id") || $_POST["aid"] == "new" )){
                $_SESSION[$cf]->level = 2;
//                $_SESSION[$cf]->state = "address";
                $_SESSION[$cf]->sub_id = $_POST["aid"];
        }
        
        if(isset($_POST["t_janitor"]) && var_exist($_POST["t_janitor"],'addresses',"id")){
            $janitor = "yes";
            if(getVarFromDB('addresses','janitor','id',$_POST["t_janitor"]) == "yes")$janitor = '';
            updateInDB('addresses','janitor',$janitor,'id',$_POST["t_janitor"]);
        }
    }
    if($_SESSION[$cf]->level == 2){
        $user = db_get_user($_SESSION[$cf]->id);
        $title_state = "مشاهده آدرس";
        if($_SESSION[$cf]->sub_id == "new"){$title_state = "آدرس جدید";}
        $title = '<a class="curpo" onclick="sub_show('."'clear','0'".')">'."$title_b".'</a> » <a class="curpo" onclick="sub_show('."'clear','1'".')">'.$user->data()["name"]."</a> » $title_state";
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