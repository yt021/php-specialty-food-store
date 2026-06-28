<?php
$indexed = 1;
include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
include $bu."modules/cart/session_start.php";
// echo ini_get('message_size_limit');
function table_columns_comma($table,$db){
    $tcc = "(";
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $st = "SELECT column_name FROM information_schema.columns WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ? ";
    $st = $mysqli->prepare($st);
    $st->bind_param('ss',$table,$db);
    if(!$st->execute())return 0;
    $res = $st->get_result();
    while($row = $res->fetch_assoc()){
        $tcc .= $row["column_name"].",";
    }
    $tcc = substr($tcc,0,strlen($tcc)-1).")";
    return $tcc;
}

if(isset($_GET["flag"]) && var_exist($_GET["flag"],"backup_tables","flag") && isset($_GET["way"])){
    $way = $_GET["way"];
    $flag = $_GET["flag"];
    $data = "";
    $error = false;
    switch($flag){
        case "all":
            include $bu."admin/backup/sub_back_all.php";
            break;
        case "all_tb":
            include $bu."admin/backup/sub_back_all_tb.php";
            break;
        default:
            include $bu."admin/backup/sub_back_data.php";
            break;
    }
    if($error){
        echo $error;
        exit;
        die;
    }
    
    $file_name = "backup_db_".$flag."_".date("Y_m_d").".sql";
    switch($way){
        case "download":
            if(isset($_SESSION["backup"]) && isset($_SESSION["a_logged"])){
                new_backup_db_record($flag,$way,1);
                header('Content-type: application/sql; charset=utf-8');
                header('Content-Disposition: attachment; filename="'.$file_name.'"'); 
                echo $data;
            }else{
                new_backup_db_record($flag,$way,"deny req");
                echo "access_denied";
                die;
            }
            break;
        case "email":
            $file_path = $bu."/admin/backup/files/$file_name";
            $f = fopen($file_path,"w");
            fwrite($f,$data);
            fclose($f);
            $result = send_backup_mail("Tables",$flag,$file_name,$file_path);
            new_backup_db_record($flag,$way,$result[0]);
            if($result[0]){
                echo "رایانامه پشتیبانی با موفقیت ارسال شد.";
            }else{
                echo "خطایی رخ داده است.";
            }
            break; 
    }
}else{
    echo "خطایی رخ داده است. ورودی‌ها به درستی وارد نشده‌اند.";
}

?>
