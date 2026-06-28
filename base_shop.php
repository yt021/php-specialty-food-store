<?php
    // Public showcase safety policy. External services are intentionally disabled.
    if(!defined('SHOWCASE_MODE')) define('SHOWCASE_MODE', true);
    if(!defined('EXTERNAL_INTEGRATIONS_ENABLED')) define('EXTERNAL_INTEGRATIONS_ENABLED', false);
    if(!function_exists('asset_url')){
        function asset_url($path, $fallback_version = '1')
        {
            $path = ltrim((string)$path, "/\\");
            $base_url = isset($GLOBALS['s']) ? (string)$GLOBALS['s'] : '';
            $base_path = isset($GLOBALS['bu']) ? rtrim((string)$GLOBALS['bu'], "\\/") . DIRECTORY_SEPARATOR : '';
            $version = (string)$fallback_version;
            if($base_path !== ''){
                $full_path = $base_path . str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
                if(is_file($full_path)){
                    $mtime = @filemtime($full_path);
                    if($mtime){
                        $version = (string)$mtime;
                    }
                }
            }
            return $base_url . $path . '?v=' . rawurlencode($version);
        }
    }

    if(isset($indexed)){
        if($indexed == 1){
 ?>
<?php
//    ini_set('session.cache_limiter','private');
//    session_cache_limiter(false);
    // header("Cache-Control: max-age:1296000");
    header("Cache-Control:no-cache,must-revalidate");
    
    $URL = $_SERVER['SCRIPT_NAME'];

    $main_folder = substr($URL,1);
    $main_folder = substr($URL,0,stripos($main_folder,"/")+1);
    $main_folder = "";
    // public_html was moved to the repository root.
    $bu = rtrim(__DIR__, "\\/").DIRECTORY_SEPARATOR;   //base filesystem path
    
    $file_name=basename($URL);
    $file_name = substr($file_name,0,stripos($file_name,"."));
//    module name
    $module_name = substr($URL,1);
    $first = stripos($module_name,"/");
    // $second = stripos($module_name,"/",$first+1);
    // $first = $first+1;
    // $module_name = substr($module_name,$first,$second-$first);
    $module_name = substr($module_name,0,$first);
    
    $i = substr_count($URL,"/");   //number of nested folders from bu to file
    $s = "";
    if($i>1){
        for($j=1;$j<$i;$j++){
            $s = $s."../";    
        }
    }
    
    $cf = basename(getcwd());

    if(!function_exists("price_sep")){
        function price_sep($value){
            if($value === null || $value === ""){
                return $value;
            }
            if(is_string($value)){
                $value = str_replace(array(","," "), "", $value);
            }
            if(!is_numeric($value)){
                return $value;
            }
            return number_format((float)$value,0,'.',',');
        }
    }
    
?>
<?php
        }
    }
?>
