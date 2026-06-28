<?php
$indexed = 1;
include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
include $bu."modules/cart/session_start.php";

if(isset($_GET["flag"]) && var_exist($_GET["flag"],"backup_files","flag") && isset($_GET["way"])){
    $way = $_GET["way"];
    $flag = $_GET["flag"];
    $data = "";
    $error = false;
    
    $file_name = "backup_files_".$flag."_".date("Y_m_d").".zip";
    $file_path = $bu."/admin/backup/files/$file_name";
    
    switch($flag){
        case "all":
            include $bu."admin/backup/sub_back_f_all.php";
            break;
        default:
            include $bu."admin/backup/sub_back_f_data.php";
            break;
    }
    if($error){
        echo $error;
        exit;
        die;
    }
    switch($way){
        case "download":
            if(isset($_SESSION["backup"]) && isset($_SESSION["a_logged"])){
                new_backup_db_record($flag,$way,1);
                header('Content-type: application/zip; charset=utf-8');
                header("Content-Transfer-Encoding: Binary");
                header('Content-Disposition: attachment; filename="'.$file_name.'"'); 
                readfile($file_path);
            }else{
                new_backup_db_record($flag,$way,"deny req");
                echo "access_denied";
                die;
            }
            break;
        case "email":
            // $file_name = "backup_files_".$flag."_".date("Y_m_d").".rar";
            $result = send_backup_mail("Files",$flag,$file_name,$file_path);
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
