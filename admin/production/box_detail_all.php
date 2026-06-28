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
<title>چاپ مشخصات جعبه‌های مخلوط انتخابی</title>
<link rel="stylesheet" id="twentyseventeen-style-css" href="<?php echo $s; ?>css/print.css" type="text/css" media="all">
<link rel="stylesheet" id="twentyseventeen-style-css" href="<?php echo $s; ?>css/print_box.css" type="text/css" media="all">
<link rel="stylesheet" id="twentyseventeen-style-css" href="<?php echo $s; ?>css/font_icon.css" type="text/css" media="all">
<body>
<?php        
        $tb = $_SESSION[$cf]->section;
        $tb2 = "sub_$tb";
        $type = $_SESSION[$cf]->state;
        include_once $bu."modules/cart/cart_funcs.php";
        
        if(isset($_POST["ms_sdp"]) && $_POST["ms_sdp"]){
            $soids = get_str_index($_POST["ms_sdp"],",")[1];
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
            $st = "SELECT $tb2.id FROM $tb2 LEFT JOIN $tb ON $tb.id = $tb2.oid LEFT JOIN addresses ON $tb.aid = addresses.id WHERE $tb.del_flag = 0 AND $tb.state = 1 AND $tb2.type = '$type' $where $cf_where";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "E";
                exit;
            }
            $k = 0;
            $soids = array();
            $res = $st->get_result();
            while($row = $res->fetch_assoc())
            {
                $soids[$k] = $row["id"];
                $k++;
            }
        }
        // var_dump($soids);
        foreach($soids as $soid){
            $pid = getVarFromDB($tb2,"pid","id",$soid);
            $weight = getVarFromDB($tb2,"weight","id",$soid);
            $no = getVarFromDB($tb2,"number","id",$soid);
            $oid = getVarFromDB($tb2,"oid","id",$soid);
            $create_date = getVarFromDB($tb,"create_date","id",$oid);
            $box = new order_box_read($pid,$no,$weight,$create_date,$soid);
            include($bu."admin/production/box_detail_data.php");
        }
?>
<script type="text/javascript">
    window.onload = function() { window.print() };
</script>
<style >

</style>
</body>

<?php
}
?>