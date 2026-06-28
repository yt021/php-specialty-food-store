<?php
$dbc_adrs = "modules/wdb/db_connection.php";
function getVarFromDB($table,$var_name,$const_name,$const_val,$order_str = "",$offset = 0){
        include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
        if($order_str != "")$order_str = " ORDER BY ".$order_str." ";
        
        $st = "SELECT $var_name FROM $table WHERE $const_name = ? $order_str LIMIT 1 OFFSET $offset";
        $st = $mysqli->prepare($st);
        $st->bind_param('s',$const_val);
        if(!$st->execute()){
            echo "E";
            exit;
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
        $st = "SELECT id FROM $table WHERE $var_name = ? LIMIT 1";
        $st = $mysqli->prepare($st);
        $st->bind_param('s',$value);
        $st->execute();
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
    $st = "UPDATE $table SET $var_name = ? WHERE $const_name = ?";
    $st = $mysqli->prepare($st);
    $st->bind_param('ss',$var_val,$const_val);
    if(!$st->execute()){
        return false;
    }
    return True;
}
function correctDate($timetext){
    include_once $GLOBALS['bu']."modules/jdf.php";
    if($timetext != ""){
        $timetext = strtotime($timetext);
        $timetext = jdate("d F Y",(int)$timetext,"","local","en");
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

function createSQLTime($year,$month,$day,$hour = 0,$minute = 0,$second = 0){
    include_once $GLOBALS['bu']."modules/jdf.php";
    $timeStamp = jmktime( $hour , $minute , $second , $month , $day , $year,"","local");
    $sqlTime = date("Y-m-d H:i:s",$timeStamp);
    return $sqlTime;
}
function db_time_now(){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $st = "SELECT NOW();";
    $st = $mysqli->prepare($st);
    $st->execute();
    $res = 0;
    $st->store_result();
    $st->bind_result($res);
    $st->fetch();
    return strtotime($res);
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
?>
