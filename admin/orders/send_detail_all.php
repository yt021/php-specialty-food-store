<?php
    error_reporting(0);
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/admin/session_start.php";
    if(!isset($_SESSION["a_logged"])){
            include($bu."modules/admin/admin_login.php");
        }
    if(isset($_SESSION["a_logged"])){
?>
<title>چاپ مشخصات ارسال</title>



<link rel="stylesheet" id="twentyseventeen-style-css" href="<?php echo $s; ?>css/print.css" type="text/css" media="all">
<link rel="stylesheet" id="twentyseventeen-style-css" href="<?php echo $s; ?>css/font_icon.css" type="text/css" media="all">
<?php        
        $tb = $_SESSION[$cf]->section;
        include_once $bu."modules/cart/cart_funcs.php";
        if(isset($_POST["ms_sdp"]) && $_POST["ms_sdp"]){
            $oids = get_str_index($_POST["ms_sdp"],",")[1];
        }else{
            if(isset($_SESSION[$cf]->sql_where["start_date"])){
                $sql_timestamp_str = createSQLTime($_SESSION[$cf]->sql_where["start_date"]["year"],$_SESSION[$cf]->sql_where["start_date"]["month"],$_SESSION[$cf]->sql_where["start_date"]["day"]);
                
                $where = " $tb.create_date > '$sql_timestamp_str'";
            }
            if(isset($_SESSION[$cf]->sql_where["end_date"])){
                $sql_timestamp_str = createSQLTime($_SESSION[$cf]->sql_where["end_date"]["year"],$_SESSION[$cf]->sql_where["end_date"]["month"],$_SESSION[$cf]->sql_where["end_date"]["day"]);
                
                if(isset($where))$where .= " AND ";else $where ="";
                $where .= " $tb.create_date < '$sql_timestamp_str'";
            }
            if(isset($where))$where = " AND $where";else $where = "";
            
            $cf_where = "";
            if(isset($_SESSION[$cf]->sql_where["county_filter"])){
                switch($_SESSION[$cf]->sql_where["county_filter"]){
                    case "tehran":
                        $cf_where = " AND addresses.county = 'شهر تهران' ";
                        break;
                    case "others":
                        $cf_where = " AND NOT addresses.county = 'شهر تهران' ";
                        break;
                }
            }            
            
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            $state = 2;
            $st = "SELECT $tb.id FROM $tb LEFT JOIN addresses ON $tb.aid = addresses.id WHERE $tb.del_flag = 0 AND $tb.state = $state $where $cf_where $order_str";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "E";
                exit;
            }
            $k = 0;
            $oids = array();
            $res = $st->get_result();
            while($row = $res->fetch_assoc())
            {
                $oids[$k] = $row["id"];
                $k++;
            }
        }
        foreach($oids as $oid){
            $uid = getVarFromDB($tb,"uid","id",$oid);
            $aid = getVarFromDB($tb,"aid","id",$oid);
            $user = db_get_user($uid);
            $address = db_get_address($aid);
            $cart = new cart_read($oid);
            $sales = new sales("read",$oid);
            $unified_id = getVarFromDB($tb,"unified_id","id",$oid);
            $rec_shift = getVarFromDB($tb,"recieve_shift","id",$oid);
            $rec_date = getVarFromDB($tb,"recieve_date","id",$oid);
            $rec_date_rqm = correctDate_rqm($rec_date);
            $send_date = correctDate_rqm(getVarFromDB($tb,"p_send_date","id",$oid));
            $rec_date = correctDate($rec_date);
            
                
            
//            if($rec_date = getVarFromDB($tb,"recieve_date","id",$oid)){
//                $rec_shift = getVarFromDB($tb,"recieve_shift","id",$oid);
//                if($rec_shift == "out_of_queue"){
//                    $rec_date_rqm = " ";
//                    $rec_date = " ";
//                    $send_date = " ";
//                }else{
//                $rec_date_rqm = correctDate_rqm($rec_date);
//                $rec_date = correctDate($rec_date);
//                $send_date = correctDate_rqm(getVarFromDB($tb,"p_send_date","id",$oid));
//                }
//                $rec_shift = cor_sendShift($rec_shift);
//                
//            }
            include($bu."admin/orders/send_detail_data.php");
        }
?>


<script type="text/javascript">
    window.onload = function() { window.print() };
</script>
<?php
}
?>