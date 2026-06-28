<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
include_once $bu."modules/cart/cart_funcs.php";

class logged{
    public $uid;
    private $level;
    public $login_str;
    public $name;
    public function __construct($uid,$log_data){
        $this->uid = $uid;
        $this->level = 0;
        $this->login_str = hash('sha512',$log_data["create_code"].$_SERVER['HTTP_USER_AGENT'].$uid.$_SERVER['REMOTE_ADDR']);
        return;
    }
    public function check_login_str($new_login_str){
        if($this->login_str == $new_login_str)return true;
        return false;
    }
    public function get_level(){
        return $this->level;
    }
    public function set_level($lvl){
        $this->level = $lvl;
    }
    public function set_name($name){
        $this->name = $name;
        return;
    }
}
class a_logged{
    public $uid;
    private $level;
    private $access_str;
    public $login_str;
    public $name;
    public function __construct($uid,$hash_data){
        $table = "admins";
        $this->uid = $uid;
        $this->level = 0;
        $this->login_str = hash('sha512',$hash_data.$_SERVER['HTTP_USER_AGENT'].$uid.$_SERVER['REMOTE_ADDR']);
        
        $lvl = getVarFromDB($table,"level","id",$this->uid);
        $name = getVarFromDB($table,"name","id",$this->uid);
        $a_str = getVarFromDB($table,"access_str","id",$this->uid);
        
        $this->set_level($lvl);
        $this->set_name($name);
        $this->set_access($a_str);
        
        updateInDB($table,"last_login",date("Y-m-d H:i:s"),"id",$uid);
        return;
    }
    public function check_login_str($new_login_str){
        if($this->login_str == $new_login_str)return true;
        return false;
    }
    public function get_level(){
        return $this->level;
    }
    public function set_level($lvl){
        $this->level = $lvl;
    }
    public function set_access($str){
        $this->access_str = $str;
    }
    public function check_access($module){

        if (!is_string($this->access_str) || !is_string($module) || $module === '') return false;

        // Normalize whitespace/newlines in access_str so pasted values still match.
        $normalized = str_replace(["\r", "\n", "\t"], '', $this->access_str);
        $parts = explode(',', $normalized);
        foreach ($parts as $p) {
            if (trim($p) === $module) return true;
        }

        return false;

    }
    public function set_name($name){
        $this->name = $name;
        return;
    }
}
function login($log_data){
    $uid = db_check_user($log_data);
    if($uid){
        $_SESSION["logged"] = new logged($uid,$log_data);
        session_regenerate_id();
        write_login_attempt($log_data,0);
        $_SESSION["user"] = db_get_user($uid);
        updateInDB("users","last_login",date("Y-m-d H:i:s"),"id",$uid);
        updateInDB("users_password_attempt","state",1,"tel",$log_data["tel"]);
        return true;
    }else{
        unset($_SESSION["user"]);
        write_login_attempt($log_data,1);
        return false;
    } 
}

function a_login($user_data){
    $table = "admins";
    if(var_exist($user_data["user"],$table,"username")){
//        $db_pass = getVarFromDB($table,"password","username",$user_data["user"]);
        $db_pass_hash = getVarFromDB($table,"passhash","username",$user_data["user"]);
        $uid = getVarFromDB($table,"id","username",$user_data["user"]);
//        $data["email"] = $user_data["pass"];

//        if($db_pass == $user_data["pass"]){
        if(password_verify($user_data["pass"],$db_pass_hash)){
            $_SESSION["a_logged"] = new a_logged($uid,$db_pass_hash);
            session_regenerate_id();
            write_a_login_attempt($user_data,0);
            return true;
        }
    }
    return false;
}
function check_a_login(){
    $uid = $_SESSION["a_logged"]->uid;
    $table = "admins";
    if(var_exist($uid,$table,"id")){
//        $db_pass = getVarFromDB($table,"password","id",$uid);
        $db_pass_hash = getVarFromDB($table,"passhash","id",$uid);
        $new_login_str = hash('sha512',$db_pass_hash.$_SERVER['HTTP_USER_AGENT'].$uid.$_SERVER['REMOTE_ADDR']);
        if($_SESSION["a_logged"]->check_login_str($new_login_str)){
            session_regenerate_id();
            return true;
        }
    }
    logout();
    return false;
}


function logout(){
    if(isset($_SESSION["logged"])){
        cart_sync_draft_for_logged_user();
    }
    unset($_SESSION);
    session_destroy();
    header("Location:".$GLOBALS["s"]);
    die;
}

function check_login(){
    $uid = $_SESSION["logged"]->uid;
    $tb = "users";
    $tb2 = "users_password_attempt";
    
    if(var_exist($uid,$tb,"id")){
        $tel = getVarFromDB($tb,"tel","id",$uid);
        $create_code = getVarFromDB($tb2,"code","tel",$tel,"id DESC");
        $new_login_str = hash('sha512',$create_code.$_SERVER['HTTP_USER_AGENT'].$uid.$_SERVER['REMOTE_ADDR']);
        if($_SESSION["logged"]->check_login_str($new_login_str)){
            session_regenerate_id();
            return true;
        }
    }
    logout();
    return false;
}

function write_login_attempt($email,$state){
//    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
//    $tb = "user_login_attempts";
//    $st = "INSERT INTO $tb (user,state,http_client_ip,http_frwd,rmt_adrs) VALUES (?,?,?,?,?)";
//    $st = $mysqli->prepare($st);
//    $st->bind_param('sssss',$email,$state,$_SERVER['HTTP_CLIENT_IP'],$_SERVER['HTTP_X_FORWARDED_FOR'],$_SERVER['REMOTE_ADDR']);
//    if(!$st->execute()){
//        echo "E";
//        exit;
//    }
return;
}
function write_a_login_attempt($email,$state){}


function generate_code($length=6){
    $code = md5( time() . rand( 1000, 9999 ) );
    $code = str_replace(['+', '-'], '', filter_var($code, FILTER_SANITIZE_NUMBER_INT));
    $code = substr($code,0,$length);
    return $code;
}    
function db_new_user_attempt($tel,$code,$state=0,$sms=0){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
        $ipAddress = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
    }
    $browser = $_SERVER['HTTP_USER_AGENT'];
    
    if($code == "" || $code == "_"){
        $code = generate_code();
        if(var_exist($tel,"users","tel")){
            updateInDB("users","password",$code,"tel",$tel);
        }
        $sms = 0;
    }

    if($state == 0){
        $create_date = getVarFromDB("users_password_attempt","create_date","tel",$tel,"id DESC");
        $sql_timestamp = strtotime($create_date);
        $sql_now = db_time_now()-30;
        if($sql_now<$sql_timestamp)
            return "Eلطفا پس از لحظاتی مجددا اقدام نمایید";
    }
    
    $st = "INSERT INTO users_password_attempt (tel,code,ip,browser,state,sms_id) VALUES (?,?,?,?,?,?)";
    if($state == 0){
        if($sms === 0){
        $sms_state = send_sms_code($tel,$code);}
        else{
            $sms_state[1] = "old_user";
            $sms_state[0] = true;
        }
    }else{
        $sms_state[1] = "admin";
        $sms_state[0] = true;
    }
    
    $st = $mysqli->prepare($st);
    $st->bind_param('ssssss',$tel,$code,$ipAddress,$browser,$state,$sms_state[1]);
    if($st->execute() && $sms_state[0]){
        return "D";
    }
    return "Eخطایی رخ داده است، لطفا مجددا اقدام نمایید.";
}
function send_sms_code($tel,$code){
    return send_sms_temp($tel,"passcode",$code);
}
function send_sms_temp($tel,$template,$code1,$code2="",$code3=""){
    if(!defined('EXTERNAL_INTEGRATIONS_ENABLED') || !EXTERNAL_INTEGRATIONS_ENABLED){
        return [false, "SMS disabled in showcase mode"];
    }
    $url = getenv('SMS_API_URL') ?: 'https://example.invalid/disabled';
    $api_key = getenv('SMS_API_KEY') ?: 'not-configured';
    
    $data = array(
        'type' => "1", 
        'Receptor' => "$tel",
        'template' => "$template",
        'param1'=>"$code1"
        );
    if($code2 != ""){$data['param2'] = $code2;}
    if($code3 != ""){$data['param3'] = $code3;}
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("apikey: $api_key"));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if($err) {return [false,"$err"];}
    if(stripos($res,'"code":200')>= 0){
        $msg_id = substr($res,stripos($res,'[')+1);
        $msg_id = substr($msg_id,0,stripos($msg_id,']'));
        return [true,$msg_id];
    }
    $msg_error = substr($res,stripos($res,'"message":"')+11);
    $msg_error = substr($msg_error,0,stripos($msg_error,'"'));
    return [false,$msg_error];
}


function send_sms($tel,$message){
    if(!defined('EXTERNAL_INTEGRATIONS_ENABLED') || !EXTERNAL_INTEGRATIONS_ENABLED){
        return [false, "SMS disabled in showcase mode"];
    }
    $url = getenv('SMS_API_URL') ?: 'https://example.invalid/disabled';
    $api_key = getenv('SMS_API_KEY') ?: 'not-configured';
    
    $data = array('message' => "$message", 'Receptor' => "$tel");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array("apikey: $api_key"));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    
    if($err) {return [false,"$err"];}
    if(stripos($res,'"code":200')>= 0){
        $msg_id = substr($res,stripos($res,'[')+1);
        $msg_id = substr($msg_id,0,stripos($msg_id,']'));
        return [true,$msg_id];
    }
    $msg_error = substr($res,stripos($res,'"message":"')+11);
    $msg_error = substr($msg_error,0,stripos($msg_error,'"'));
    return [false,$msg_error];
}
?>
<?php
        }
    }
?>
