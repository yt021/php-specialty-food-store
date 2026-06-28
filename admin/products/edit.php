<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
$adrs = $bu."products/";
    if($_SESSION[$cf]->level == 1){
        if($_SESSION[$cf]->id == "new"){
            $fields = ["name","file_name","type","weight","price","profit"];
            $error = 0;
            foreach($fields as $field){
                if(isset($_POST[$field]) && check_value('text',$_POST[$field]))$row[$field] = check_value('text',$_POST[$field]);
                else{$error++;}
            }
            if($error === 0){
                $st = "INSERT INTO $tb (name,file_name,type) VALUES (?,?,?)";
                $st = $mysqli->prepare($st);
                $st->bind_param('sss',$row["name"],$row["file_name"],$row["type"]);
                $st->execute();
                
                $id = last_id($tb);
                
                $st = "INSERT INTO products_price (pid,weight,price,profit) VALUES (?,?,?,?)";
                $st = $mysqli->prepare($st);
                $st->bind_param('ssss',$id,$row["weight"],$row["price"],$row["profit"]);
                $st->execute();
                
                $_SESSION[$cf]->id = $id;
                
                copy($adrs."index.php",$adrs.$row["file_name"].".php"); 
                // --- Pinket Sync after new product ---
                require_once __DIR__ . '/../pinket/products.php';
                file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [EDIT] New product added, syncing products to Pinket\n", FILE_APPEND);
                $result = sendProductsToPinket($mysqli);
                file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [EDIT] New product Pinket API result: " . json_encode($result) . "\n", FILE_APPEND);
            }
        }else{
        $fields = ["name","category","keywords","description"];
        foreach($fields as $field){
            if(isset($_POST[$field]) && check_value("text",$_POST[$field])){
                updateInDB($tb,$field,$_POST[$field],"id",$_SESSION[$cf]->id);
            }
        }
        $fields = ["weight","price","profit"];
        $error = 0;
        foreach($fields as $field){
            if(isset($_POST[$field]) && check_value('text',$_POST[$field]))$row[$field] = check_value('text',$_POST[$field]);
            else{$error++;}
        }
        if($error === 0){
            $id = $_SESSION[$cf]->id;
            $st = "INSERT INTO products_price (pid,weight,price,profit) VALUES (?,?,?,?)";
            $st = $mysqli->prepare($st);
            $st->bind_param('ssss',$id,$row["weight"],$row["price"],$row["profit"]);
            $st->execute();
        }
        if(isset($_POST["show_order"])){
            updateInDB($tb,"show_order",$_POST["show_order"],"id",$_SESSION[$cf]->id);
        }
        $discount_weight = "";
        if(isset($_POST["discount_weight"])){
            $discount_weight = trim((string)$_POST["discount_weight"]);
        }
        if($discount_weight === ""){
            $discount_weight = (string)getVarFromDB("products_price","weight","pid",$_SESSION[$cf]->id,"start_time DESC");
        }

        $discount_price = "";
        if(isset($_POST["discount_price"])){
            $parts = explode(",",(string)$_POST["discount_price"]);
            $clean_parts = array();
            foreach($parts as $part){
                $clean_parts[] = trim((string)$part);
            }
            $discount_price = implode(",",$clean_parts);
        }

        $discount_start_at = null;
        if(isset($_POST["discount_s_day"]) && isset($_POST["discount_s_month"]) && isset($_POST["discount_s_year"])){
            $discount_start_at = createSQLTime($_POST["discount_s_year"],$_POST["discount_s_month"],$_POST["discount_s_day"]);
        }
        $discount_end_at = null;
        if(isset($_POST["discount_e_day"]) && isset($_POST["discount_e_month"]) && isset($_POST["discount_e_year"])){
            $discount_end_at = createSQLTime($_POST["discount_e_year"],$_POST["discount_e_month"],$_POST["discount_e_day"]);
        }
        if($discount_start_at && $discount_end_at && strtotime($discount_end_at) < strtotime($discount_start_at)){
            $tmp = $discount_start_at;
            $discount_start_at = $discount_end_at;
            $discount_end_at = $tmp;
        }

        $discount_active = isset($_POST["discount_active"]) ? 1 : 0;
        $discount_has_any_price = false;
        if($discount_price !== ""){
            foreach(explode(",",$discount_price) as $part){
                $part = trim((string)$part);
                if($part !== "" && is_numeric($part)){
                    $discount_has_any_price = true;
                    break;
                }
            }
        }

        $discount_requested = (
            isset($_POST["discount_weight"]) ||
            isset($_POST["discount_price"]) ||
            isset($_POST["discount_s_day"]) ||
            isset($_POST["discount_s_month"]) ||
            isset($_POST["discount_s_year"]) ||
            isset($_POST["discount_e_day"]) ||
            isset($_POST["discount_e_month"]) ||
            isset($_POST["discount_e_year"]) ||
            isset($_POST["discount_active"])
        );
        if($discount_requested){
            $pid = (int)$_SESSION[$cf]->id;
            $discount_table_ready = false;
            $discount_weight_price_ready = (product_discount_has_column("discount_weight") && product_discount_has_column("discount_price"));
            $st_chk = $mysqli->prepare("SELECT id FROM product_discounts WHERE pid = ? ORDER BY id DESC LIMIT 1");
            if($st_chk){
                $discount_table_ready = true;
                $pid_sql = (string)$pid;
                $st_chk->bind_param('s',$pid_sql);
                if($st_chk->execute()){
                    $res_chk = $st_chk->get_result();
                    $latest = $res_chk ? $res_chk->fetch_assoc() : null;
                }else{
                    $latest = null;
                }

                if($discount_weight_price_ready){
                    $dw_sql = ($discount_weight === "") ? null : (string)$discount_weight;
                    $dp_sql = ($discount_price === "") ? null : (string)$discount_price;
                    $da_sql = (string)$discount_active;
                    $start_sql = ($discount_start_at === null) ? null : (string)$discount_start_at;
                    $end_sql = ($discount_end_at === null) ? null : (string)$discount_end_at;

                    if($latest && isset($latest["id"])){
                        $did_sql = (string)$latest["id"];
                        $st_up = $mysqli->prepare("UPDATE product_discounts SET discount_weight = ?, discount_price = ?, start_at = ?, end_at = ?, is_active = ?, updated_at = NOW() WHERE id = ?");
                        if($st_up){
                            $st_up->bind_param('ssssss',$dw_sql,$dp_sql,$start_sql,$end_sql,$da_sql,$did_sql);
                            $st_up->execute();
                        }
                    }else{
                        $pid_ins = (string)$pid;
                        $st_in = $mysqli->prepare("INSERT INTO product_discounts (pid,discount_weight,discount_price,start_at,end_at,is_active,created_at,updated_at) VALUES (?,?,?,?,?,?,NOW(),NOW())");
                        if($st_in){
                            $st_in->bind_param('ssssss',$pid_ins,$dw_sql,$dp_sql,$start_sql,$end_sql,$da_sql);
                            $st_in->execute();
                        }
                    }
                }
            }
            if($discount_table_ready && function_exists('product_discount_sync_sale_state')){
                product_discount_sync_sale_state((int)$_SESSION[$cf]->id);
            }
        }
        }
    }
?>
<?php
        }
    }
?>

<?php
// --- Pinket Sync Integration ---
file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [EDIT] --- START ---\n", FILE_APPEND);

try {
    file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [EDIT] About to require products.php\n", FILE_APPEND);
    require_once(__DIR__ . '/../pinket/products.php');
    file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [EDIT] products.php required successfully\n", FILE_APPEND);

    file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [EDIT] About to call sendProductsToPinket\n", FILE_APPEND);
    $result = sendProductsToPinket($mysqli);
    file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [EDIT] sendProductsToPinket result: " . json_encode($result) . "\n", FILE_APPEND);
} catch (Throwable $e) {
    file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [EDIT] Exception: " . $e->getMessage() . "\n", FILE_APPEND);
}
file_put_contents(__DIR__ . '/../pinket/debug_api_send.log', date('Y-m-d H:i:s') . " [EDIT] --- END ---\n", FILE_APPEND);
?>
