<?php
    error_reporting(0);
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/admin/session_start.php";
    if(!isset($_SESSION["a_logged"])){
            include($bu."modules/admin/admin_login.php");
        }
    if(isset($_SESSION["a_logged"])){
        $tb = $_SESSION[$cf]->section;
    if(isset($_SESSION[$cf]->id) && getVarFromDB($tb,"state","id",$_SESSION[$cf]->id) >= 2){
        $oid = $_SESSION[$cf]->id;
        $unified_id = getVarFromDB($tb,"unified_id","id",$oid);
        include_once $bu."modules/cart/cart_funcs.php";
        $uid = getVarFromDB($tb,"uid","id",$oid);
        $aid = getVarFromDB($tb,"aid","id",$oid);
        $user = db_get_user($uid);
        $address = db_get_address($aid);
        $cart = new cart_read($oid);
        $sales = new sales("read",$oid);
        
        $rec_shift = getVarFromDB($tb,"recieve_shift","id",$oid);
        $rec_date = getVarFromDB($tb,"recieve_date","id",$oid);
        $rec_date_rqm = correctDate_rqm($rec_date);
        $send_date = correctDate_rqm(getVarFromDB($tb,"p_send_date","id",$oid));
        $rec_date = correctDate($rec_date);
?>





<link rel="stylesheet" id="twentyseventeen-style-css" href="<?php echo $s; ?>css/print.css" type="text/css" media="all">
<link rel="stylesheet" id="twentyseventeen-style-css" href="<?php echo $s; ?>css/font_icon.css" type="text/css" media="all">

<?php
    include($bu."admin/orders/send_detail_data.php");
?>

<script type="text/javascript">
    window.onload = function() { window.print() };
</script>
<?php
    }
}
?>