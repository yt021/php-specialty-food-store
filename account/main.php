<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(!isset($_SESSION[$cf]))$_SESSION[$cf] = new where($cf);
    $atb = "account_state";
    
    $title = "Ø­Ø³Ø§Ø¨ Ù…Ù†";
    
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
                $_SESSION[$cf]->sub_id = null;
        }
        
    }
    
    if($_SESSION[$cf]->level == 0){
        if(isset($_POST["state"]) && var_exist($_POST["state"],$atb,"flag")){
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->state = $_POST["state"];
        }
        if(isset($_POST["oid"]) && var_exist($_POST["oid"],"orders","id") && getVarFromDB("orders","state","id",$_POST["oid"]) >= 1 && getVarFromDB("orders","uid","id",$_POST["oid"])==$_SESSION["logged"]->uid){
            $_SESSION[$cf]->level = 2;
            $_SESSION[$cf]->state = "orders";
            $_SESSION[$cf]->id = $_POST["oid"];
        }
    }
    if($_SESSION[$cf]->level == 1){
        if(isset($_POST["oid"]) && var_exist($_POST["oid"],"orders","id") && getVarFromDB("orders","uid","id",$_POST["oid"])==$_SESSION["logged"]->uid){
            $_SESSION[$cf]->level = 2;
            $_SESSION[$cf]->state = "orders";
            $_SESSION[$cf]->id = $_POST["oid"];
        }

    }
    if(
        $_SESSION[$cf]->level == 2 &&
        isset($_SESSION[$cf]->state) &&
        $_SESSION[$cf]->state == "orders" &&
        isset($_POST["snappay_user_action"]) &&
        !isset($_POST["snappay_user_csrf"])
    ){
        include $bu."account/snappay_order_action.php";
    }
    
            
    include $bu."account/".$_SESSION[$cf]->state.$_SESSION[$cf]->level.".php";
        
?>
<style type="text/css">
    .account_dash_page{
        max-width:1240px;
        margin:0 auto;
    }
    .account_dash_header{
        border:1px solid rgba(0,0,0,0.12);
        border-radius:18px;
        background:linear-gradient(135deg,#fff 0%,#f7f2ef 100%);
        padding:10px 14px;
        margin-bottom:12px;
    }
    .account_dash_kicker{
        margin:0 0 2px 0;
        font-size:22px;
        line-height:1.15;
        font-weight:800;
        color:#4a2d22;
        text-align:right;
    }
    .account_dash_title{
        margin:0;
        font-size:14px;
        line-height:1.35;
        font-weight:600;
        color:#8a6d5d;
        text-align:right;
    }
    .account_dash_meta{
        margin-top:8px;
        display:flex;
        flex-wrap:wrap;
        gap:8px;
    }
    .account_dash_meta_item{
        border:1px solid rgba(128,0,0,0.12);
        border-radius:12px;
        background:rgba(255,255,255,0.88);
        padding:7px 9px;
        min-width:150px;
        flex:1 1 180px;
        box-sizing:border-box;
    }
    .account_dash_meta_label{
        display:block;
        margin-bottom:2px;
        font-size:10px;
        color:#8a6d5d;
    }
    .account_dash_meta_value{
        display:block;
        font-size:12px;
        line-height:1.4;
        font-weight:600;
        color:#2c201b;
    }
    .account_dash_layout{
        display:grid;
        grid-template-columns:minmax(0,1fr);
        gap:14px;
    }
    .account_dash_panel{
        border:1px solid rgba(0,0,0,0.12);
        border-radius:14px;
        background:#fff;
        padding:14px 16px;
        box-sizing:border-box;
    }
    .account_dash_panel_title{
        margin:0 0 10px 0;
        font-size:16px;
        line-height:1.3;
        font-weight:700;
        color:#2c201b;
        text-align:right;
    }
    .account_dash_panel_text{
        margin:0;
        line-height:1.9;
        color:#4f433e;
    }
    .account_dash_table_wrap{
        overflow:auto;
    }
    .account_dash_panel table.cart{
        width:100%;
        margin:0;
    }
    .account_dash_panel table.cart thead th{
        padding-top:8px;
        padding-bottom:8px;
        font-size:12px;
        line-height:1.3;
        font-weight:700;
    }
    .account_dash_panel table.cart tbody td{
        padding-top:10px;
        padding-bottom:10px;
        vertical-align:middle;
    }
    .account_dash_orders_cards{
        display:none;
    }
    .account_dash_order_card{
        border:1px solid rgba(128,0,0,0.16);
        border-radius:14px;
        background:linear-gradient(180deg,#fff 0%,#fbf7f4 100%);
        padding:14px 16px;
        box-sizing:border-box;
    }
    .account_dash_order_card + .account_dash_order_card{
        margin-top:10px;
    }
    .account_dash_order_card_head{
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:12px;
        margin-bottom:10px;
    }
    .account_dash_order_card_title{
        margin:0;
        font-size:16px;
        line-height:1.35;
        font-weight:700;
        color:#2c201b;
    }
    .account_dash_order_card_status{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        padding:4px 10px;
        border-radius:999px;
        background:#8d0e0e;
        color:#fff;
        font-size:11px;
        line-height:1.4;
        font-weight:700;
        white-space:nowrap;
    }
    .account_dash_order_card_grid{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:8px 10px;
    }
    .account_dash_order_card_item{
        border:1px solid rgba(128,0,0,0.10);
        border-radius:10px;
        background:#fff;
        padding:8px 10px;
        min-width:0;
    }
    .account_dash_order_card_item.is-wide{
        grid-column:1 / -1;
    }
    .account_dash_order_card_label{
        display:block;
        margin-bottom:3px;
        font-size:10px;
        line-height:1.4;
        color:#8a6d5d;
    }
    .account_dash_order_card_value{
        display:block;
        font-size:13px;
        line-height:1.65;
        font-weight:600;
        color:#2c201b;
        overflow-wrap:anywhere;
    }
    .account_dash_order_card_actions{
        margin-top:12px;
    }
    .account_dash_order_card_actions .btn{
        width:100%;
        margin:0 !important;
        box-sizing:border-box;
        text-align:center;
    }
    .account_dash_actions{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:10px;
    }
    .account_dash_side_layout{
        display:grid;
        grid-template-columns:minmax(0,1fr) 320px;
        gap:16px;
        align-items:start;
    }
    .account_dash_actions_intro{
        display:none;
    }
    .account_dash_actions_box{
        display:flex;
        flex-direction:column;
        gap:10px;
    }
    .account_dash_actions .btn{
        width:100%;
        margin:0 !important;
        box-sizing:border-box;
        text-align:center;
    }
    .account_dash_back{
        max-width:280px;
        margin:0 auto;
    }
    .account_dash_back .btn{
        width:100%;
        margin:0 !important;
        box-sizing:border-box;
        text-align:center;
    }
    .account_dash_forms{
        display:grid;
        grid-template-columns:repeat(2,minmax(0,1fr));
        gap:14px;
    }
    .account_dash_form_actions{
        margin-top:12px;
    }
    .account_dash_form_actions .btn{
        min-width:180px;
        margin:0;
    }
    @media (max-width:900px){
        .account_dash_side_layout,
        .account_dash_actions,
        .account_dash_forms{
            grid-template-columns:minmax(0,1fr);
        }
        .account_dash_order_list_block .account_dash_table_wrap{
            display:none;
        }
        .account_dash_order_list_block .account_dash_orders_cards{
            display:block;
        }
    }
    @media (max-width:640px){
        .account_dash_header{
            padding:10px 12px;
        }
        .account_dash_kicker{
            font-size:18px;
        }
        .account_dash_title{
            font-size:13px;
        }
        .account_dash_order_card_head{
            flex-direction:column;
            align-items:flex-start;
        }
        .account_dash_order_card_grid{
            grid-template-columns:minmax(0,1fr);
        }
        .account_dash_actions .btn,
        .account_dash_back .btn,
        .account_dash_form_actions .btn{
            min-width:0;
            width:100%;
        }
    }
</style>
        
        

<script type="text/javascript">
function sub_show(key,value,address=""){
    var form = document.createElement("form");
    var input = document.createElement("input");
    
    form.method = "POST";
    form.action = "<?php echo $URL; ?>";
    if(address != ""){
        form.action = "<?php echo $s."admin/"; ?>"+address;
    }
    input.value = value;
    input.name = key;
    form.appendChild(input);
    
    document.body.appendChild(form);
    form.submit();
}
</script>
<?php
        }
    }
?>


