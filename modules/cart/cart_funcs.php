<?php
//$bu = "../../";
include_once $bu."modules/wdb/db_connection.php";
include_once $bu."modules/wdb/db_funcs.php";

class cart{
    public $orders = array();
    public function add_order($pid,$weight,$admin_state = 0){
        if(getVarFromDB("products","category","id",$pid) == "غیر قابل فروش"){
            return;
        }
        $o = sizeof($this->orders);
        $f = $this->find_order($pid,$weight);
        if($f>-1){
            $this->orders[$f]->no++;
            return;
        }
        if(getVarFromDB("products","type","id",$pid) == "box"){
            $this->orders[$o] = new order_box($pid,$weight,$admin_state);
        }else if(getVarFromDB("products","type","id",$pid) == "pack"){
        $this->orders[$o] = new order($pid,$weight,$admin_state);
        }
        return;
    }
    public function find_order($pid,$weight,$opbid=""){
        $o = sizeof($this->orders);
        $f = 0;
        for($i=0;$i<$o;$i++){
            $f += $this->orders[$i]->is_pid($pid)*$this->orders[$i]->is_weight($weight)*$this->orders[$i]->is_opbid($opbid)*($i+1);
        }
        return $f-1;  
    }
    public function delete_order($pid,$weight,$opbid=""){
        $o = sizeof($this->orders);
        $f = $this->find_order($pid,$weight,$opbid);
        if($f>-1){
            for($i = $f;$i<$o-1;$i++){
                $this->orders[$i] = $this->orders[$i+1];
                
            }
            unset($this->orders[$i]);
        }
        return;
    }
    public function change_no_order($pid,$weight,$opbid,$add){
        $f = $this->find_order($pid,$weight,$opbid);
        if($f>-1){
            
            $this->orders[$f]->no += $add;
            if($this->orders[$f]->no < 1){

                $this->delete_order($pid,$weight,$opbid);
            }
        }
        return;
    }
    public function change_weight_order($pid,$old_weight,$new_weight){
        if($old_weight == $new_weight)return ;
        $o = sizeof($this->orders);
        $f = $this->find_order($pid,$old_weight);
        if($f>-1){
            $fn = $this->find_order($pid,$new_weight);
            if($fn>-1){
                $this->orders[$fn]->no += $this->orders[$f]->no;
                $this->delete_order($pid,$old_weight);
            }else{
                $this->orders[$f]->change_w($new_weight);
            }
        }
        return;
    }
    public function number(){
        $o = sizeof($this->orders);
        $noo = 0;
        for($i = 0;$i<$o;$i++){
            $noo += $this->orders[$i]->no;
        }
        return $noo;
    }
    public function weight(){
        $o = sizeof($this->orders);
        $otw = 0;
        for($i = 0;$i<$o;$i++){
            $otw += ($this->orders[$i]->no* $this->orders[$i]->weight());
        }
        return $otw;
    }
    public function price(){
        $o = sizeof($this->orders);
        $otp = 0;
        for($i = 0;$i<$o;$i++){
            $otp += ($this->orders[$i]->no* $this->orders[$i]->price());
            if(isset($_SESSION["a_logged"])){ 
                // echo $otp;
            }
        }
        return $otp;
    }
    public function has_category($category){
        $cat_no = 0;
        foreach($this->orders as $order){
            if($order->is_category($category))$cat_no++;
        }
        return $cat_no;
    }
}

class order{
    public $pid;
    public $no = 1;
    public $weight = 50;
    public function __construct($pid,$weight,$admin_state = 0,$type=""){
        if(var_exist($pid,"products","id") && getVarFromDB("products","state","id",$pid) <= $admin_state && getVarFromDB("products","del_flag","id",$pid) == 0 && ($type == "" || $type == getVarFromDB("products","type","id",$pid))){
            $this->pid = $pid;
            $this->change_w($weight);
        }
    }
    public function is_opbid($opbid){
         $type = getVarFromDB("products","type","id",$this->pid);
         switch($type){
             case "pack":
             case "piece":
                return 1;
                break;
             case "box":
                if($this->opbid() == $opbid)
                return 1;
                break;
         }
         return 0;
    }
    public function opbid(){
        return "";
    }
    public function is_pid($new_pid){
      if($new_pid == $this->pid){return 1;}
      return 0;
    }
    public function is_weight($w){
      if($w == $this->weight()){return 1;}
      return 0;
    }
    public function is_category($cat){
        if(getVarFromDB("products","category","id",$this->pid) == $cat)
        return 1;
        return 0;
    }
    public function name(){
      return getVarFromDB("products","name","id",$this->pid);
    }
    public function change_w($w){
        $w_str = getVarFromDB("products_price","weight","pid",$this->pid,"start_time DESC");
        if(get_str_index($w_str,",",$w,1)[0] != null){
            $this->weight = $w;
            return 1;
        }
        else{
            $this->weight = get_str_index($w_str,",")[1][0];
        }
        return 0;
    }
    public function price(){
        $weight_str = getVarFromDB("products_price","weight","pid",$this->pid,"start_time DESC");
        $price_str = getVarFromDB("products_price","price","pid",$this->pid,"start_time DESC");
        $index = get_str_index($weight_str,",",$this->weight,1)[0];
        $price = get_str_index($price_str,",")[1][$index];
        $discount = product_discount_get_active($this->pid);
        if($discount !== false){
            $price = product_discount_apply_to_weight_price($price,$this->weight,$discount);
        }
        return $price;
    }
    public function weight(){
        return $this->weight;
    }
}
class box_cart extends cart{
    public $capacity = 1;
    public function __construct($cap){
        $this->capacity = $cap;
    }
    public function add_order($pid,$weight,$admin_state = 0){
        if($this->capacity > $this->number()){
            $o = sizeof($this->orders);
            $f = $this->find_order($pid,$weight);
            if($f>-1){
                $this->orders[$f]->no++;
                return;
            }
            if(getVarFromDB("products","type","id",$pid) == "piece"){
                $this->orders[$o] = new order($pid,$weight,$admin_state);
            }
        }
        return;
    }
    public function change_no_order($pid,$weight,$add,$opbid=""){
        if($this->number()+$add > $this->capacity){return;}
        $f = $this->find_order($pid,$weight);
        if($f>-1){
            $this->orders[$f]->no += $add;
            if($this->orders[$f]->no < 1){
                $this->delete_order($pid,$weight,$opbid);
            }
        }
        return;
    }
    public function list_items($unit){
        $list_str = "";
        $o = sizeof($this->orders);
        for($i = 0;$i<$o;$i++){
            $list_str .= " ".$this->orders[$i]->name().": ".$this->orders[$i]->no." $unit".' - ';
            //(".$this->orders[$i]->weight." گرمی)
        }
        return $list_str;
    }
    public function opbid(){
        $opbid = "";
        $o = sizeof($this->orders);
        for($i = 0;$i<$o;$i++){
            $opbid .= "_".$this->orders[$i]->pid;
        }
        return $opbid;
    }
}
class order_box extends order{
    public $capacity;
    public $content;
    public function __construct($pid,$weight,$admin_state = 0,$type=""){
        if(var_exist($pid,"products","id") && getVarFromDB("products","state","id",$pid) <= $admin_state && getVarFromDB("products","del_flag","id",$pid) == 0 && ($type == "" || $type == getVarFromDB("products","type","id",$pid))){
            $this->pid = $pid;
            $this->change_w($weight);
            $this->capacity = (int)$this->weight;
            $this->content = new box_cart($this->capacity);
        }
    }
    public function opbid(){
        return $this->pid.$this->content->opbid();
    }
    public function price($box=false){
        $weight_str = getVarFromDB("products_price","weight","pid",$this->pid,"start_time DESC");
        $price_str = getVarFromDB("products_price","price","pid",$this->pid,"start_time DESC");
        $index = get_str_index($weight_str,",",$this->weight,1)[0];
        $box_price = get_str_index($price_str,",")[1][$index];
        $discount = product_discount_get_active($this->pid);
        if($discount !== false){
            $box_price = product_discount_apply_to_weight_price($box_price,$this->weight,$discount);
        }
        if($box){
            return $box_price;
        }
        $content_price = $this->content->price();
        $price = $box_price+$content_price;
        return $price;
    }
    public function weight(){
        return $this->content->weight();
    }
}


function cart_draft_days(){
    return 3;
}

function cart_draft_table(){
    return "user_cart_drafts";
}

function cart_draft_ensure_table(){
    static $is_ready = false;
    if($is_ready){
        return true;
    }
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $tb = cart_draft_table();
    // First try to use existing table to avoid requiring CREATE privilege on every request.
    $st = $mysqli->prepare("SELECT uid FROM $tb LIMIT 1");
    if($st && $st->execute()){
        $is_ready = true;
        return true;
    }
    $st = "CREATE TABLE IF NOT EXISTS $tb (
            uid BIGINT(20) NOT NULL,
            cart_json LONGTEXT NOT NULL,
            updated_at DATETIME NOT NULL,
            expires_at DATETIME NOT NULL,
            PRIMARY KEY (uid),
            KEY expires_at (expires_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $st = $mysqli->prepare($st);
    if(!$st){
        return false;
    }
    if(!$st->execute()){
        return false;
    }
    $is_ready = true;
    return true;
}

function cart_draft_snapshot_from_cart($cart_obj){
    $snapshot = array();
    if(!is_object($cart_obj) || !isset($cart_obj->orders) || !is_array($cart_obj->orders)){
        return $snapshot;
    }
    foreach($cart_obj->orders as $item){
        if(!is_object($item) || !isset($item->pid)){
            continue;
        }
        $row = array(
            "pid" => (int)$item->pid,
            "weight" => (int)$item->weight,
            "no" => max(1,(int)$item->no),
            "type" => "pack"
        );
        if(is_a($item,"order_box")){
            $row["type"] = "box";
            $row["content"] = array();
            if(isset($item->content) && isset($item->content->orders) && is_array($item->content->orders)){
                foreach($item->content->orders as $c_item){
                    if(!is_object($c_item) || !isset($c_item->pid)){
                        continue;
                    }
                    $row["content"][] = array(
                        "pid" => (int)$c_item->pid,
                        "weight" => (int)$c_item->weight,
                        "no" => max(1,(int)$c_item->no)
                    );
                }
            }
        }
        $snapshot[] = $row;
    }
    return $snapshot;
}

function cart_draft_cart_from_snapshot_json($cart_json){
    $cart = new cart();
    if(!is_string($cart_json) || trim($cart_json) == ""){
        return $cart;
    }
    $snapshot = json_decode($cart_json,true);
    if(!is_array($snapshot)){
        return $cart;
    }

    foreach($snapshot as $row){
        if(!is_array($row) || !isset($row["pid"]) || !isset($row["weight"])){
            continue;
        }
        $pid = (int)$row["pid"];
        $weight = (int)$row["weight"];
        $no = 1;
        if(isset($row["no"])){
            $no = max(1,(int)$row["no"]);
        }

        $orders_before = sizeof($cart->orders);
        $cart->add_order($pid,$weight);
        $orders_after = sizeof($cart->orders);
        $f = -1;
        if($orders_after > $orders_before){
            $f = $orders_after - 1;
        }else{
            $f = $cart->find_order($pid,$weight);
        }

        if($no > 1 && $f > -1){
            if(isset($row["type"]) && $row["type"] == "box"){
                $cart->orders[$f]->no = $no;
            }else{
                $cart->change_no_order($pid,$weight,"",$no-1);
            }
        }

        if(isset($row["type"]) && $row["type"] == "box" && isset($row["content"]) && is_array($row["content"])){
            if($f > -1 && isset($cart->orders[$f]) && is_a($cart->orders[$f],"order_box")){
                foreach($row["content"] as $c_row){
                    if(!is_array($c_row) || !isset($c_row["pid"]) || !isset($c_row["weight"])){
                        continue;
                    }
                    $c_pid = (int)$c_row["pid"];
                    $c_weight = (int)$c_row["weight"];
                    $c_no = 1;
                    if(isset($c_row["no"])){
                        $c_no = max(1,(int)$c_row["no"]);
                    }
                    $cart->orders[$f]->content->add_order($c_pid,$c_weight);
                    if($c_no > 1){
                        $cart->orders[$f]->content->change_no_order($c_pid,$c_weight,$c_no-1);
                    }
                }
            }
        }
    }
    return $cart;
}

function cart_draft_clear_for_uid($uid){
    $uid = (int)$uid;
    if($uid < 1){
        return false;
    }
    if(!cart_draft_ensure_table()){
        return false;
    }
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $tb = cart_draft_table();
    $st = "DELETE FROM $tb WHERE uid = ?";
    $st = $mysqli->prepare($st);
    if(!$st){
        return false;
    }
    $uid_sql = (string)$uid;
    $st->bind_param('s',$uid_sql);
    if(!$st->execute()){
        return false;
    }
    return true;
}

function cart_draft_save_for_uid($uid,$cart_obj,$days = null){
    $uid = (int)$uid;
    if($uid < 1){
        return false;
    }
    if($days === null){
        $days = cart_draft_days();
    }
    $days = (int)$days;
    if($days < 1){
        $days = 1;
    }
    if(!cart_draft_ensure_table()){
        return false;
    }
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];

    $snapshot = cart_draft_snapshot_from_cart($cart_obj);
    $cart_json = json_encode($snapshot,JSON_UNESCAPED_UNICODE);
    if($cart_json === false){
        return false;
    }

    $updated_at = date("Y-m-d H:i:s");
    $expires_at = date("Y-m-d H:i:s",time() + ($days * 24 * 3600));
    $tb = cart_draft_table();
    $st = "INSERT INTO $tb (uid,cart_json,updated_at,expires_at) VALUES (?,?,?,?)
            ON DUPLICATE KEY UPDATE cart_json = VALUES(cart_json), updated_at = VALUES(updated_at), expires_at = VALUES(expires_at)";
    $st = $mysqli->prepare($st);
    if(!$st){
        return false;
    }
    $uid_sql = (string)$uid;
    $st->bind_param('ssss',$uid_sql,$cart_json,$updated_at,$expires_at);
    if(!$st->execute()){
        return false;
    }
    return true;
}

function cart_draft_load_for_uid($uid){
    $uid = (int)$uid;
    if($uid < 1){
        return false;
    }
    if(!cart_draft_ensure_table()){
        return false;
    }
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $tb = cart_draft_table();
    $now = date("Y-m-d H:i:s");

    $st = "SELECT cart_json FROM $tb WHERE uid = ? AND expires_at > ? LIMIT 1";
    $st = $mysqli->prepare($st);
    if(!$st){
        return false;
    }
    $uid_sql = (string)$uid;
    $st->bind_param('ss',$uid_sql,$now);
    if(!$st->execute()){
        return false;
    }
    $st->store_result();
    if($st->num_rows != 1){
        $st->close();
        return false;
    }
    $cart_json = "";
    $st->bind_result($cart_json);
    $st->fetch();
    $st->close();

    return cart_draft_cart_from_snapshot_json($cart_json);
}

function cart_restore_draft_for_logged_user(){
    if(!isset($_SESSION["logged"]) || !isset($_SESSION["logged"]->uid)){
        return false;
    }
    $uid = (int)$_SESSION["logged"]->uid;
    if($uid < 1){
        return false;
    }
    $restore_key = "cart_draft_restored_uid";
    if(isset($_SESSION[$restore_key]) && (int)$_SESSION[$restore_key] == $uid){
        return false;
    }
    if(!isset($_SESSION["cart"]) || !is_object($_SESSION["cart"])){
        $_SESSION["cart"] = new cart();
    }
    if($_SESSION["cart"]->number() > 0){
        $_SESSION[$restore_key] = $uid;
        return false;
    }

    $stored_cart = cart_draft_load_for_uid($uid);
    if($stored_cart !== false && $stored_cart->number() > 0){
        $_SESSION["cart"] = $stored_cart;
        $_SESSION[$restore_key] = $uid;
        return true;
    }

    $_SESSION[$restore_key] = $uid;
    return false;
}

function cart_sync_draft_for_logged_user($days = null){
    if(!isset($_SESSION["logged"]) || !isset($_SESSION["logged"]->uid)){
        return false;
    }
    $uid = (int)$_SESSION["logged"]->uid;
    if($uid < 1){
        return false;
    }
    if(!isset($_SESSION["cart"]) || !is_object($_SESSION["cart"])){
        $_SESSION["cart"] = new cart();
    }
    if($_SESSION["cart"]->number() > 0){
        return cart_draft_save_for_uid($uid,$_SESSION["cart"],$days);
    }
    return cart_draft_clear_for_uid($uid);
}

function cart_sale_normalize_type($type)
{
    $type = strtolower(trim((string)$type));
    switch($type){
        case "percentage":
            return "percent";
        case "fixed_amount":
            return "amount";
        case "whole_price":
        case "final_price":
            return "fixed_price";
        case "free_shipping":
        case "free_send":
            return "send";
    }
    return $type;
}

function cart_sale_parse_numeric($value, $default = 0.0)
{
    $value = (string)$value;
    $checked = check_value("text",$value);
    if($checked !== null){
        $value = (string)$checked;
    }
    $value = trim($value);
    if($value === ""){
        return (float)$default;
    }

    $value = str_replace(array(",", " ", "٬"), "", $value);
    $value = str_replace(array("٫", "،"), ".", $value);
    if(!preg_match('/-?\d+(?:\.\d+)?/',$value,$matches)){
        return (float)$default;
    }
    return (float)$matches[0];
}

function cart_discount_code_normalize($code)
{
    $code = (string)$code;
    $checked = check_value("text",$code);
    if($checked !== null){
        $code = (string)$checked;
    }
    // Normalize common Unicode variants that users can enter from RTL keyboards.
    $digit_map = array(
        "۰"=>"0","۱"=>"1","۲"=>"2","۳"=>"3","۴"=>"4",
        "۵"=>"5","۶"=>"6","۷"=>"7","۸"=>"8","۹"=>"9",
        "٠"=>"0","١"=>"1","٢"=>"2","٣"=>"3","٤"=>"4",
        "٥"=>"5","٦"=>"6","٧"=>"7","٨"=>"8","٩"=>"9"
    );
    $code = strtr($code,$digit_map);
    // Remove invisible bidi and zero-width marks that break exact comparisons.
    $code = preg_replace('/[\x{200C}\x{200D}\x{200E}\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}]/u','',$code);
    // Discount codes should not depend on accidental spaces between characters.
    $code = preg_replace('/\s+/u','',$code);
    $code = trim($code);
    if($code === ""){
        return "";
    }
    if(function_exists("mb_strtoupper")){
        return mb_strtoupper($code,"UTF-8");
    }
    return strtoupper($code);
}

function cart_discount_sale_time_is_active($start_date,$end_date,$timestamp = null)
{
    if($timestamp === null){
        $timestamp = time() + 2.5*3600;
    }
    $timestamp = (int)$timestamp;
    $start_date = trim((string)$start_date);
    if($start_date !== ""){
        $start_ts = strtotime($start_date);
        if($start_ts !== false && $start_ts > $timestamp){
            return false;
        }
    }
    $end_date = trim((string)$end_date);
    if($end_date !== ""){
        $end_ts = strtotime($end_date);
        if($end_ts !== false && $end_ts <= $timestamp){
            return false;
        }
    }
    return true;
}

function cart_discount_sale_row_effect_text($row,$send_applicable = true)
{
    if(!is_array($row)){
        return "";
    }
    $type = cart_sale_normalize_type(isset($row["type"]) ? $row["type"] : "");
    $amount = isset($row["amount"]) ? $row["amount"] : 0;
    $name = trim((string)(isset($row["name"]) ? $row["name"] : ""));
    switch($type){
        case "percent":
            $percent = (int)round(max(cart_sale_parse_numeric($amount,0),0));
            return ($name !== "" ? $name.": " : "").$percent."%";
        case "amount":
            return ($name !== "" ? $name.": " : "").price_sep((int)max(cart_sale_parse_numeric($amount,0),0))." تومان";
        case "fixed_price":
            return ($name !== "" ? $name.": " : "")."قیمت نهایی ".price_sep((int)max(cart_sale_parse_numeric($amount,0),0))." تومان";
        case "send":
            if(!$send_applicable){
                return "";
            }
            return ($name !== "" ? $name.": " : "")."ارسال رایگان";
        case "gift":
            $gift_id = (int)round(max(cart_sale_parse_numeric($amount,0),0));
            $gift_name = "";
            if($gift_id > 0 && var_exist($gift_id,"products","id")){
                $gift_name = (string)getVarFromDB("products","name","id",$gift_id);
            }
            if($gift_name !== ""){
                return ($name !== "" ? $name.": " : "")."هدیه ".$gift_name;
            }
            return ($name !== "" ? $name.": " : "")."هدیه";
    }
    return $name !== "" ? $name : "تخفیف";
}

function cart_discount_code_validate_for_cart($code,$mode,$uid,$cart_cost,$send_cost,$admin_state,$send_applicable,$options = array())
{
    $result = array(
        "ok" => false,
        "status" => "invalid",
        "message" => "کد تخفیف معتبر نیست.",
        "normalized_code" => "",
        "effects" => array(),
        "matched_count" => 0,
        "eligible_count" => 0
    );

    $normalized = cart_discount_code_normalize($code);
    $result["normalized_code"] = $normalized;
    if($normalized === ""){
        $result["status"] = "empty";
        $result["message"] = "کد تخفیف را وارد کنید.";
        return $result;
    }

    $ignore_cart_requirements = false;
    $ignore_send_applicable = false;
    if(is_array($options)){
        if(!empty($options["ignore_cart_requirements"])){
            $ignore_cart_requirements = true;
        }
        if(!empty($options["ignore_send_applicable"])){
            $ignore_send_applicable = true;
        }
    }

    $uid = (int)$uid;
    $cart_cost = (int)max(cart_sale_parse_numeric($cart_cost,0),0);
    $send_cost = (int)max(cart_sale_parse_numeric($send_cost,0),0);
    $admin_state = (int)$admin_state;
    $send_applicable = (bool)$send_applicable;

    $t_r_buy = 0;
    $n_buy = 0;
    if(!$ignore_cart_requirements && $mode === "new" && $uid > 0){
        $t_r_buy = (int)max(cart_sale_parse_numeric(getVarFromDB("orders","sum(pay_price)","uid",$uid),0),0);
        $n_buy = (int)max(cart_sale_parse_numeric(getVarFromDB("orders","count(pay_price)","uid",$uid),0),0);
    }

    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $st = $mysqli->prepare("SELECT id,name,type,amount,min_buy,min_t_buy,min_n_buy,start_date,end_date,lksr FROM sales WHERE del_flag = 0 AND admin_state <= ?");
    if(!$st){
        $result["status"] = "error";
        $result["message"] = "خطا در اعتبارسنجی کد تخفیف. لطفا مجدد تلاش کنید.";
        return $result;
    }

    $admin_state_sql = (int)$admin_state;
    $st->bind_param('i',$admin_state_sql);
    if(!$st->execute()){
        $result["status"] = "error";
        $result["message"] = "خطا در اعتبارسنجی کد تخفیف. لطفا مجدد تلاش کنید.";
        return $result;
    }

    $timestamp = time() + 2.5*3600;
    $has_inactive = false;
    $has_not_eligible = false;
    $res = $st->get_result();
    while($row = $res->fetch_assoc()){
        $row_code = cart_discount_code_normalize(isset($row["lksr"]) ? $row["lksr"] : "");
        if($row_code === "" || $row_code !== $normalized){
            continue;
        }

        $result["matched_count"]++;
        if(!cart_discount_sale_time_is_active(
            isset($row["start_date"]) ? $row["start_date"] : "",
            isset($row["end_date"]) ? $row["end_date"] : "",
            $timestamp
        )){
            $has_inactive = true;
            continue;
        }

        $min_buy = (int)(isset($row["min_buy"]) ? $row["min_buy"] : 0);
        $min_t_buy = (int)(isset($row["min_t_buy"]) ? $row["min_t_buy"] : 0);
        $min_n_buy = (int)(isset($row["min_n_buy"]) ? $row["min_n_buy"] : 0);
        if(!$ignore_cart_requirements && !($min_t_buy <= $t_r_buy && $min_n_buy <= $n_buy && $min_buy <= $cart_cost)){
            $has_not_eligible = true;
            continue;
        }

        $sale_type = cart_sale_normalize_type(isset($row["type"]) ? $row["type"] : "");
        if($sale_type === "send" && !$ignore_send_applicable && !$send_applicable){
            $has_not_eligible = true;
            continue;
        }

        $result["eligible_count"]++;
        $effect = cart_discount_sale_row_effect_text($row,$send_applicable);
        if($effect !== ""){
            $result["effects"][] = $effect;
        }
    }

    if($result["matched_count"] === 0){
        $result["status"] = "invalid";
        $result["message"] = "کد تخفیف معتبر نیست.";
        return $result;
    }

    if($result["eligible_count"] > 0){
        $result["ok"] = true;
        $result["status"] = "valid";
        $result["message"] = "کد تخفیف با موفقیت اعتبارسنجی شد.";
        return $result;
    }

    if($has_inactive){
        $result["status"] = "expired";
        $result["message"] = "کد تخفیف منقضی شده یا هنوز فعال نشده است.";
        return $result;
    }

    if($has_not_eligible){
        $result["status"] = "not_eligible";
        $result["message"] = "این کد تخفیف برای سبد فعلی شما قابل استفاده نیست.";
        return $result;
    }

    $result["status"] = "invalid";
    $result["message"] = "کد تخفیف معتبر نیست.";
    return $result;
}


class sales{
    public $cart = array();
    public $gifts = array();
    public $send = 1;
    public function __construct($mode,$ouid,$cart_cost=0,$send_cost=0,$admin_state = 0,$send_applicable = true){
        switch($mode){
            case "read":
                $this->db_get_sales($ouid);
                break;
            case "first":
                $t_r_buy = 0;
                $n_buy = 0;
                $this->db_new_sales($cart_cost,$send_cost,$t_r_buy,$n_buy,$admin_state,$send_applicable);
                break;
            case "new":
                $t_r_buy = getVarFromDB("orders","sum(pay_price)","uid",$ouid);
                $n_buy = getVarFromDB("orders","count(pay_price)","uid",$ouid);
                $this->db_new_sales($cart_cost,$send_cost,$t_r_buy,$n_buy,$admin_state,$send_applicable);
                break;
        }           
    }
    private function db_new_sales($cart_cost,$send_cost,$t_r_buy,$n_buy,$admin_state,$send_applicable = true){
        include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
        $tb = "sales";
        $timestamp = time() + 2.5*3600;
        $current_time = date("Y-m-d H:i:s",$timestamp);
        $st = "SELECT id,name,type,amount,min_buy,min_t_buy,min_n_buy,end_date,lksr FROM sales WHERE del_flag = 0 AND start_date <= '$current_time' AND admin_state <= $admin_state;";
        $st = $mysqli->prepare($st);
        $session_lksr = "";
        if(isset($_SESSION['lksr'])){
            $session_lksr = cart_discount_code_normalize($_SESSION['lksr']);
        }
        if($st->execute()){
        $res = $st->get_result();
        while($row = $res->fetch_assoc()){
            $sid = $row["id"];
            $name = $row["name"];
            $type = cart_sale_normalize_type($row["type"]);
            $amount = $row["amount"];    
            $end_date = $row["end_date"];    
            $min_buy = (int)$row["min_buy"];    
            $min_t_buy = (int)$row["min_t_buy"];    
            $min_n_buy = (int)$row["min_n_buy"];
            if(isset($end_date) && $end_date){
                $end_date = strtotime($end_date);
            }else{
                $end_date = db_time_now() + 3.5*3600;
                // $end_date = date("Y-m-d H:i:s",$end_date);
            }
            
            $row_lksr = cart_discount_code_normalize(isset($row["lksr"]) ? $row["lksr"] : "");
            if($row_lksr === "" || ($session_lksr !== "" && $row_lksr === $session_lksr)){
                // $_SESSION['s_lk'][0]=$end_date;
                // $_SESSION['s_lk'][1]=$timestamp;
                if($end_date > $timestamp && $min_t_buy <= $t_r_buy && $min_n_buy <= $n_buy && $min_buy <= $cart_cost){
                    // $_SESSION['s_lk'][2]=$current_time;
                    switch($type){
                        case "gift":
                            $this->gifts[sizeof($this->gifts)] = new sale($sid,$type,$name,$amount,$cart_cost,$send_cost);
                            break;
                        case "send":
                            if($send_applicable){
                                $this->send = new sale($sid,$type,$name,$amount,$cart_cost,$send_cost);
                                }
                            break;
                        case "percent":
                        case "amount":
                        case "fixed_price":
                            $this->cart[sizeof($this->cart)] = new sale($sid,$type,$name,$amount,$cart_cost,$send_cost);
                            break;
                    }
                }
                
            }
        }
        }
    }
    private function db_get_sales($oid){
        include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
        $st = "SELECT sid,amount FROM orders_sale WHERE oid = ?";
        $st = $mysqli->prepare($st);
        $st->bind_param("s",$oid);
        if($st->execute()){
        $res = $st->get_result();
        while($row = $res->fetch_assoc()){
            $sid = $row["sid"];
            $t_amount = $row["amount"];
            $name = getVarFromDB("sales","name","id",$sid);
            $type = cart_sale_normalize_type(getVarFromDB("sales","type","id",$sid));
            $amount = getVarFromDB("sales","amount","id",$sid);
            
            switch($type){
                case "gift":
                    $this->gifts[sizeof($this->gifts)] = new sale($sid,$type,$name,$amount,0,0);
                    break;
                case "send":
                    $this->send = new sale($sid,$type,$name,$amount,0,0);
                    $this->send->t_amount = (int)round(max(cart_sale_parse_numeric($t_amount,0),0));
                    break;
                case "percent":
                case "amount":
                case "fixed_price":
                    $this->cart[sizeof($this->cart)] = new sale($sid,$type,$name,$amount,0,0);
                    $this->cart[sizeof($this->cart)-1]->t_amount = (int)round(max(cart_sale_parse_numeric($t_amount,0),0));
                    break;
            }
        }
        }
    }
    public function cart_sale(){
        $cart_sales = 0;
        foreach ($this->cart as $cart){
            $cart_sales += $cart->t_amount;
        }
        return $cart_sales;
    }
    public function send_sale(){
        if($this->send !== 1){
            return true;
        }
        return false;
    }
    public function total_sale(){
        if($this->send_sale())return $this->send->t_amount + $this->cart_sale();
        return $this->cart_sale();
    }
}
class sale{
    public $name;
    public $amount;
    public $type;
    public $sid;
    public $t_amount;
    public function __construct($sid,$type,$name,$amount,$cart_cost,$send_cost){
        $this->name = $name;
        $this->type = cart_sale_normalize_type($type);
        $this->sid = $sid;
        if($this->type == "gift"){
            $this->amount = (int)round(max(cart_sale_parse_numeric($amount,0),0));
            $this->name .= " :".getVarFromDB("products","name","id",$this->amount);
        }else{
            $this->amount = cart_sale_parse_numeric($amount,0);
        }
        if($this->type == "percent")$this->name .= ": ".(0 + $this->amount)." % از سبد خرید";
        $this->c_amount($cart_cost,$send_cost);
    }
    public function c_amount($cart_cost = 0, $send_cost = 0) {
    // Ensure inputs are valid numbers and non-negative
    $cart_cost = max(cart_sale_parse_numeric($cart_cost, 0), 0);
    $send_cost = max(cart_sale_parse_numeric($send_cost, 0), 0);

    switch ($this->type) {
        case "percent":
            // New Logic: Apply percentage to (Cart + Shipping)
            $percent = max(cart_sale_parse_numeric($this->amount, 0), 0);
            $this->t_amount = ($cart_cost + $send_cost) * $percent / 100;
            break;

        case "send":
            // Logic remains: Only covers shipping cost
            $this->t_amount = $send_cost;
            break;

        case "gift":
            $this->t_amount = 0;
            break;

        case "amount":
            // Logic remains: Fixed amount off the total
            $this->t_amount = max(cart_sale_parse_numeric($this->amount, 0), 0);
            break;

        case "fixed_price":
            // New Logic: Cap the (Cart + Shipping) total to this fixed price
            $fixed_price = max(cart_sale_parse_numeric($this->amount, 0), 0);
            $this->t_amount = max(($cart_cost + $send_cost) - $fixed_price, 0);
            break;

        default:
            $this->t_amount = 0;
            break;
    }

    // Prevent discount from exceeding the total price and round to integer
    // We calculate the max of 0, and ensure it doesn't exceed the total sum
    $total_limit = $cart_cost + $send_cost;
    $this->t_amount = (int) round(min($this->t_amount, $total_limit));

    return $this->t_amount;
}
}

class user{
//    public $uid;
    private $name;
    private $tel;
    private $email;
    private $PP_state;
    private $create_code;
    public function __construct($user_data){
        $this->set_name($user_data["name"]);
        $this->set_tel($user_data["tel"]);
        $this->set_email($user_data["email"]);
        $this->set_PP_state($user_data["PP_state"]);
        $this->set_create_code($user_data["create_code"]);
        
//        $this->uid = db_check_user($user_data);
    }
    private function error(){
        $er_no = 0;
        $err = array();
        if(!isset($this->name)){
            $err["name"] = "not set";
            $er_no++;
            // db_new_user_register_error($this->tel,$this->name,"name not set");
        }
        if(!isset($this->tel)){
            $err["tel"] = "not set";
            $er_no++;
            // db_new_user_register_error($this->tel,$this->name,"tel not set");
        }else{
            // if(var_exist($this->tel,"users","tel")){
            //     $err["tel"] = "duplicate";
            //     $er_no++;
            // }
        }
//        if(!isset($this->email)){
//            $err["email"] = "not set";
//            $er_no++;
//        }
        if(!isset($this->PP_state)){
            $err["PP_state"] = "not set";
            $er_no++;
            // db_new_user_register_error($this->tel,$this->name,"PP not set");
        }
        if(!isset($this->create_code)){
            $err["create_code"] = "not set";
            $er_no++;
            // db_new_user_register_error($this->tel,$this->name,"CC not set");
        }
        return [$er_no,$err];
    }
    private function set_name($name){
        if($name = check_value("text",$name)){
            $this->name = $name;
        }
        return;
    }
    private function set_tel($tel){
        if($tel = check_value("tel",$tel)){
            $this->tel = $tel;
        }
        return;
    }
    private function set_email($email){
        if($email = check_value("email",$email)){
            $this->email = $email;
        }
        return;
    }
    private function set_PP_state($PP_state){
        if($PP_state = check_value("checkbox",$PP_state)){
            $this->PP_state = True;
        }
    }
    private function set_create_code($create_code){
        if($create_code = check_value("text",$create_code)){
            $this->create_code = $create_code;
        }
    }
    public function data(){
        return array(
//                    "uid"=>$this->uid,
                    "name"=>$this->name,
                    "tel"=>$this->tel,
                    "email"=>$this->email,
                    "PP_state"=>$this->PP_state,
                    "create_code"=>$this->create_code,
                    "error"=>$this->error()
                    );
    }
}
class user_r{
    public $uid;
    private $name;
    private $tel;
    private $email;
    public function __construct($user_data){
        $this->set_name($user_data["name"]);
        $this->set_tel($user_data["tel"]);
        $this->set_email($user_data["email"]);
        
//        $this->uid = db_check_user($user_data);
    }
    private function error(){
        $er_no = 0;
        $err = array();
        if(!isset($this->name)){
            $err["name"] = "not set";
            $er_no++;
        }
        if(!isset($this->tel)){
            $err["tel"] = "not set";
            $er_no++;
        }
        return [$er_no,$err];
    }
    private function set_name($name){
        if($name = check_value("text",$name)){
            $this->name = $name;
        }
        return;
    }
    private function set_tel($tel){
        if($tel = check_value("text",$tel)){
            $this->tel = $tel;
        }
        return;
    }
    private function set_email($email){
        if($email = check_value("email",$email)){
            $this->email = $email;
        }
        return;
    }
    public function data(){
        return array(
//                    "uid"=>$this->uid,
                    "name"=>$this->name,
                    "tel"=>$this->tel,
                    "email"=>$this->email,
                    "error"=>$this->error()
                    );
    }
}
class address{
    private $aid = null;
    private $county;
    private $city;
    private $address;
    private $post_code;
    private $rec_name;
    private $rec_tel;
    private $rec_tel_2;
    private $janitor;
    private $cost;
    private $error;
    public function __construct($address,$aid=null){
        // $this->set_county(check_value("text",$address["county"]));
        // $this->set_city(check_value("text",$address["city"]));
        $this->set_county_and_city(check_value("text",$address["county"]),check_value("text",$address["city"]));
        $this->address = check_value("text",$address["address"]);
        $this->post_code = check_value("text",$address["post_code"]);
        $this->set_cost();
        $this->check_error();
        $this->aid = $aid;
        $this->rec_name = check_value("text",$address["rec_name"]);
        $this->rec_tel = check_value("tel",$address["rec_tel"]);
        $this->rec_tel_2 = check_number($address["rec_tel_2"]);
        $this->janitor = check_value("checkbox",$address["janitor"]);
    }
    private function set_county_and_city($county,$city){
        if(var_exist($county,"cities","county")){
            $c_str = getVarFromDB("cities","cities_str","county",$county);
            if(stripos(",$c_str,",$city) || stripos(",$c_str,",$city)===0){
                $this->county = $county;
                $this->city = $city;
                return;
            }
        }
        unset($this->county);
        unset($this->city);
        return;
    }
    private function set_county($county){
        if(var_exist($county,"cities","county")){
            $this->county = $county;
        }
        return;
    }
    private function set_city($city){
        if(isset($this->county)){
            $c_str = getVarFromDB("cities","cities_str","county",$this->county);
            if(get_str_index($c_str,",",$city)[0] >= 0){
                $this->city = $city;
            }
        }
        return;
    }
    private function set_cost(){
        if(isset($this->county)){
            $this->cost = getVarFromDB("cities","cost","county",$this->county);
        }
        return;
    }
    private function check_error(){
        $er_no = 0;
        $err = array();
        if(!isset($this->county)){
            $err[0] = "County not set";
            $er_no++;
        }
        if(!isset($this->city)){
            $err[1] = "City not set";
            $er_no++;
        }
        if(!isset($this->address)){
            $err[2] = "Address not set";
            $er_no++;
        }
        if($this->county != "شهر تهران" && !isset($this->post_code)){
            $err[3] = "Postal code not set";
//            $er_no++;
        }
        if(!isset($this->cost)){
            $err[4] = "Cost not set";
            $er_no++;
        }
        $this->error = [$er_no,$err];
        return;
    } 
    public function data(){
        return array(
                    "aid"=>$this->aid,
                    "county"=>$this->county,
                    "city"=>$this->city,
                    "address"=>$this->address,
                    "post_code"=>$this->post_code,
                    "rec_name"=>$this->rec_name,
                    "rec_tel"=>$this->rec_tel,
                    "rec_tel_2"=>$this->rec_tel_2,
                    "janitor"=>$this->janitor,
                    "cost"=>$this->cost,
                    "error"=>$this->error
                    );
    }
    public function full_address(){
        if ($this->county === 'pinket' || $this->city === 'pinket') {
            return $this->address;
        }
        return $this->county."، ".$this->city."، ".$this->address;
    }
}

class send_date{
    public $sdid;
    public $shift;
    public $tp_id;
    public $n2s;
    
    public function __construct($sdid,$shift,$n2s){
        $state = false;
        $this->tp_id = getVarFromDB("sd_shifts","transporter_id","id",$shift);
        switch($sdid){
            case "post":
                if($this->tp_id == 1){
                    $state = true;
                }
                break;
            case "courier":
                if($this->tp_id == 2){
                    $state = true;
                }
                break;
            default:
                if(var_exist($sdid,"send_date","id") && $this->tp_id == getVarFromDB("sd_setting","value","flag","tp_id")){
                    $state = true;
                }
                break;
        }
        if($state){
            $this->sdid = $sdid;
            $this->shift = $shift;
            $this->n2s = $n2s;
        }else{
            $this->sdid = False;
        }
    }
    public function sendDate_CSQL(){
        if($this->tp_id == "1" || $this->tp_id == "2"){
            if(var_exist($this->n2s,"send_date","id")){
                $sql_time = getVarFromDB("send_date","date","id",$this->n2s);
            }else $sql_time = NULL;
            return $sql_time;
        }
        if(var_exist($this->sdid,"send_date","id")){
            $sql_time = getVarFromDB("send_date","date","id",$this->sdid);
        }else $sql_time = NULL;
        return $sql_time;
    }
    public function recDate_CSQL(){
        include_once $GLOBALS['bu']."modules/jdf.php";
        if($this->tp_id == "1" || $this->tp_id == "2"){
            if(var_exist($this->n2s,"send_date","id")){
                $sql_time = getVarFromDB("send_date","date","id",$this->n2s);
            }else $sql_time = NULL;
            return $sql_time;
        }
        if(var_exist($this->sdid,"send_date","id")){
            $ts = strtotime($this->sendDate_CSQL());
            $ts = $ts + 24*3600*$this->n2s;
            return date("Y-m-d H:i:s",$ts);
        }
        return false;
    }
    public function data(){
        return array(
            "sdid"=>$this->sdid,
            "shift"=>$this->shift,
            "n2s"=>$this->n2s,
            );
    }
}


function db_check_user($log_data){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $tb = "users";
    $tb2 = "users_password_attempt";
    if(isset($log_data["tel"]) && isset($log_data["create_code"])){
        $tel = $log_data["tel"];
        $code = $log_data["create_code"];
        $db_code = getVarFromDB($tb2,"code","tel",$tel,"id DESC");
        if($code == $db_code){
            $timestamp = time() - 2 * 60 + 2.5*60*60;
            $sqltime = date("Y-m-d H:i:s",$timestamp);
            $st = "SELECT id FROM $tb2 WHERE tel = ? AND code = ? AND create_date >= '$sqltime' AND state = 0 ORDER BY id DESC Limit 1";
            $st = $mysqli->prepare($st);
            $st->bind_param('ss',$tel,$code);
            $st->execute();
            $st->store_result();
            if($st->num_rows == 1){
                $st->bind_result($id);
                $st->fetch();
                if(var_exist($tel,$tb,"tel")){
                    return getVarFromDB($tb,"id","tel",$tel);
                }
        }
    }
        return false;
    }
}

function db_check_address($uid,$address){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $table = "addresses";
    $st = "SELECT id FROM $table WHERE uid = ? AND county = ? AND city = ? AND address = ? LIMIT 1";
    $st = $mysqli->prepare($st);
    $st->bind_param('ssss',$uid,$address["county"],$address["city"],$address["address"]);
    if(!$st->execute()){
        return false;
    }
    $st->store_result();
    $st->bind_result($aid);
    $st->fetch();
    if($st->num_rows == 1){
        $fields = ["post_code","rec_name","rec_tel","rec_tel_2","janitor"];
        foreach($fields as $f){
            if($address[$f]){
                updateInDB($table,$f,$address[$f],"id",$aid);
            }
        }
        return $aid;
    }
    return false;
}
function db_new_user($user_data){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $table = "users";
    $field = "email";
    if(isset($_SESSION["a_logged"])){$field = "social";$user_data["create_code"]="_";}
    $st = "INSERT INTO $table (name,tel,$field,password) VALUES (?,?,?,?);";
    $st = $mysqli->prepare($st);
    $st->bind_param('ssss',$user_data["name"],$user_data["tel"],$user_data["email"],$user_data["create_code"]);
    if(!$st->execute()){
        $ss_e = 1;
    }
    $st = "SELECT id FROM $table ORDER BY id DESC;";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        $ss_e = 1;
    }
    $st->store_result();
    $st->bind_result($value);
    $st->fetch();
    return $value;
}
function db_new_address($uid,$address){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $table = "addresses";
    $st = "INSERT INTO $table (uid,county,city,address,post_code,rec_name,rec_tel,rec_tel_2,janitor) VALUES (?,?,?,?,?,?,?,?,?)";
    $st = $mysqli->prepare($st);
    $st->bind_param('sssssssss',$uid,$address["county"],$address["city"],$address["address"],$address["post_code"],$address["rec_name"],$address["rec_tel"],$address["rec_tel_2"],$address["janitor"]);
    if(!$st->execute()){
        return false;
    }
    $st = "SELECT id FROM $table ORDER BY id DESC;";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        return false;
    }
    $st->store_result();
    $st->bind_result($value);
    $st->fetch();
    return $value;
}
function db_new_order($uid,$aid,$cart,$cpr,$sat,$pp,$send_date=""){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $ss_e = 0;
    $table = "orders";
    $as = 0;
    if(isset($_SESSION["a_logged"])){$as = 1;}  
    $lksr = null;
    if(isset($_SESSION['lksr'])){
        $lksr_raw = trim((string)$_SESSION['lksr']);
        if($lksr_raw !== ""){
            $lksr = $lksr_raw;
        }
    }
    if($send_date===""){
        $sd_sql = NULL;
        $rd_sql = NULL;
        $r_shift = NULL;
    }else{
        $sd_sql = $send_date->sendDate_CSQL();
        $rd_sql = $send_date->recDate_CSQL();
        $r_shift = $send_date->shift;
    }
    $st = "INSERT INTO $table (uid,aid,cart_price,cart_pure,sale_total,pay_price,admin_state,p_send_date,recieve_date,recieve_shift,lksr) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
    $st = $mysqli->prepare($st);
    $cp = $cart->price();
    $st->bind_param('sssssssssss',$uid,$aid,$cp,$cpr,$sat,$pp,$as,$sd_sql,$rd_sql,$r_shift,$lksr);
    if(!$st->execute()){
        return false;
    }
    $st = "SELECT id FROM $table ORDER BY id DESC;";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        return false;
    }
    $st->store_result();
    $st->bind_result($oid);
    $st->fetch();
// Create Sub Orders
    $table = "sub_orders";
    for($so_i=0;$so_i<sizeof($cart->orders);$so_i++){
        $item = $cart->orders[$so_i];
        $type = getVarFromDB('products','type','id',$item->pid);
        $st = "INSERT INTO $table (oid,pid,weight,number,type) VALUES (?,?,?,?,?)";
        $st = $mysqli->prepare($st);
        $st->bind_param('sssss',$oid,$item->pid,$item->weight,$item->no,$type);
        if(!$st->execute()){
            return false;
        }
        if(is_a($item,"order_box")){
            $so_id = last_id($table);
            for($sob_i=0;$sob_i<sizeof($item->content->orders);$sob_i++){
                $b_item = $item->content->orders[$sob_i];
                $st = "INSERT INTO $table (oid,pid,weight,number,type,so_id) VALUES (?,?,?,?,'piece',?)";
                $st = $mysqli->prepare($st);
                $st->bind_param('sssss',$oid,$b_item->pid,$b_item->weight,$b_item->no,$so_id);
                if(!$st->execute()){
                    return false;
                }
            }
        }
    }
    return $oid;
}
function db_new_orders_sale($uid,$oid,$sales){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $ss_e = 1;
    $table = "orders_sale";
    $st = "INSERT INTO $table (uid,oid,sid,amount) VALUES (?,?,?,?)";
    $st = $mysqli->prepare($st);
    foreach ($sales->cart as $sale){
        $sid = $sale->sid;
        $amount = $sale->t_amount;
        $st->bind_param('ssss',$uid,$oid,$sid,$amount);
        if(!$st->execute())$ss_e++;
    }
    foreach ($sales->gifts as $sale){
        $sid = $sale->sid;
        $amount = $sale->t_amount;
        $st->bind_param('ssss',$uid,$oid,$sid,$amount);
        if(!$st->execute())$ss_e++;
    }
    if($sales->send_sale()){
        $sale = $sales->send;
        $sid = $sale->sid;
        $amount = $sale->t_amount;
        $st->bind_param('ssss',$uid,$oid,$sid,$amount);
        if(!$st->execute())$ss_e++;
    }
    if($ss_e == 1)return true;
    return false;
}

function db_get_user($uid){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $table = "users";
    $user_data["name"] = getVarFromDB($table,"name","id",$uid);
    $user_data["tel"] = getVarFromDB($table,"tel","id",$uid);
    $user_data["email"] = getVarFromDB($table,"email","id",$uid);
    $user = new user_r($user_data);
    if($user->data()["error"][0] == 0)
    return $user;
    return false;
}
function db_get_address($aid){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $table = "addresses";
    $fields = ["county","city","address","post_code","rec_name","rec_tel","rec_tel_2","janitor"];
    foreach($fields as $f){
        $address[$f] = getVarFromDB($table,$f,"id",$aid);
    }

    $address = new address($address);
    if($address->data()["error"][0] == 0)return $address;
    return false;
}
function db_get_all_address($uid){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $table = "addresses";
    $st = "SELECT * FROM $table WHERE uid = ? ORDER BY id ASC";
    $st = $mysqli->prepare($st);
    $st->bind_param('s',$uid);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $k = 0;
    while($row = $res->fetch_assoc()){
        $addresses[$k] = new address($row,$row["id"]);
        $k++;
    }
    if(isset($addresses))return $addresses;
    return false;
}

function db_get_cart($oid){
        include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
        $cart = new cart();
        $table = "sub_orders";
        $st = "SELECT id,pid,weight,number,type FROM $table WHERE oid = $oid AND del_flag = 0 AND (type = 'pack' OR type = 'box')";
        $st = $mysqli->prepare($st);
        if(!$st->execute()){
            echo "E";
            exit;
        }
        $res = $st->get_result();
        $k = 0;
        while($row = $res->fetch_assoc()){
            $cart->add_order($row["pid"],$row["weight"],1);
            $cart->change_no_order($row["pid"],$row["weight"],$row["number"]-1);
        }
        return $cart;
    }
function db_get_orders_sales($oid){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $cart = new cart();
    $table = "orders_sale";
    $aid = getVarFromDB("orders","aid","id",$oid);
    $county = getVarFromDB("addresses","county","id",$aid);
    $send_cost = getVarFromDB("cities","cost","county",$county);
    $otp = getVarFromDB("orders","total_price","id",$oid);
    
    $st = "SELECT sid FROM $table WHERE oid = $oid";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $k = 0;
    while($row = $res->fetch_assoc()){
        $sid = $row["sid"];
        $name = getVarFromDB("sales","name","id",$sid);
        $type = getVarFromDB("sales","type","id",$sid);
        $amount = getVarFromDB("sales","amount","id",$sid);
        $sales[$k] = new sale($sid,$type,$name,$amount);
        $sales[$k]->c_amount($otp,$send_cost);
    }
    return $sales;
}

class where_u{
    public $section;
    public $level;
    public $state;
//    public $order_by;
//    public $del_flag;
//    public $sql_where;
    public $id;
    public $sub_id;
    public function __construct($section,$level = 0,$state = null,$id = null){
        $this->section = $section;
        $this->level = $level;
        $this->state = $state;
//        $this->order_by = new order_by();
        $this->id = $id;
//        $this->del_flag = 0;
    }
}


function pep_sign_data($data_str){
    if(!defined('EXTERNAL_INTEGRATIONS_ENABLED') || !EXTERNAL_INTEGRATIONS_ENABLED) return '';
    $bu = $GLOBALS['bu'];
    // Sign: Step 2: SHA1 Hashing
    $hashed_sign = sha1($data_str,true);
    // Sign: Step 3: Sign data with Private Key
    require_once($bu."modules/pep/RSAProcessor.class.php");
    $processor = new RSAProcessor($bu."modules/pep/key.xml",RSAKeyType::XMLFile);
    $signed_str =  $processor->sign($hashed_sign);
    // Sign: Step 4: Base64 Encoding
    $sign = base64_encode($signed_str);
    return $sign;
}
function pep_post_req($fields,$url){
    if(!defined('EXTERNAL_INTEGRATIONS_ENABLED') || !EXTERNAL_INTEGRATIONS_ENABLED) return '';
    $fields_str = http_build_query($fields);
    //open connection
    $ch = curl_init();
    //set the url, number of POST vars, POST data
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_POST,count($fields));
    curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_str);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    //execute post
    $res = curl_exec($ch);
    //close connection
    curl_close($ch);
    return $res;
}
function pep_xmlres_parser($xml_response){
    $ret = array();
    $parser = xml_parser_create();
    xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
    xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
    xml_parse_into_struct($parser,$xml_response,$values,$tags);
    xml_parser_free($parser);
    $hash_stack = array();
    foreach ($values as $key => $val)
    {
        switch ($val['type'])
        {
           case 'open':
              array_push($hash_stack, $val['tag']);
           break;
           case 'close':
              array_pop($hash_stack);
           break;
           case 'complete':
              array_push($hash_stack, $val['tag']);
              eval("\$ret[" . implode($hash_stack, "][") . "] = '{$val[value]}';");
              array_pop($hash_stack);
           break;
        }
    }
    return $ret;
}
function pep_verifyPayment($bcd){
    $bu = $GLOBALS['bu'];
    $fields_keys = ["merchantCode","terminalCode","invoiceNumber","invoiceDate","amount"];
    foreach($fields_keys as $key){
        $fields[$key] = $bcd[$key];
    }
    $fields["TimeStamp"] = date("Y/m/d H:i:s");
    $data_str = "#";
    foreach($fields as $key=>$field){
        $data_str.=$field."#";
    }
    $fields["sign"] = pep_sign_data($data_str);
    $url = 'https://pep.shaparak.ir/VerifyPayment.aspx';
    $verify_xml = pep_post_req($fields,$url);
    $verify_res = pep_xmlres_parser($verify_xml)["actionResult"];
    return $verify_res;
}
function db_new_pep_error($oid="",$data="",$bcd=""){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $ipAddress = $_SERVER['REMOTE_ADDR'];
    if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
        $ipAddress = array_pop(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
    }
    $browser = $_SERVER['HTTP_USER_AGENT'];
    $data = json_encode($data);
    $bcd = json_encode($bcd);
    $st = "INSERT INTO pep_errors (oid,data,bcd,ip,browser) VALUES (?,?,?,?,?);";
    $st = $mysqli->prepare($st);
    $st->bind_param('sssss',$oid,$data,$bcd,$ipAddress,$browser);
    if($st->execute()){
        return true;
    }
    return false;
}
function db_pay_order_pep($oid,$tref_id,$trace_no,$ref_no,$day_id=0){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    if(
    updateInDB("orders","pay_request_auth",$tref_id,"id",$oid) &&
    updateInDB("orders","pay_id",$trace_no,"id",$oid) && 
    updateInDB("orders","pay_ref_no",$ref_no,"id",$oid) &&
    updateInDB("orders","state",1,"id",$oid)
    ){
        if($day_id && var_exist($day_id,"send_date","id")){
            $on = (int)getVarFromDB("send_date","order_no","id",$day_id);
            $on++;
            updateInDB("send_date","order_no",$on,"id",$day_id);
        }
    return true;
    }
    return false;
}

function db_pay_order($oid,$pay_id,$day_id=0){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    updateInDB("orders","pay_id",$pay_id,"id",$oid);
    updateInDB("orders","state",1,"id",$oid);
    if($day_id && var_exist($day_id,"send_date","id")){
        $on = (int)getVarFromDB("send_date","order_no","id",$day_id);
        $on++;
        updateInDB("send_date","order_no",$on,"id",$day_id);
    }
    
}
function db_new_zp_error($oid="",$Status="",$Authority = ""){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $st = "INSERT INTO zp_errors (oid,status,authority) VALUES (?,?,?);";
    $st = $mysqli->prepare($st);
    $st->bind_param('sss',$oid,$Status,$Authority);
    if($st->execute()){
        return true;
    }
    return false;
}

function db_new_user_register_error($tel="",$name="",$error=""){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $st = "INSERT INTO user_register_errors (tel,name,error_detail) VALUES (?,?,?)";
    $st = $mysqli->prepare($st);
    $st->bind_param('sss',$tel,$name,$error);
    $st->execute();
    return;
}

function product_discount_apply_to_amount($base_amount, $discount_type, $discount_value)
{
    $base_amount = (float)$base_amount;
    $discount_value = (float)$discount_value;
    $discount_type = strtolower(trim((string)$discount_type));

    $final_amount = $base_amount;
    switch ($discount_type) {
        case "percent":
            $final_amount = $base_amount - (($base_amount * $discount_value) / 100.0);
            break;
        case "fixed_amount":
            $final_amount = $base_amount - $discount_value;
            break;
        case "fixed_price":
            $final_amount = $discount_value;
            break;
    }

    if ($final_amount < 0) $final_amount = 0;
    return (int)round($final_amount);
}

function product_discount_table_columns()
{
    static $columns = null;
    if($columns !== null){
        return $columns;
    }
    $columns = array();
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $st = $mysqli->prepare("SHOW COLUMNS FROM product_discounts");
    if(!$st){
        return $columns;
    }
    if(!$st->execute()){
        return $columns;
    }
    $res = $st->get_result();
    while($res && ($row = $res->fetch_assoc())){
        if(isset($row["Field"])){
            $columns[(string)$row["Field"]] = 1;
        }
    }
    return $columns;
}

function product_discount_has_column($column_name)
{
    $columns = product_discount_table_columns();
    return isset($columns[(string)$column_name]);
}

function product_discount_csv_to_array($csv_value)
{
    if($csv_value === null){
        return array();
    }
    $csv_value = trim((string)$csv_value);
    if($csv_value === ""){
        return array();
    }
    $parts = explode(",",$csv_value);
    $out = array();
    foreach($parts as $part){
        $out[] = trim((string)$part);
    }
    return $out;
}

function product_discount_weight_key($weight)
{
    $weight = trim((string)$weight);
    if($weight === ""){
        return null;
    }
    if(is_numeric($weight)){
        return (string)((int)round((float)$weight));
    }
    return $weight;
}

function product_discount_build_weight_price_map($discount_row)
{
    if(!is_array($discount_row)){
        return array();
    }
    if(!array_key_exists("discount_weight",$discount_row) || !array_key_exists("discount_price",$discount_row)){
        return array();
    }

    $weights = product_discount_csv_to_array($discount_row["discount_weight"]);
    $prices = product_discount_csv_to_array($discount_row["discount_price"]);
    if(sizeof($weights) === 0 || sizeof($prices) === 0){
        return array();
    }

    $map = array();
    foreach($weights as $i => $weight_raw){
        $key = product_discount_weight_key($weight_raw);
        if($key === null){
            continue;
        }
        if(!array_key_exists($i,$prices)){
            continue;
        }
        $price_raw = trim((string)$prices[$i]);
        if($price_raw === ""){
            continue;
        }
        if(!is_numeric($price_raw)){
            continue;
        }
        $price = (int)round((float)$price_raw);
        if($price < 0){
            $price = 0;
        }
        $map[$key] = $price;
    }
    return $map;
}

function product_discount_is_old_style_valid($discount_row)
{
    if(!is_array($discount_row)){
        return false;
    }
    if(!array_key_exists("discount_type",$discount_row) || !array_key_exists("discount_value",$discount_row)){
        return false;
    }
    $discount_type = strtolower(trim((string)$discount_row["discount_type"]));
    $discount_value = (float)$discount_row["discount_value"];
    if($discount_type === "fixed_price"){
        return $discount_value >= 0;
    }
    if($discount_value <= 0){
        return false;
    }
    return in_array($discount_type,array("percent","fixed_amount"),true);
}

function product_discount_apply_to_weight_price($base_amount,$weight,$discount_row)
{
    $base_amount = (float)$base_amount;
    if(!is_array($discount_row)){
        return (int)round($base_amount);
    }

    $weight_map = product_discount_build_weight_price_map($discount_row);
    if(sizeof($weight_map) > 0){
        $weight_key = product_discount_weight_key($weight);
        if($weight_key !== null && array_key_exists($weight_key,$weight_map)){
            return (int)$weight_map[$weight_key];
        }
        return (int)round($base_amount);
    }

    if(product_discount_is_old_style_valid($discount_row)){
        return product_discount_apply_to_amount(
            $base_amount,
            (string)$discount_row["discount_type"],
            (float)$discount_row["discount_value"]
        );
    }
    return (int)round($base_amount);
}

function product_discount_get_active($pid, $at_time = null)
{
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $pid = (int)$pid;
    if ($pid < 1) return false;

    if ($at_time === null || trim((string)$at_time) === "") {
        $at_time = date("Y-m-d H:i:s");
    } else {
        $at_time = date("Y-m-d H:i:s", strtotime((string)$at_time));
    }

    $select_fields = array("id","pid","start_at","end_at","is_active");
    $optional_fields = array("discount_weight","discount_price","discount_type","discount_value","title_fa");
    foreach($optional_fields as $field){
        if(product_discount_has_column($field)){
            $select_fields[] = $field;
        }
    }

    $st = "SELECT ".implode(", ",$select_fields)."
           FROM product_discounts
           WHERE pid = ?
             AND is_active = 1
             AND (start_at IS NULL OR start_at <= ?)
             AND (end_at IS NULL OR end_at >= ?)
           ORDER BY (start_at IS NULL) ASC, start_at DESC, id DESC
           LIMIT 1";
    $st = $mysqli->prepare($st);
    if(!$st){
        return false;
    }
    $pid_sql = (string)$pid;
    $st->bind_param('sss', $pid_sql, $at_time, $at_time);
    if(!$st->execute()){
        return false;
    }
    $res = $st->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    if(!$row){
        return false;
    }
    $weight_map = product_discount_build_weight_price_map($row);
    if(sizeof($weight_map) === 0 && !product_discount_is_old_style_valid($row)){
        return false;
    }
    return $row;
}

function product_discount_sync_sale_state($pid)
{
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $pid = (int)$pid;
    if($pid < 1){
        return false;
    }
    $has_active_discount = (product_discount_get_active($pid) !== false) ? 1 : 0;
    $pid_sql = (string)$pid;
    $sale_state_sql = (string)$has_active_discount;
    $st = $mysqli->prepare("UPDATE products SET sale_state = ? WHERE id = ?");
    if(!$st){
        return false;
    }
    $st->bind_param('ss',$sale_state_sql,$pid_sql);
    return (bool)$st->execute();
}

function product_discount_apply_price_list($base_price_list, $discount_row, $weight_list = null)
{
    if(!is_array($base_price_list) || !is_array($discount_row)){
        return $base_price_list;
    }

    $out = array();
    $weight_map = product_discount_build_weight_price_map($discount_row);
    if(sizeof($weight_map) > 0){
        if(!is_array($weight_list)){
            if(isset($discount_row["discount_weight"])){
                $weight_list = product_discount_csv_to_array($discount_row["discount_weight"]);
            }else{
                $weight_list = array();
            }
        }
        foreach($base_price_list as $k => $price){
            $base_price = (int)round((float)$price);
            $weight_key = null;
            if(isset($weight_list[$k])){
                $weight_key = product_discount_weight_key($weight_list[$k]);
            }
            if($weight_key !== null && array_key_exists($weight_key,$weight_map)){
                $out[$k] = (int)$weight_map[$weight_key];
            }else{
                $out[$k] = $base_price;
            }
        }
        return $out;
    }

    $discount_type = isset($discount_row["discount_type"]) ? (string)$discount_row["discount_type"] : "";
    $discount_value = isset($discount_row["discount_value"]) ? (float)$discount_row["discount_value"] : 0;
    foreach($base_price_list as $k => $price){
        $out[$k] = product_discount_apply_to_amount((float)$price, $discount_type, $discount_value);
    }
    return $out;
}

function product_discount_prepare_price_lists($base_price_list, $discount_row, $weight_list = null)
{
    if(!is_array($base_price_list) || !is_array($discount_row)){
        return array(
            "price" => $base_price_list,
            "old_price" => array(),
            "has_discount" => false
        );
    }
    $price = product_discount_apply_price_list($base_price_list,$discount_row,$weight_list);
    $old_price = array();
    $has_discount = false;
    foreach($base_price_list as $k => $base_price){
        $base = (int)round((float)$base_price);
        $current = isset($price[$k]) ? (int)round((float)$price[$k]) : $base;
        if($current !== $base){
            $old_price[$k] = $base;
            $has_discount = true;
        }
    }
    return array(
        "price" => $price,
        "old_price" => $old_price,
        "has_discount" => $has_discount
    );
}


function product_finance($pid,$weight,$time){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $st = "SELECT weight,price,profit FROM products_price WHERE pid = $pid AND start_time < '$time' ORDER BY start_time DESC LIMIT 1";
    
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    
    $res = $st->get_result();
    $res = $res->fetch_assoc();
    
    $price_str = $res["price"];
    $weight_str = $res["weight"];
    $profit_str = $res["profit"];
    
    $price = get_str_index($price_str,",")[1][get_str_index($weight_str,",",$weight,1)[0]];
    $profit = get_str_index($profit_str,",")[1][get_str_index($weight_str,",",$weight,1)[0]];
    $discount = product_discount_get_active($pid, $time);
    if($discount !== false){
        $price = product_discount_apply_to_weight_price($price,$weight,$discount);
    }
    
    return array(
        "weight"=>$weight,
        "price"=>$price,
        "profit"=>$profit
    );
}



function cart_summary_normalize_time($at_time = null)
{
    if($at_time === null || trim((string)$at_time) === ""){
        return date("Y-m-d H:i:s");
    }
    $ts = strtotime((string)$at_time);
    if($ts === false){
        return date("Y-m-d H:i:s");
    }
    return date("Y-m-d H:i:s",$ts);
}

function cart_summary_base_unit_price_at($pid,$weight,$at_time = null)
{
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $pid = (int)$pid;
    $weight = (int)$weight;
    if($pid < 1){
        return 0;
    }

    $at_time = cart_summary_normalize_time($at_time);
    static $row_cache = array();
    $row_key = $pid."|".$at_time;

    if(!array_key_exists($row_key,$row_cache)){
        $st = "SELECT weight,price
               FROM products_price
               WHERE pid = ?
                 AND start_time <= ?
               ORDER BY start_time DESC
               LIMIT 1";
        $st = $mysqli->prepare($st);
        if(!$st){
            $row_cache[$row_key] = null;
        }else{
            $pid_sql = (string)$pid;
            $st->bind_param("ss",$pid_sql,$at_time);
            if(!$st->execute()){
                $row_cache[$row_key] = null;
            }else{
                $res = $st->get_result();
                $row_cache[$row_key] = $res ? $res->fetch_assoc() : null;
            }
        }
    }

    $row = $row_cache[$row_key];
    if(!is_array($row) || !isset($row["weight"]) || !isset($row["price"])){
        return 0;
    }

    $weight_str = (string)$row["weight"];
    $price_str = (string)$row["price"];
    $weight_index = get_str_index($weight_str,",",$weight,1)[0];
    $price_list = get_str_index($price_str,",")[1];

    if($weight_index === null || !isset($price_list[$weight_index])){
        if(isset($price_list[0]) && is_numeric($price_list[0])){
            return (int)round((float)$price_list[0]);
        }
        return 0;
    }
    if(!is_numeric($price_list[$weight_index])){
        return 0;
    }
    return (int)round((float)$price_list[$weight_index]);
}

function cart_summary_item_is_runtime_box($item)
{
    return (
        is_object($item) &&
        isset($item->content) &&
        is_object($item->content) &&
        isset($item->content->orders) &&
        is_array($item->content->orders)
    );
}

function cart_summary_item_is_read_box($item)
{
    return (
        is_object($item) &&
        isset($item->content) &&
        is_array($item->content)
    );
}

function cart_summary_item_name($item)
{
    if(!is_object($item)){
        return "";
    }

    if(isset($item->name) && trim((string)$item->name) !== ""){
        return (string)$item->name;
    }

    if(method_exists($item,"name")){
        return (string)$item->name();
    }

    if(isset($item->pid) && var_exist((int)$item->pid,"products","id")){
        return (string)getVarFromDB("products","name","id",(int)$item->pid);
    }

    return "";
}

function cart_summary_item_weight_display($item)
{
    if(!is_object($item)){
        return "-";
    }

    if(cart_summary_item_is_runtime_box($item)){
        $w = isset($item->weight) ? (int)$item->weight : 0;
        return ($w > 0) ? (string)$w : "-";
    }

    if(cart_summary_item_is_read_box($item)){
        $w = isset($item->capacity) ? (int)$item->capacity : 0;
        if($w <= 0 && isset($item->weight)){
            $w = (int)$item->weight;
        }
        return ($w > 0) ? (string)$w : "-";
    }

    $w = 0;
    if(isset($item->weight)){
        $w = (int)$item->weight;
    }elseif(method_exists($item,"weight")){
        $w = (int)$item->weight();
    }
    return ($w > 0) ? (string)$w : "-";
}

function cart_summary_item_final_unit_price($item)
{
    if(!is_object($item)){
        return 0;
    }
    if(method_exists($item,"price")){
        return (int)round((float)$item->price());
    }
    if(isset($item->price)){
        return (int)round((float)$item->price);
    }
    return 0;
}

function cart_summary_item_base_unit_price($item,$at_time = null)
{
    if(!is_object($item) || !isset($item->pid)){
        return 0;
    }

    $pid = (int)$item->pid;
    if($pid < 1){
        return 0;
    }

    if(cart_summary_item_is_runtime_box($item)){
        $box_weight = isset($item->weight) ? (int)$item->weight : 0;
        $base = cart_summary_base_unit_price_at($pid,$box_weight,$at_time);
        foreach($item->content->orders as $box_item){
            if(!is_object($box_item) || !isset($box_item->pid)) continue;
            $base += cart_summary_base_unit_price_at((int)$box_item->pid,(int)$box_item->weight,$at_time) * (int)$box_item->no;
        }
        return (int)$base;
    }

    if(cart_summary_item_is_read_box($item)){
        $box_weight = isset($item->capacity) ? (int)$item->capacity : 0;
        if($box_weight <= 0 && isset($item->weight)){
            $box_weight = (int)$item->weight;
        }
        $base = cart_summary_base_unit_price_at($pid,$box_weight,$at_time);
        foreach($item->content as $box_item){
            if(!is_object($box_item) || !isset($box_item->pid)) continue;
            $base += cart_summary_base_unit_price_at((int)$box_item->pid,(int)$box_item->weight,$at_time) * (int)$box_item->no;
        }
        return (int)$base;
    }

    $weight = isset($item->weight) ? (int)$item->weight : 0;
    if($weight <= 0 && method_exists($item,"weight")){
        $weight = (int)$item->weight();
    }
    return cart_summary_base_unit_price_at($pid,$weight,$at_time);
}

function cart_summary_build_rows_from_items($items,$at_time = null)
{
    $out = array(
        "rows" => array(),
        "base_total" => 0,
        "final_total" => 0
    );

    if(!is_array($items)){
        return $out;
    }

    $i = 0;
    foreach($items as $item){
        if(!is_object($item) || !isset($item->pid)){
            continue;
        }
        $qty = isset($item->no) ? (int)$item->no : 1;
        if($qty < 1){
            continue;
        }

        $final_unit = cart_summary_item_final_unit_price($item);
        $base_unit = cart_summary_item_base_unit_price($item,$at_time);
        if($base_unit <= 0){
            $base_unit = $final_unit;
        }

        $i++;
        $line_total = (int)$final_unit * $qty;
        $line_base_total = (int)$base_unit * $qty;
        $discount_unit = ($final_unit < $base_unit) ? $final_unit : null;

        $out["rows"][] = array(
            "index" => $i,
            "soid" => isset($item->soid) ? (int)$item->soid : 0,
            "pid" => isset($item->pid) ? (int)$item->pid : 0,
            "opbid" => method_exists($item,"opbid") ? (string)$item->opbid() : "",
            "name" => cart_summary_item_name($item),
            "weight" => cart_summary_item_weight_display($item),
            "qty" => $qty,
            "base_unit" => (int)$base_unit,
            "discount_unit" => $discount_unit,
            "final_unit" => (int)$final_unit,
            "line_total" => (int)$line_total,
            "line_base_total" => (int)$line_base_total
        );

        $out["base_total"] += (int)$line_base_total;
        $out["final_total"] += (int)$line_total;
    }

    return $out;
}

function cart_summary_add_sale_line(&$lines,$sale_item,$line_type = "")
{
    if(!is_object($sale_item) || !isset($sale_item->sid)){
        return;
    }

    $sid = (int)$sale_item->sid;
    $line_lksr = "";
    if($sid > 0 && var_exist($sid,"sales","id")){
        $line_lksr = cart_discount_code_normalize((string)getVarFromDB("sales","lksr","id",$sid));
    }

    $lines[] = array(
        "sid" => $sid,
        "name" => isset($sale_item->name) ? (string)$sale_item->name : "",
        "type" => $line_type !== "" ? $line_type : (isset($sale_item->type) ? (string)$sale_item->type : ""),
        "amount" => isset($sale_item->t_amount) ? (int)$sale_item->t_amount : 0,
        "raw_amount" => isset($sale_item->amount) ? $sale_item->amount : 0,
        "lksr" => $line_lksr
    );
}

function cart_summary_collect_sale_lines($sales_obj)
{
    $lines = array();
    if(!is_object($sales_obj)){
        return $lines;
    }

    if(isset($sales_obj->cart) && is_array($sales_obj->cart)){
        foreach($sales_obj->cart as $sale_item){
            cart_summary_add_sale_line($lines,$sale_item);
        }
    }

    if(isset($sales_obj->send) && is_object($sales_obj->send) && isset($sales_obj->send->sid)){
        cart_summary_add_sale_line($lines,$sales_obj->send,"send");
    }

    if(isset($sales_obj->gifts) && is_array($sales_obj->gifts)){
        foreach($sales_obj->gifts as $sale_item){
            cart_summary_add_sale_line($lines,$sale_item,"gift");
        }
    }

    return $lines;
}

function cart_summary_collect_code_effect_lines($sale_lines,$lksr)
{
    $out = array();
    $lksr = cart_discount_code_normalize($lksr);
    if($lksr === "" || !is_array($sale_lines)){
        return $out;
    }
    foreach($sale_lines as $line){
        if(!is_array($line)) continue;
        if(!isset($line["lksr"])) continue;
        if(cart_discount_code_normalize((string)$line["lksr"]) === $lksr){
            $out[] = $line;
        }
    }
    return $out;
}

function cart_summary_sale_line_value($line,$context = array())
{
    if(!is_array($line)){
        return "-";
    }
    $type = isset($line["type"]) ? (string)$line["type"] : "";
    $amount = cart_summary_code_line_discount_amount($line,$context);
    if($type === "gift"){
        if($amount > 0){
            return "Gift ".price_sep($amount);
        }
        return "Gift";
    }
    if($amount <= 0){
        return "-";
    }
    return price_sep($amount);
}

function cart_summary_code_line_discount_amount($line,$context = array())
{
    if(!is_array($line)){
        return 0;
    }

    $type = cart_sale_normalize_type(isset($line["type"]) ? (string)$line["type"] : "");
    $amount = (int)round(max(cart_sale_parse_numeric(isset($line["amount"]) ? $line["amount"] : 0,0),0));
    if($amount > 0){
        return $amount;
    }

    $raw_amount = cart_sale_parse_numeric(isset($line["raw_amount"]) ? $line["raw_amount"] : 0,0);
    $cart_final_total = (int)round(max(cart_sale_parse_numeric(isset($context["cart_final_total"]) ? $context["cart_final_total"] : 0,0),0));
    $shipping_amount = (int)round(max(cart_sale_parse_numeric(isset($context["shipping_amount"]) ? $context["shipping_amount"] : 0,0),0));

    switch($type){
        case "amount":
            return (int)round(max($raw_amount,0));
        case "percent":
            return (int)round(max($cart_final_total * max($raw_amount,0) / 100,0));
        case "fixed_price":
            return (int)round(max($cart_final_total - max($raw_amount,0),0));
        case "send":
            return $shipping_amount;
        case "gift":
            $gift_id = (int)round(max($raw_amount,0));
            if($gift_id < 1){
                $sid = isset($line["sid"]) ? (int)$line["sid"] : 0;
                if($sid > 0 && var_exist($sid,"sales","id")){
                    $gift_id = (int)round(max(cart_sale_parse_numeric(getVarFromDB("sales","amount","id",$sid),0),0));
                }
            }
            if($gift_id > 0 && var_exist($gift_id,"products","id")){
                $weight_raw = (string)getVarFromDB("products_price","weight","pid",$gift_id,"start_time DESC");
                $weight_arr = explode(",",$weight_raw);
                $weight = isset($weight_arr[0]) ? trim((string)$weight_arr[0]) : "";
                if($weight === ""){
                    $weight = "0";
                }
                $at_time = isset($context["at_time"]) ? trim((string)$context["at_time"]) : date("Y-m-d H:i:s");
                $pf = product_finance($gift_id,$weight,$at_time);
                if(is_array($pf) && isset($pf["price"],$pf["profit"])){
                    return (int)round(max(cart_sale_parse_numeric($pf["price"],0)-cart_sale_parse_numeric($pf["profit"],0),0));
                }
            }
            return 0;
    }

    return 0;
}

function cart_summary_code_discount_total($code_effect_lines,$context = array())
{
    $total = 0;
    if(!is_array($code_effect_lines)){
        return 0;
    }
    foreach($code_effect_lines as $line){
        $total += cart_summary_code_line_discount_amount($line,$context);
    }
    return (int)round(max($total,0));
}

function cart_summary_default_line_name($type)
{
    $type = cart_sale_normalize_type($type);
    switch($type){
        case "percent":
            return "تخفیف درصدی";
        case "amount":
            return "تخفیف مبلغی";
        case "fixed_price":
            return "قیمت نهایی سفارش";
        case "send":
            return "ارسال رایگان";
        case "gift":
            return "هدیه";
    }
    return "تخفیف";
}

function cart_render_detailed_summary_table($rows,$summary = array())
{
    if(!is_array($rows)){
        $rows = array();
    }

    $title = isset($summary["title"]) ? (string)$summary["title"] : "";
    $cart_base_total = isset($summary["cart_base_total"]) ? (int)$summary["cart_base_total"] : 0;
    $cart_final_total = isset($summary["cart_final_total"]) ? (int)$summary["cart_final_total"] : 0;
    $product_discount_total = (int)max($cart_base_total - $cart_final_total,0);
    if(isset($summary["product_discount_total"])){
        $product_discount_total = (int)$summary["product_discount_total"];
    }
    $shipping_amount = isset($summary["shipping_amount"]) ? (int)$summary["shipping_amount"] : 0;
    $shipping_display = isset($summary["shipping_display"]) ? (string)$summary["shipping_display"] : "";
    $shipping_note = isset($summary["shipping_note"]) ? trim((string)$summary["shipping_note"]) : "";
    $sale_total = isset($summary["sale_total"]) ? (int)$summary["sale_total"] : 0;
    $pay_price = isset($summary["pay_price"]) ? (int)$summary["pay_price"] : 0;
    $lksr = isset($summary["lksr"]) ? cart_discount_code_normalize((string)$summary["lksr"]) : "";
    $code_effect_lines = isset($summary["code_effect_lines"]) && is_array($summary["code_effect_lines"]) ? $summary["code_effect_lines"] : array();
    $qty_editable = !empty($summary["qty_editable"]);
    $qty_input_name = isset($summary["qty_input_name"]) ? trim((string)$summary["qty_input_name"]) : "";
    $qty_control_mode = isset($summary["qty_control_mode"]) ? trim((string)$summary["qty_control_mode"]) : "";
    $qty_inputs_disabled = !empty($summary["qty_inputs_disabled"]);
    $cart_controls_enabled = !empty($summary["cart_controls_enabled"]);
    $show_payable_row = array_key_exists("show_payable_row",$summary) ? !empty($summary["show_payable_row"]) : true;
    $discount_row_only_with_code = !empty($summary["discount_row_only_with_code"]);
    $discount_row_before_totals = !empty($summary["discount_row_before_totals"]);
    $shipping_row_mode = isset($summary["shipping_row_mode"]) ? trim((string)$summary["shipping_row_mode"]) : "";
    $shipping_method_text = isset($summary["shipping_method_text"]) ? trim((string)$summary["shipping_method_text"]) : "";
    $checkout_scrollable = !empty($summary["checkout_scrollable"]);
    $checkout_mobile_cards = !empty($summary["checkout_mobile_cards"]);
    $discount_negative_display = !empty($summary["discount_negative_display"]);
    $total_columns = 7;

    echo '<div class="cut"></div>';
    if($title !== ""){
        echo '<h3 class="tac">'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'</h3>';
    }

    $summary_table_class = 'cart';
    if($checkout_scrollable){
        $summary_table_class .= ' checkout_summary_table';
    }
    if($checkout_mobile_cards){
        $summary_table_class .= ' checkout_mobile_source_table';
    }

    if($checkout_scrollable){
        echo '<div class="checkout_summary_shell"><div class="checkout_summary_scroll_wrap">';
    }

    echo '<table class="'.$summary_table_class.'">';
    echo '<thead>';
    echo '<tr><th>ردیف</th><th>محصول</th><th>وزن</th><th>تعداد</th><th>قیمت واحد</th><th>قیمت با تخفیف</th><th>مجموع</th></tr>';
    echo '</thead>';
    echo '<tbody>';

    $total_weight = 0;
    $mobile_cards = '';
    foreach($rows as $row){
        if(!is_array($row)) continue;
        $row_qty = isset($row["qty"]) ? (int)$row["qty"] : 0;
        $row_soid = isset($row["soid"]) ? (int)$row["soid"] : 0;
        $row_pid = isset($row["pid"]) ? (int)$row["pid"] : 0;
        $row_opbid = isset($row["opbid"]) ? (string)$row["opbid"] : "";
        $row_weight = isset($row["weight"]) ? trim((string)$row["weight"]) : "";
        $row_weight_num = null;
        if($row_weight !== "" && is_numeric($row_weight)){
            $row_weight_num = (int)round((float)$row_weight);
            $total_weight += $row_weight_num * $row_qty;
        }
        $can_cart_controls = $cart_controls_enabled && $row_pid > 0 && $row_weight_num !== null;
        $discount_cell = "-";
        if(isset($row["discount_unit"]) && $row["discount_unit"] !== null){
            $discount_cell = price_sep((int)$row["discount_unit"]);
        }
        if($can_cart_controls){
            echo '<tr id="'.$row_pid.'" name="'.htmlspecialchars($row_opbid, ENT_QUOTES, 'UTF-8').'">';
        }else{
            echo '<tr>';
        }
        echo '<td>'.(isset($row["index"]) ? (int)$row["index"] : 0).'</td>';
        echo '<td>'.htmlspecialchars((isset($row["name"]) ? (string)$row["name"] : ""), ENT_QUOTES, 'UTF-8').'</td>';
        if($can_cart_controls){
            echo '<td class="weight_options"><span class="sd">'.$row_weight_num.' گرم</span></td>';
        }else{
            echo '<td>'.htmlspecialchars((isset($row["weight"]) ? (string)$row["weight"] : "-"), ENT_QUOTES, 'UTF-8').'</td>';
        }
        if($qty_editable && $qty_input_name !== "" && $row_soid > 0){
            if($qty_control_mode === "decrease_only"){
                echo '<td><div class="order_qty_editor'.($qty_inputs_disabled ? ' is-disabled' : '').'" data-qty-editor>';
                echo '<span class="order_qty_btn s_but is-disabled" data-qty-increase aria-disabled="true" title="افزایش تعداد برای این سفارش مجاز نیست">+</span>';
                echo '<span class="order_qty_value" data-qty-value>'.$row_qty.'</span>';
                echo '<span class="order_qty_btn s_but'.($qty_inputs_disabled ? ' is-disabled' : '').'" data-qty-decrease>-</span>';
                echo '<input type="hidden" name="'.htmlspecialchars($qty_input_name, ENT_QUOTES, 'UTF-8').'['.$row_soid.']" value="'.$row_qty.'" data-qty-input data-qty-key="'.$row_soid.'" data-initial-qty="'.$row_qty.'" max="'.$row_qty.'"'.($qty_inputs_disabled ? ' disabled' : '').'>';
                echo '</div></td>';
            }else{
                echo '<td><input type="number" name="'.htmlspecialchars($qty_input_name, ENT_QUOTES, 'UTF-8').'['.$row_soid.']" min="0" max="'.$row_qty.'" value="'.$row_qty.'" data-qty-input data-qty-key="'.$row_soid.'" data-initial-qty="'.$row_qty.'" style="width:72px;text-align:center;"'.($qty_inputs_disabled ? ' disabled' : '').'></td>';
            }
        }else if($can_cart_controls){
            $reload_js = "setTimeout(function(){window.location.reload();},700);";
            echo '<td><span><span class="s_but" onclick="if(typeof inc_cart===\'function\'){inc_cart(this.parentNode.parentNode);}'.$reload_js.'">+</span>'.$row_qty.'<span class="s_but" onclick="if(typeof dec_cart===\'function\'){dec_cart(this.parentNode.parentNode);}'.$reload_js.'">-</span> <a class="remove" style="margin-right:8px;" onclick="if(typeof del_from_cart===\'function\'){del_from_cart(this.parentNode.parentNode);}'.$reload_js.'">&times;</a></span></td>';
        }else{
            echo '<td>'.$row_qty.'</td>';
        }
        echo '<td>'.price_sep((isset($row["base_unit"]) ? (int)$row["base_unit"] : 0)).'</td>';
        echo '<td>'.$discount_cell.'</td>';
        echo '<td>'.price_sep((isset($row["line_total"]) ? (int)$row["line_total"] : 0)).'</td>';
        echo '</tr>';

        if($checkout_mobile_cards){
            $mobile_cards .= '<div class="cfp_item">';
            $mobile_cards .= '<h3>'.htmlspecialchars((isset($row["name"]) ? (string)$row["name"] : ""), ENT_QUOTES, 'UTF-8').'</h3>';
            $mobile_cards .= '<div class="cfpi_weight">وزن: '.htmlspecialchars((isset($row["weight"]) ? (string)$row["weight"] : "-"), ENT_QUOTES, 'UTF-8').'</div>';
            $mobile_cards .= '<div class="cfpi_price">قیمت واحد: '.price_sep((isset($row["base_unit"]) ? (int)$row["base_unit"] : 0)).'<br>قیمت با تخفیف: '.$discount_cell.'</div>';
            $mobile_cards .= '<div class="cb"></div>';
            if($qty_editable && $qty_input_name !== "" && $row_soid > 0 && $qty_control_mode === "decrease_only"){
                $mobile_cards .= '<div class="cfpi_number">تعداد: <span class="order_qty_editor'.($qty_inputs_disabled ? ' is-disabled' : '').'" data-qty-editor>';
                $mobile_cards .= '<span class="order_qty_btn s_but is-disabled" data-qty-increase aria-disabled="true" title="افزایش تعداد برای این سفارش مجاز نیست">+</span>';
                $mobile_cards .= '<span class="order_qty_value" data-qty-value>'.$row_qty.'</span>';
                $mobile_cards .= '<span class="order_qty_btn s_but'.($qty_inputs_disabled ? ' is-disabled' : '').'" data-qty-decrease>-</span>';
                $mobile_cards .= '<input type="hidden" name="'.htmlspecialchars($qty_input_name, ENT_QUOTES, 'UTF-8').'['.$row_soid.']" value="'.$row_qty.'" data-qty-input data-qty-key="'.$row_soid.'" data-initial-qty="'.$row_qty.'" max="'.$row_qty.'"'.($qty_inputs_disabled ? ' disabled' : '').'>';
                $mobile_cards .= '</span></div>';
            }else{
                $mobile_cards .= '<div class="cfpi_number">تعداد: '.$row_qty.'</div>';
            }
            $mobile_cards .= '<div class="cfpi_total_price">مجموع: '.price_sep((isset($row["line_total"]) ? (int)$row["line_total"] : 0)).'</div>';
            $mobile_cards .= '</div>';
        }
    }

    $shipping_text_raw = $shipping_display;
    if($shipping_text_raw === ""){
        $shipping_text_raw = price_sep($shipping_amount);
    }
    $shipping_text = htmlspecialchars($shipping_text_raw, ENT_QUOTES, 'UTF-8');
    $mobile_method_text = $shipping_method_text;
    if($mobile_method_text === ""){
        $mobile_method_text = ($shipping_note !== "") ? $shipping_note : "-";
    }
    if($shipping_row_mode === "method_and_cost"){
        $method_text = $shipping_method_text;
        if($method_text === ""){
            $method_text = ($shipping_note !== "") ? $shipping_note : "-";
        }
        $mobile_method_text = $method_text;
        echo '<tr>';
        echo '<td>روش ارسال</td>';
        echo '<td colspan="4">'.htmlspecialchars($method_text, ENT_QUOTES, 'UTF-8').'</td>';
        echo '<td>هزینه ارسال</td>';
        echo '<td>'.$shipping_text.'</td>';
        echo '</tr>';
    }else if($shipping_note !== ""){
        $shipping_text .= ' - '.htmlspecialchars($shipping_note, ENT_QUOTES, 'UTF-8');
    }
    if($shipping_row_mode !== "method_and_cost"){
        echo '<tr><td colspan="2">هزینه ارسال</td><td colspan="'.($total_columns-2).'">'.$shipping_text.'</td></tr>';
    }

    $code_text = ($lksr !== "") ? htmlspecialchars($lksr, ENT_QUOTES, 'UTF-8') : "-";
    $description_parts = array();
    $effects = array();
    foreach($code_effect_lines as $line){
        if(!is_array($line)) continue;
        $line_name = isset($line["name"]) ? trim((string)$line["name"]) : "";
        if($line_name === ""){
            $line_name = cart_summary_default_line_name(isset($line["type"]) ? (string)$line["type"] : "");
        }
        $description_parts[$line_name] = 1;
        $line_value = cart_summary_sale_line_value(
            $line,
            array(
                "cart_final_total" => $cart_final_total,
                "shipping_amount" => $shipping_amount,
                "at_time" => isset($summary["at_time"]) ? (string)$summary["at_time"] : date("Y-m-d H:i:s")
            )
        );
        if($line_value !== "-"){
            $effects[] = $line_value;
        }
    }
    $details = array();
    if(sizeof($description_parts) > 0){
        $details[] = implode(" | ",array_keys($description_parts));
    }
    if(sizeof($effects) > 0){
        $details[] = implode(" | ",$effects);
    }
    $effects_text = sizeof($details) > 0 ? htmlspecialchars(implode(": ",$details), ENT_QUOTES, 'UTF-8') : "-";
    $code_discount_amount = cart_summary_code_discount_total(
        $code_effect_lines,
        array(
            "cart_final_total" => $cart_final_total,
            "shipping_amount" => $shipping_amount,
            "at_time" => isset($summary["at_time"]) ? (string)$summary["at_time"] : date("Y-m-d H:i:s")
        )
    );
    $current_total_before_code_discount = (int)max($cart_final_total + $shipping_amount,0);
    if($lksr !== ""){
        $code_discount_amount = (int)max($current_total_before_code_discount - $pay_price,0);
    }
    $code_discount_text = price_sep((int)$code_discount_amount);
    $code_discount_bidi = $code_discount_text;
    if($discount_negative_display && $code_discount_amount > 0){
        $code_discount_bidi = '<bdi dir="ltr">'.price_sep((int)$code_discount_amount).'-</bdi>';
    }
    $show_discount_row = true;
    if($discount_row_only_with_code && $lksr === ""){
        $show_discount_row = false;
    }
    if($discount_row_before_totals && $show_discount_row){
        echo '<tr class="rgba">';
        echo '<td>کد تخفیف</td>';
        echo '<td><span dir="ltr">'.$code_text.'</span></td>';
        echo '<td>اثر روی سفارش</td>';
        echo '<td colspan="'.($total_columns-4).'">'.$effects_text.'</td>';
        if($discount_negative_display && $code_discount_amount > 0){
            echo '<td style="text-align:center;direction:ltr;unicode-bidi:isolate;">'.$code_discount_bidi.'</td>';
        }else{
            echo '<td>'.$code_discount_text.'</td>';
        }
        echo '</tr>';
    }

    $total_before_all_discount = (int)max($cart_base_total + $shipping_amount,0);
    $total_before_code_discount = $current_total_before_code_discount;
    $total_after_all_discount = (int)max($pay_price,0);
    $discounted_total_label = ($code_discount_amount > 0) ? "جمع قبل از کد تخفیف" : "جمع کل";
    $weight_text = $total_weight > 0 ? (price_sep($total_weight).' گرم') : '-';
    echo '<tr class="rgba checkout_summary_final_row">';
    echo '<td>وزن کل</td>';
    echo '<td colspan="2">'.$weight_text.'</td>';
    echo '<td colspan="3">'.$discounted_total_label.'</td>';
    echo '<td>'.price_sep(($code_discount_amount > 0) ? $total_before_code_discount : $total_after_all_discount).'</td>';

    echo '</tr>';

    if(!$discount_row_before_totals && $show_discount_row){
        echo '<tr class="rgba">';
        echo '<td>کد تخفیف</td>';
        echo '<td><span dir="ltr">'.$code_text.'</span></td>';
        echo '<td>اثر روی سفارش</td>';
        echo '<td colspan="'.($total_columns-4).'">'.$effects_text.'</td>';
        if($discount_negative_display && $code_discount_amount > 0){
            echo '<td style="text-align:center;direction:ltr;unicode-bidi:isolate;">'.$code_discount_bidi.'</td>';
        }else{
            echo '<td>'.$code_discount_text.'</td>';
        }
        echo '</tr>';
    }

    if($show_payable_row){
        echo '<tr class="rgba"><td colspan="'.($total_columns-1).'">مبلغ قابل پرداخت</td><td>'.price_sep($pay_price).'</td></tr>';
    }

    echo '</tbody>';
    echo '</table>';

    if($checkout_scrollable){
        echo '</div></div>';
    }

    if($checkout_mobile_cards){
        echo '<div class="cart_for_phone checkout_mobile_summary">';
        echo $mobile_cards;
        echo '<div class="cfp_item total checkout_mobile_totals_card">';
        echo '<h3>خلاصه</h3>';
        echo '<div class="cfpi_weight">وزن کل: '.$weight_text.'</div>';
        echo '<div class="cfpi_price">هزینه ارسال: '.$shipping_text_raw.'<br>روش ارسال: '.htmlspecialchars($mobile_method_text, ENT_QUOTES, 'UTF-8').'</div>';
        echo '<div class="cb"></div>';
        echo '<div class="cfpi_number">'.$discounted_total_label.': '.price_sep(($code_discount_amount > 0) ? $total_before_code_discount : $total_after_all_discount).'</div>';
        if($show_discount_row){
            echo '<div class="cfpi_price">کد: <span dir="ltr">'.$code_text.'</span><br>تخفیف: '.$code_discount_text.'</div>';
        }
        echo '<div class="cfpi_total_price">مبلغ قابل پرداخت: '.price_sep($total_after_all_discount).'</div>';

        echo '</div>';
        echo '</div>';
    }
}

function cart_render_checkout_payment_summary($cart_obj,$sales_obj,$shipping_display,$shipping_amount,$pay_price,$sale_total,$lksr = "",$shipping_note = "",$shipping_method_text = "")
{
    $items = (is_object($cart_obj) && isset($cart_obj->orders) && is_array($cart_obj->orders)) ? $cart_obj->orders : array();
    $rows_data = cart_summary_build_rows_from_items($items,date("Y-m-d H:i:s"));
    $sale_lines = cart_summary_collect_sale_lines($sales_obj);
    $code_lines = cart_summary_collect_code_effect_lines($sale_lines,$lksr);

    cart_render_detailed_summary_table(
        $rows_data["rows"],
        array(
            "title" => "پیش فاکتور خرید",
            "cart_base_total" => (int)$rows_data["base_total"],
            "cart_final_total" => (int)$rows_data["final_total"],
            "shipping_amount" => (int)$shipping_amount,
            "shipping_display" => (string)$shipping_display,
            "shipping_note" => (string)$shipping_note,
            "sale_total" => (int)$sale_total,
            "pay_price" => (int)$pay_price,
            "lksr" => (string)$lksr,
            "code_effect_lines" => $code_lines,
            "at_time" => date("Y-m-d H:i:s"),
            "cart_controls_enabled" => false,
            "show_payable_row" => true,
            "discount_row_only_with_code" => true,
            "discount_row_before_totals" => false,
            "shipping_row_mode" => "method_and_cost",
            "shipping_method_text" => (string)$shipping_method_text,
            "checkout_scrollable" => true,
            "checkout_mobile_cards" => true,
            "discount_negative_display" => true
        )
    );
}
function cart_render_paid_order_summary($oid,$options = array())
{
    $oid = (int)$oid;
    if($oid < 1 || !var_exist($oid,"orders","id")){
        return;
    }

    $order_cart_price = (int)getVarFromDB("orders","cart_price","id",$oid);
    $order_sale_total = (int)getVarFromDB("orders","sale_total","id",$oid);
    $order_pay_price = (int)getVarFromDB("orders","pay_price","id",$oid);
    $order_lksr = (string)getVarFromDB("orders","lksr","id",$oid);
    $qty_override_map = array();
    $financial_override = array();
    if(is_array($options)){
        if(isset($options["qty_override_map"]) && is_array($options["qty_override_map"])){
            foreach($options["qty_override_map"] as $soid => $qty){
                $soid = (int)$soid;
                if($soid > 0){
                    $qty_override_map[$soid] = max(0,(int)$qty);
                }
            }
        }
        if(isset($options["financial_override"]) && is_array($options["financial_override"])){
            $financial_override = $options["financial_override"];
        }
    }
    if(isset($financial_override["cart_price"])){
        $order_cart_price = (int)$financial_override["cart_price"];
    }
    if(isset($financial_override["sale_total"])){
        $order_sale_total = (int)$financial_override["sale_total"];
    }
    if(isset($financial_override["pay_price"])){
        $order_pay_price = (int)$financial_override["pay_price"];
    }
    $order_shipping = (int)max($order_pay_price + $order_sale_total - $order_cart_price, 0);

    $cart = new cart_read($oid);
    if($cart->orders === false){
        return;
    }
    if(count($qty_override_map) > 0){
        foreach($cart->orders as $idx => $item){
            if(!is_object($item) || !isset($item->soid)) continue;
            $item_soid = (int)$item->soid;
            if(array_key_exists($item_soid,$qty_override_map)){
                $item->no = max(0,(int)$qty_override_map[$item_soid]);
            }
        }
    }

    $rows_data = cart_summary_build_rows_from_items($cart->orders,$cart->create_date);
    $sales_obj = new sales("read",$oid);
    $sale_lines = cart_summary_collect_sale_lines($sales_obj);
    $code_lines = cart_summary_collect_code_effect_lines($sale_lines,$order_lksr);

    $summary = array(
        "title" => "خلاصه نهایی سفارش",
        "cart_base_total" => (int)$rows_data["base_total"],
        "cart_final_total" => (int)$order_cart_price,
        "shipping_amount" => (int)$order_shipping,
        "sale_total" => (int)$order_sale_total,
        "pay_price" => (int)$order_pay_price,
        "lksr" => (string)$order_lksr,
        "code_effect_lines" => $code_lines,
        "at_time" => (string)$cart->create_date
    );
    if(is_array($options)){
        if(isset($options["title"])){
            $summary["title"] = (string)$options["title"];
        }
        if(array_key_exists("discount_negative_display",$options)){
            $summary["discount_negative_display"] = !empty($options["discount_negative_display"]);
        }
        if(isset($options["shipping_row_mode"])){
            $summary["shipping_row_mode"] = (string)$options["shipping_row_mode"];
        }
        if(isset($options["shipping_method_text"])){
            $summary["shipping_method_text"] = (string)$options["shipping_method_text"];
        }
        if(isset($options["shipping_display"])){
            $summary["shipping_display"] = (string)$options["shipping_display"];
        }
        if(isset($options["shipping_note"])){
            $summary["shipping_note"] = (string)$options["shipping_note"];
        }
        if(!empty($options["qty_editable"])){
            $summary["qty_editable"] = true;
            $summary["qty_input_name"] = isset($options["qty_input_name"]) ? (string)$options["qty_input_name"] : "snappay_qty";
        }
        if(isset($options["qty_control_mode"])){
            $summary["qty_control_mode"] = (string)$options["qty_control_mode"];
        }
        if(!empty($options["qty_inputs_disabled"])){
            $summary["qty_inputs_disabled"] = true;
        }
        if(!empty($options["checkout_scrollable"])){
            $summary["checkout_scrollable"] = true;
        }
        if(!empty($options["checkout_mobile_cards"])){
            $summary["checkout_mobile_cards"] = true;
        }
    }

    cart_render_detailed_summary_table($rows_data["rows"],$summary);
}


class cart_read{
    public $create_date;
    public $orders = array();
    public function __construct($oid){
        if(var_exist($oid,"orders","id")){
            $this->create_date = getVarFromDB("orders","create_date","id",$oid);
            $st = "SELECT id,pid,weight,number,type FROM sub_orders WHERE oid = $oid AND del_flag = 0 AND (type = 'pack' OR type = 'box')";
            $k = 0;
            $res = return_sel_sql($st);
            while($row = $res->fetch_assoc()){
                switch($row["type"]){
                    case "pack":
                        $this->orders[$k] = new order_read($row["pid"],$row["number"],$row["weight"],$this->create_date,$row["id"]);
                        break;
                    case "box":
                        $this->orders[$k] = new order_box_read($row["pid"],$row["number"],$row["weight"],$this->create_date,$row["id"]);
                        break;
                }
                
                $k++;
            }
        }else{
            $this->orders = false;
        }
        
    }
    public function number(){
        $noo = 0;
        foreach($this->orders as $order){
            $noo += $order->no;
        }
        return $noo;
    }
    public function weight(){
        $otw = 0;
        foreach($this->orders as $order){
            $otw += ($order->no* $order->weight);
        }
        return $otw;
    }
    public function price(){
        $otp = 0;
        foreach($this->orders as $order){
            $otp += ($order->no* $order->price);
        }
        return $otp;
    }
}


class order_read{
    public $pid;
    public $soid = null;
    public $no = 1;
    public $weight = 50;
    public $price = 1000000;
    public $name;
    public function __construct($pid,$no,$weight,$create_date,$soid=null){
        if(var_exist($pid,"products","id")){
            $p_fin = product_finance($pid,$weight,$create_date);
            $this->pid = $pid;
            $this->soid = $soid;
            $this->no = $no;
            $this->weight = $weight;
            $this->price = $p_fin["price"];
            $this->name = getVarFromDB('products','name','id',$pid);
        }
    }
}
class order_box_read extends order_read{
    public $content = array();
    public $capacity;
    public function __construct($pid,$no,$weight,$create_date,$so_id){
        if(var_exist($pid,"products","id")){
            $p_fin = product_finance($pid,$weight,$create_date);
            $this->pid = $pid;
            $this->soid = $so_id;
            $this->no = $no;
            $this->name = getVarFromDB('products','name','id',$pid);
            $this->capacity = (int)$weight;
            $this->weight = 0;
            $this->price = $p_fin["price"];
            $st2 = "SELECT pid,weight,number FROM sub_orders WHERE so_id = $so_id AND del_flag = 0 AND type = 'piece'";
            $k = 0;
            $res2 = return_sel_sql($st2);
            while($row = $res2->fetch_assoc()){
                $this->content[$k] = new order_read($row["pid"],$row["number"],$row["weight"],$create_date);
                $this->weight += $this->content[$k]->weight*$this->content[$k]->no;
                $this->price += $this->content[$k]->price*$this->content[$k]->no;
                $k++;
            }
            
        }
    }
    public function c_list(){
        $c_list = "جعبه مخلوط انتخابی شامل: ";
        $c_list = "جعبه انتخابی شامل: ";
//        var_dump($this->content);
        if(sizeof($this->content) == 2){
            foreach($this->content as $bc_item){
                $c_list .= $bc_item->no." عدد ".$bc_item->name." و ";
            }
            $c_list = substr($c_list,0,strlen($c_list)-3);
            return $c_list;
        }
        foreach($this->content as $bc_item){
            $c_list .= $bc_item->no." عدد ".$bc_item->name."، ";
        }
        $c_list = substr($c_list,0,strlen($c_list)-3);
        return $c_list;
    }
}


function get_old_price($pid){
    if(var_exist($pid,"products_price",'pid')){
        include $GLOBALS["bu"].$GLOBALS['dbc_adrs'];
        $st = "SELECT price FROM products_price WHERE pid = ? ORDER BY start_time DESC LIMIT 1 OFFSET 1";
        $st = $mysqli->prepare($st);
        $st->bind_param('s',$pid);
        $st->execute();
        if(!$st->execute()){
            return NULL;
        }
        $st->store_result();
        $st->bind_result($old_price);
        $st->fetch();
        $old_price = explode(",",$old_price);
        return $old_price;
    }
    return NULL;
}

?>
