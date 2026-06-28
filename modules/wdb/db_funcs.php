<?php
$dbc_adrs = "modules/wdb/db_connection.php";
function getVarFromDB($table,$var_name,$const_name,$const_val,$order_str = "",$offset = 0){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    if(!($mysqli instanceof mysqli)){
        return false;
    }
    if($order_str != "")$order_str = " ORDER BY ".$order_str." ";
    
    $st = "SELECT $var_name FROM $table WHERE $const_name = ? $order_str LIMIT 1 OFFSET $offset";
    $st = $mysqli->prepare($st);
    if(!$st){
        return false;
    }
    $st->bind_param('s',$const_val);
    if(!$st->execute()){
        return false;
    }
    $st->store_result();
    $st->bind_result($value);
    $st->fetch();
//        if($st->num_rows == 1){
        return $value;
//        }
//        return false;
}
function var_exist($value,$table,$var_name){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    if(!($mysqli instanceof mysqli)){
        return 0;
    }
    $st = "SELECT id FROM $table WHERE $var_name = ? LIMIT 1";
    $st = $mysqli->prepare($st);
    if(!$st){
        return 0;
    }
    $st->bind_param('s',$value);
    if(!$st->execute()){
        $st->close();
        return 0;
    }
    $st->store_result();
    if($st->num_rows == 1){
        $st->close();
        return 1;
    }
    $st->close();
    return 0;
}
function updateInDB($table,$var_name,$var_val,$const_name,$const_val){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    if(!($mysqli instanceof mysqli)){
        return false;
    }
    $st = "UPDATE $table SET $var_name = ? WHERE $const_name = ?";
    $st = $mysqli->prepare($st);
    if(!$st){
        return false;
    }
    $st->bind_param('ss',$var_val,$const_val);
    if(!$st->execute()){
        return false;
    }
    return True;
}
function CaSDate($timetext){
    include_once $GLOBALS['bu']."modules/jdf.php";
    if($timetext != ""){
        $date['day'] = jdate("d",(int)$timetext,"","local","en");
        $date['day_name'] = jdate("l",(int)$timetext,"","local","en");
        $date['month'] = jdate("m",(int)$timetext,"","local","en");
        $date['month_name'] = jdate("F",(int)$timetext,"","local","en");
        $date['year'] = jdate("Y",(int)$timetext,"","local","en");
        return $date;
    }
    return $timetext;
}
function correctDate_ts($timestamp){
    include_once $GLOBALS['bu']."modules/jdf.php";
    return jdate("d F Y",(int)$timestamp,"","local","en");
}
function correctDate_rqm_ts($timestamp){
    include_once $GLOBALS['bu']."modules/jdf.php";
    return jdate("Y/m/d",(int)$timestamp,"","local","en");
}
function correctDate_rqm_ts_woy($timestamp){
    include_once $GLOBALS['bu']."modules/jdf.php";
    return jdate("m/d",(int)$timestamp,"","local","en");
}
function correctDate_ts_woy($timestamp){
    include_once $GLOBALS['bu']."modules/jdf.php";
    return jdate("d F",(int)$timestamp,"","local","en");
}
function correctDate($timetext){
    include_once $GLOBALS['bu']."modules/jdf.php";
    if($timetext != ""){
        $timetext = strtotime($timetext);
        $timetext = correctDate_ts($timetext);
        return $timetext;
    }
    return $timetext;
}
function correctDate_rqm($timetext){
    include_once $GLOBALS['bu']."modules/jdf.php";
    if($timetext != ""){
        $timetext = strtotime($timetext);
        $timetext = jdate("Y/m/d",(int)$timetext,"","local","en");
        return $timetext;
    }
    return $timetext;
}
function create_tsDate($date){
    return create_ts($date["year"],$date["month"],$date["day"]);
}
function create_ts($year,$month,$day,$hour = 0,$minute = 0,$second = 0){
    include_once $GLOBALS['bu']."modules/jdf.php";
    $timeStamp = jmktime( $hour , $minute , $second , $month , $day , $year,"","local");
    return $timeStamp;
}

function cSQLTimefS($date){
    return createSQLTime($date["year"],$date["month"],$date["day"]);
}
function cSQLTimefS_hour($date,$hour = 0){
    return createSQLTime($date["year"],$date["month"],$date["day"],$hour);
}
function createSQLTime($year,$month,$day,$hour = 0,$minute = 0,$second = 0){
    include_once $GLOBALS['bu']."modules/jdf.php";
    $timeStamp = jmktime( $hour , $minute , $second , $month , $day , $year,"","local");
    $sqlTime = date("Y-m-d H:i:s",$timeStamp);
    return $sqlTime;
}
function db_time_now(){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    if(!($mysqli instanceof mysqli)){
        return time();
    }
    $st = "SELECT NOW();";
    $st = $mysqli->prepare($st);
    if(!$st){
        return time();
    }
    if(!$st->execute()){
        return time();
    }
    $res = 0;
    $st->store_result();
    $st->bind_result($res);
    $st->fetch();
    return strtotime($res);
}
function db_time_condition($table_field,$start_date="",$end_date="",$where_and=0){
    if($start_date){
        $sql_timestamp_str = cSQLTimefS($start_date);
        $where = " $table_field > '$sql_timestamp_str'";
    }
    if($end_date){
        $sql_timestamp_str = cSQLTimefS($end_date);
        
        if(isset($where))$where .= " AND ";else $where ="";
        $where .= " $table_field < '$sql_timestamp_str'";
    }
    if(isset($where)){
        if($where_and)$where = " AND $where";
        else $where = " WHERE $where";
    }else $where = "";
    return $where;
}


function monthNoDaysJ($month,$year){
    $month_end = false;
    switch($month){
        case 1:
        case 2:
        case 3:
        case 4:
        case 5:
        case 6:
            $month_end = 31;
            break;
        case 7:
        case 8:
        case 9:
        case 10:
        case 11:
            $month_end = 30;
            break;
        case 12:
            $mod = $year%128;
            if(($mod < 25 && $mod%4 === 0) || ($mod > 25 && $mod < 58 && ($mod-1)%4 === 0) || ($mod > 59 && $mod < 91 && ($mod-2)%4 === 0) || ($mod > 92 && $mod < 120 && ($mod-3)%4 === 0) || $mod == 124){$month_end = 30;}
            else{$month_end = 29;}
            break;
    }
    return $month_end;
}
function dayoWeekJ($day_name){
    switch($day_name){
        case "شنبه":
            $first_dof = 0;
            break;
        case "یکشنبه";
            $first_dof = 1;
            break;
        case "دوشنبه";
            $first_dof = 2;
            break;
        case "سه شنبه";
            $first_dof = 3;
            break;
        case "چهارشنبه";
            $first_dof = 4;
            break;
        case "پنجشنبه";
            $first_dof = 5;
            break;
        case "جمعه";
            $first_dof = 6;
            break;
        default:
            $first_dof = false;
            break;
    }
    return $first_dof;
}
function dayoWeekJ_date($year,$month,$day){
    $day_name = CaSDate(create_ts($year,$month,$day,12))["day_name"];
    return dayoWeekJ($day_name);
}

function cd_show_date($date_ts){
    include_once $GLOBALS['bu']."modules/jdf.php";
    $date_p1 = jdate("l d F",$date_ts,"","local","en");
    $date_p1 = str_replace(" شنبه","‌شنبه",$date_p1);
    $date_p1 = str_replace("شنبه","‌شنبه",$date_p1);
    return $date_p1;
}   

function cor_sendShift($shift){
    $tb = "sd_shifts";
    if(var_exist($shift,$tb,"id")){
        $tp_id = getVarFromDB($tb,"transporter_id","id",$shift);
        $shift_name = getVarFromDB($tb,"name","id",$shift);
        if($tp_id<3){
            return $shift_name;
        }
        $start_hour = getVarFromDB($tb,"start_hour","id",$shift);
        $end_hour = getVarFromDB($tb,"end_hour","id",$shift);
        return "$shift_name ($start_hour تا $end_hour)";
    }else{
        return false;
    }
}
function cor_sendShift_client($shift){
    $tb = "sd_shifts";
    if(var_exist($shift,$tb,"id")){
        $tp_id = getVarFromDB($tb,"transporter_id","id",$shift);
        if($tp_id == 1){
            return "";
        }
        if($tp_id == 2){
            return "ساعت هماهنگ شده";
        }
        $start_hour = getVarFromDB($tb,"start_hour","id",$shift);
        $end_hour = getVarFromDB($tb,"end_hour","id",$shift);
        return "ساعت $start_hour تا $end_hour";
    }else{
        return "";
    }
}


function correct_post_state_client($post_ref_id,$send_date="",$rec_date="",$rec_shift=""){
    if(strlen($post_ref_id) == 24){
        return "شماره رهگیری مرسوله <a class='color' href='https://newtracking.post.ir/' target='_blank'>پستی</a>: $post_ref_id";
    }else{
        $show_title = getVarFromDB("transporters","show_title","id",$post_ref_id);
        if($show_title){
            $show_title = " توسط $show_title";
        }
        $date = $rec_date;
        if(!$date)$date = $send_date;
        if(!$date)$date = "";
        else $date = "روز $date";
        $rec_shift = cor_sendShift_client($rec_shift);
        if($rec_shift)$rec_shift = "در $rec_shift";
        return "مرسوله $rec_shift $date$show_title ارسال شده است.";
    }
}
function correct_rec_time_client($rec_date="",$rec_shift=NULL){
    if($rec_shift){
        $tp_id = getVarFromDB("sd_shifts","transporter_id","id",$rec_shift);
        if($tp_id == 1){
            $res = "روش ارسال: ".cor_sendShift($rec_shift);
            if($rec_date){
                $res .= " - تاریخ ارسال: ".$rec_date;
            }
            return $res;
        }
        if($tp_id == 2){
            if(!$rec_date){
                return "روش ارسال: پیک ".cor_sendShift($rec_shift)." - با شما هماهنگ خواهد شد.";
            }
        }
    }
    $rec_shift = cor_sendShift_client($rec_shift);
    return "زمان تحویل: $rec_shift روز $rec_date";
}


function fileExtention_e($dir,$check_str,$n){
        if(is_dir($dir)){
            if($dh = opendir($dir)){
                while (($file=readdir($dh))!== false){
                    $j = strpos($file,".");
                    if(strcmp(substr($file,0,$j),$check_str.$n) == 0){
                        $ex = substr($file,$j+1); 
                        return $ex;
                    }
                }
                closedir($dh);
            }
        }
        return false;
    }
    
    
function make_ready_keywords($kws){
    $kws .= ",";
    $res = "";
    while($kws != ""){
        $res .= "، ";
        $pos = stripos($kws,",");
        $kw = substr($kws,0,$pos);
        $kws = substr($kws,$pos+1);
        $res .='<a class="srch_k">'.$kw.'</a>';
    }
    $res = substr($res,2);
    return $res;
    
}
function last_id($table){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $st = "SELECT id FROM $table ORDER BY id DESC LIMIT 1";
    $st = $mysqli->prepare($st);
    $st->execute();
    $res = 0;
    $st->store_result();
    $st->bind_result($res);
    $st->fetch();
    
    return $res;
}
function table_rows($table,$dlf="",$shf=""){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $lid = last_id($table);
    $k = 0;
    $i = 1;
    if($dlf === ""){
        if($shf != ""){
            while($i<=$lid){
                
                if(var_exist($i,$table,"id") && getVarFromDB($table,"show_flag","id",$i) == $shf){$k++;}
                $i++;
            }
            return $k;
            
        }
        while($i<=$lid){
            
            if(var_exist($i,$table,"id")){$k++;}
            $i++;
        }
        return $k;
    }
    if($shf == ""){
        while($i<=$lid){
            if(var_exist($i,$table,"id") && getVarFromDB($table,"del_flag","id",$i) == $dlf){$k++;}
            $i++;

        }
        return $k;
            
    }
    while($i<=$lid){
        
        if(var_exist($i,$table,"id") && getVarFromDB($table,"show_flag","id",$i) == $shf && getVarFromDB($table,"del_flag","id",$i) == $dlf){$k++;}
        $i++;
    }
    return $k;

    
}

function get_str_index($str,$depart,$key=1,$int=0){
    $str = $str.$depart;
    $str = trim($str);
    $str = str_replace(" ".$depart,$depart,$str);
    $str = str_replace($depart." ",$depart,$str);
    $str = str_replace($depart.$depart,$depart,$str);
    $no = substr_count($str,$depart);
    $array = array();
    $index = null;
    for($i=0;$i<$no;$i++){
        $pos = stripos($str,$depart);
        $array[$i] = substr($str,0,$pos);
        $str = substr($str,$pos+1);
        if($int == 1){
            $array[$i] = (int)$array[$i];
        }
        if($key == $array[$i]){
            $index = $i;
        }
    }
    return [$index,$array];
}
function check_value($format,$input){
    $arabic_nums = ["٠","١","٢","٣","٤","٥","٦","٧","٨","٩"];
    $persian_nums = ["۰","۱","۲","۳","۴","۵","۶","۷","۸","۹"];
    $english_nums = ["0","1","2","3","4","5","6","7","8","9"];

    for($i=0;$i<10;$i++){
      $input = str_replace($arabic_nums[$i],$english_nums[$i],$input);
      $input = str_replace($persian_nums[$i],$english_nums[$i],$input);
    }
    switch($format){
        case "text":
            if(strlen($input)>0){
                return $input;
            }
            break;
        case "email":
//            $input = filter_var($input,FILTER_SANITIZE_EMAIL);
//            return filter_var($input,FILTER_VALIDATE_EMAIL);
            if(strlen($input)>0){
                return $input;
            }
            break;

        case "checkbox":
            if($input === "yes"){
                return $input;
            }
            break;
        case "tel":
            return check_number($input,11);
            break;
        return null;
    }
}
function check_number($number_str,$length=""){
    $number_str = str_replace(" ","",$number_str);
    $number_str = (string)$number_str;
    for($i=0;$i<strlen($number_str);$i++){
        $k = $number_str[$i];
        if($k != "0"){
            if((int)$k == 0){
                return null;
            }
        }
    }
    if($length != ""){
        if($length > strlen($number_str)){
            return null;
        }
    }
    return $number_str;
} 
function checkFileType($file_format){
    switch ($file_format){
        case "jpg":
        case "jpeg":
        case "png":
        case "gif":
        case "tif":
        case "tiff":
            return "Image";
        case "avi":
        case "flv":
        case "mp4":
        case "wmv":
        case "mov":
        case "m4v":
        case "mpg":
        case "3gp":
            return "Video";
        case "3gp":
        case "aiff":
        case "m4a":
        case "mp3":
        case "ogg":
        case "wav":
        case "wma":
        case "webm":
        case "amr":
        case "au":
        case "awb":
            return "Audio";
        case "pdf":
        case "doc":
        case "docx":
        case "xls":
        case "xlsx":
            return "Doc";
        case "stl":
        case "obj":
        case "sldprt":
        case "prt":
        case "sldasm":
        case "asm":
        case "igs":
        case "iges":
        case "step":
        case "stp":
        case "wrl":
        case "3dm":
            return "Object";
        case "drw":
        case "slddrw":
            return "Drawing";
        default:
            return false;
    }
    return false;
}
function iconClass($fileType){
    switch($fileType){
            case "Video":
                return "icon-vid";
            case "Audio":
                return "icon-oud";
            case "Doc":
                return "icon-doc";
            case "Object":
                return "icon-3d";
            case "Drawing":
                return "icon-f";
        }
    return false;
}



require_once($bu.'modules/phpmailer/PHPMailer.php');
require_once($bu.'modules/phpmailer/SMTP.php');
require_once($bu.'modules/phpmailer/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;



function send_mail($from,$subject,$body,$addresses,$files){
    if(!defined('EXTERNAL_INTEGRATIONS_ENABLED') || !EXTERNAL_INTEGRATIONS_ENABLED){
        return [false, "Email disabled in showcase mode"];
    }
    $bu = $GLOBALS['bu'];

    $mail = new PHPMailer();
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($from["email"],$from["name"]);
    $mail->Subject   = $subject;
    $mail->Body      = $body;
    foreach($addresses as $address){
        $mail->addAddress($address);
    }
    foreach($files as $file){
        $mail->AddAttachment($file["path"],$file["name"]);
    }
    if($mail->send()){
        return [true];
    }else{
        return [false,$mail->ErrorInfo];
    }
}

function send_backup_mail($state,$type,$file_name,$file_path){
    
    $from["name"] = "Aban Fruit Back Up";
    $from["email"] = "backup@abanfruit.com";
    $subject = "$state Back Up - $type - ".date("Y_m_d");
    $state = strtolower($state);
    $body = "
    <body dir='rtl'>
    سلام،<br>
    به پیوست فایل پشتیبان ".getVarFromDB("backup_$state","name","flag",$type)." که در روز ".correctDate_ts(time())." ایجاد شده است، تقدیم می‌گردد.<br>
    با تشکر<br>
    پشتیبان‌گیری خودکار<br>
    تیم فنی میوه خشک آبان<br>
    </body>
    ";
    $addresses = array("admin@example.com");
    $files = array(
        array("name"=>$file_name,"path"=>$file_path),
    );
    return send_mail($from,$subject,$body,$addresses,$files);
}

function send_sale_mail($subject,$name,$email,$file_name,$file_path){
    
    $from["name"] = "میوه خشک آبان | فروش";
    $from["email"] = "sales@abanfruit.com";

    $body = "
    <body dir='rtl' >
    سلام $name عزیز،<br>
    
    چه خوبه که قراره بهارتون رنگی بشه با میوه های آبان قشنگمون☺️♥️<br>
    <br>
حواستون هست فروش نوروزی میوه خشک آبان شروع شده؟
    <br>
برای ارسال های تهران همه روزهای ارسال اسفند باز هستند و می تونید زمان ارسالو خودتون انتخاب کنید، تعداد ارسال هر روز محدود هست و به محض پر شدن ظرفیت از لیست انتخاب زمان ارسال حذف میشه.<br>
برای سفارش های خارج از تهران با توجه به زمان آماده سازی و پست برای پست پیشتاز حدودا ۷ روز و پست سفارشی حدودا ۹ روز  زمان میبره تا سفارش ها برسن.
    <br><br>
حتى تو اين روزاى شلوغ حواسمون به تک تک مراحل آماده کردن میوه های قشنگمون هست که تو ذهنتون واسه همیشه خوشمزه ترین بمونیم😌🌱
اعتمادتون خیلی ارزشمنده برامون...♥️.
    <br><br>
    

براى ثبت سفارش به راحتی مي‌تونيد از 
<a href='https://abanfruit.com/?lksr=eid1400' >سايت آبان</a>
اقدام كنيد يا به ادمين مهربونمون پيام بديد☺️
    
    <br><br>میوه خشک آبان<br>
    <a href='https://abanfruit.com/?lksr=eid1400' >abanfruit.com</a><br>
    
    </body>
    ";
    $addresses = array("$email");
    $files = array(
        array("name"=>$file_name,"path"=>$file_path),
    );
    return send_mail($from,$subject,$body,$addresses,$files);
}



function new_backup_db_record($type,$way,$status){
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
        $ipAddress = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
    }
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    if($status)$status = 1;
    $st = "INSERT INTO backup_records (type,way,status,ip) VALUES (?,?,?,?)";
    $st = $mysqli->prepare($st);
    $st->bind_param('ssss',$type,$way,$status,$ipAddress);
    if($st->execute()){return true;}
    return false;
}

function check_state($module,$state){
    $state_flags = getVarFromDB("admin_modules","state_flags","flag",$module);
    if(stripos($state_flags,$state) === false){
        return false;
    }
    return true;
}
function get_ms_name($module,$state){
    // Get Module State Name 
    $state_flags = explode(",",getVarFromDB("admin_modules","state_flags","flag",$module));
    $state_names = explode(",",getVarFromDB("admin_modules","state_names","flag",$module));
    return array_combine($state_flags,$state_names)[$state];
}
function get_ms_flag($module,$index){
    $state_flags = explode(",",getVarFromDB("admin_modules","state_flags","flag",$module));
    $flag = $state_flags[$index];
    return $flag;
}


function return_sel_sql($st){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $st = $mysqli->prepare($st);
    $st->execute();
    $res = $st->get_result();
    return $res;
}
?>
