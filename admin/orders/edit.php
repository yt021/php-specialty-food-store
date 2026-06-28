<?php
file_put_contents(__DIR__.'/pinket_debug.log', "edit.php loaded\n", FILE_APPEND);
require_once __DIR__ . '/../pinket/helpers.php';
include_once $bu."modules/cart/cart_funcs.php";
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $has_products_save_action = isset($_POST["edit_products_save"]) || isset($_POST["edit_products_save_send"]);
    if($has_products_save_action){
        $can_edit_products = isset($_SESSION["a_logged"]) && is_object($_SESSION["a_logged"]) && method_exists($_SESSION["a_logged"], 'get_level') && $_SESSION["a_logged"]->get_level() >= 2;
        if(!$can_edit_products){
            $_SESSION["order_items_msg"] = "EAccess denied.";
        } else {
            $oid = isset($_SESSION[$cf]->id) ? (int)$_SESSION[$cf]->id : 0;
            if($oid < 1 || !var_exist($oid, "orders", "id")){
                $_SESSION["order_items_msg"] = "EOrder is invalid.";
            } else if(
                !isset($_POST['order_items_csrf']) ||
                !isset($_SESSION['order_items_csrf']) ||
                !is_string($_POST['order_items_csrf']) ||
                !is_string($_SESSION['order_items_csrf']) ||
                !hash_equals($_SESSION['order_items_csrf'], $_POST['order_items_csrf'])
            ){
                $_SESSION["order_items_msg"] = "EUnsafe request (CSRF). Please retry.";
            } else {
                $qty_input = isset($_POST['item_qty']) && is_array($_POST['item_qty']) ? $_POST['item_qty'] : [];
                $st = $mysqli->prepare("SELECT id,number FROM sub_orders WHERE oid = ? AND del_flag = 0 AND (type = 'pack' OR type = 'box') ORDER BY id ASC");
                $st->bind_param('i', $oid);
                $st->execute();
                $res = $st->get_result();
                $existing_items = [];
                while($row = $res->fetch_assoc()){
                    $existing_items[(int)$row['id']] = (int)$row['number'];
                }
                $st->close();

                if(count($existing_items) === 0){
                    $_SESSION["order_items_msg"] = "ENo editable order items found.";
                } else {
                    $new_qty_map = [];
                    $positive_rows = 0;
                    foreach($existing_items as $so_id => $cur_no){
                        $new_no = $cur_no;
                        if(isset($qty_input[$so_id])){
                            $new_no = (int)$qty_input[$so_id];
                        }
                        if($new_no < 0) $new_no = 0;
                        $new_qty_map[$so_id] = $new_no;
                        if($new_no > 0) $positive_rows++;
                    }

                    if($positive_rows < 1){
                        $_SESSION["order_items_msg"] = "EAt least one product must remain in the order.";
                    } else {
                        $order_before = getRow($mysqli, "orders", "id = ?", [$oid], "i");
                        if(!$order_before){
                            $_SESSION["order_items_msg"] = "EOrder not found for recalculation.";
                        } else {
                            try{
                                $mysqli->begin_transaction();

                                $st_update_no = $mysqli->prepare("UPDATE sub_orders SET number = ?, del_flag = 0 WHERE id = ?");
                                $st_delete = $mysqli->prepare("UPDATE sub_orders SET del_flag = 1 WHERE id = ?");

                                foreach($new_qty_map as $so_id => $new_no){
                                    if($new_no <= 0){
                                        $st_delete->bind_param('i', $so_id);
                                        $st_delete->execute();
                                    } else {
                                        $st_update_no->bind_param('ii', $new_no, $so_id);
                                        $st_update_no->execute();
                                    }
                                }

                                $st_update_no->close();
                                $st_delete->close();

                                $cart_after = new cart_read($oid);
                                if($cart_after->orders === false || count($cart_after->orders) === 0){
                                    throw new Exception("Cart became empty after update.");
                                }

                                $new_cart_price = (int)$cart_after->price();

                                $old_cart_price = (int)$order_before['cart_price'];
                                $old_cart_pure = (int)$order_before['cart_pure'];
                                $old_sale_total = (int)$order_before['sale_total'];
                                $old_pay_price = (int)$order_before['pay_price'];

                                $old_send_cost = (int)max($old_pay_price + $old_sale_total - $old_cart_price, 0);
                                $old_cart_sale = (int)max($old_cart_price - $old_cart_pure, 0);
                                $old_send_sale = (int)max($old_sale_total - $old_cart_sale, 0);

                                $new_cart_sale = (int)min($old_cart_sale, $new_cart_price);
                                $new_send_sale = (int)min($old_send_sale, $old_send_cost);
                                $new_full_price = (int)$new_cart_price + (int)$old_send_cost;
                                $new_sale_total = (int)min($new_cart_sale + $new_send_sale, $new_full_price);
                                $new_cart_pure = (int)max($new_cart_price - $new_cart_sale, 0);
                                $new_pay_price = (int)max($new_full_price - $new_sale_total, 0);

                                $st_order = $mysqli->prepare("UPDATE orders SET cart_price = ?, cart_pure = ?, sale_total = ?, pay_price = ? WHERE id = ?");
                                $st_order->bind_param('iiiii', $new_cart_price, $new_cart_pure, $new_sale_total, $new_pay_price, $oid);
                                $st_order->execute();
                                $st_order->close();

                                $mysqli->commit();

                                $_SESSION["order_items_msg"] = "DProducts updated successfully.";

                                if(isset($_POST["edit_products_save_send"])){
                                    $_SESSION[$cf]->level = 2;
                                    $_POST["snappay_update"] = "1";
                                    $_POST["snappay_confirm"] = "yes";
                                    include $bu."$module_name/$cf/snappay_update.php";
                                }
                            } catch(Exception $e){
                                $mysqli->rollback();
                                $_SESSION["order_items_msg"] = "EFailed to update order products.";
                            }
                        }
                    }
                }
            }
        }
    }

    if($_SESSION[$cf]->level == 2 && !$has_products_save_action){
//        delete order
        if($_POST["edit"] == "delete"){
            updateInDB($tb,"del_flag",1,"id",$_SESSION[$cf]->id);
            // Notify Pinket if this is a Pinket order
            $order = getRow($mysqli, "orders", "id = ?", [$_SESSION[$cf]->id], "i");
            if ($order && !empty($order['unified_id']) && !ctype_digit($order['unified_id'])) {
                require_once $bu . "admin/pinket/status-webhook.php";
                updateOrderStatusInPinket($_SESSION[$cf]->id);
            }
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = null;
        }
//        level down order
        if($_POST["edit"] == "return"){
            $state = (int)getVarFromDB($tb,"state","id",$_SESSION[$cf]->id);
            $state = $state - 1;
            $state = max([$state,0]);
            updateInDB($tb,"state",$state,"id",$_SESSION[$cf]->id);
            // Notify Pinket if this is a Pinket order
            $order = getRow($mysqli, "orders", "id = ?", [$_SESSION[$cf]->id], "i");
            if ($order && !empty($order['unified_id']) && !ctype_digit($order['unified_id'])) {
                require_once $bu . "admin/pinket/status-webhook.php";
                updateOrderStatusInPinket($_SESSION[$cf]->id);
            }
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = null;
        }
//        level up order
        if($_POST["edit"] == "submit"){
            $state = getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state);
            updateInDB($tb,"state",$state,"id",$_SESSION[$cf]->id);
            // Notify Pinket if this is a Pinket order
            $order = getRow($mysqli, "orders", "id = ?", [$_SESSION[$cf]->id], "i");
            if ($order && !empty($order['unified_id']) && !ctype_digit($order['unified_id'])) {
                file_put_contents(__DIR__.'/pinket_debug.log', "Single submit Pinket for OID: " . $_SESSION[$cf]->id . "\n", FILE_APPEND);
                require_once __DIR__ . '/../pinket/status-webhook.php';
                updateOrderStatusInPinket($_SESSION[$cf]->id);
            }
            if($_SESSION[$cf]->state == "new"){
                updateInDB($tb,"payment_date",date("Y-m-d H:i:s"),"id",$_SESSION[$cf]->id);
            }
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = null;
        }
//        edit pay req
        if(isset($_POST["pay_request_id"]) && check_value("text",$_POST["pay_request_id"])){
            updateInDB($tb,"pay_request_id",check_value("text",$_POST["pay_request_id"]),"id",$_SESSION[$cf]->id);
            
        }
//        edit pay id
        if(isset($_POST["pay_id"]) && check_value("text",$_POST["pay_id"])){
            updateInDB($tb,"pay_id",check_value("text",$_POST["pay_id"]),"id",$_SESSION[$cf]->id);
            
        }
//        edit order detail
        if(isset($_POST["order_detail"]) && check_value("text",$_POST["order_detail"])){
            updateInDB($tb,"order_detail",check_value("text",$_POST["order_detail"]),"id",$_SESSION[$cf]->id);
        }
//        edit post reference id
        if(isset($_POST["tp_id"]) && var_exist($_POST["tp_id"],"transporters","id")){
            if($_POST["tp_id"] == 1){
                if(isset($_POST["post_ref_id"]) ){
                    $post_ref = check_value("text",$_POST["post_ref_id"]);
                    $post_ref = str_replace(" ","",$post_ref);
                    if(strlen($post_ref) == 24){
                        updateInDB($tb,"post_ref_id",$post_ref,"id",$_SESSION[$cf]->id);
                    }
                }
            }else{
                updateInDB($tb,"post_ref_id",$_POST["tp_id"],"id",$_SESSION[$cf]->id);
            }
        }
//        edit send date and shift
        if(isset($_POST["shift"])){
            $change_shift = 1;
            switch($_POST["shift"]){
                case 1:
                case 2:
                    break;
                case 3:
                    $day_fields = ["day","month","year"];
                    $d_f_e = 0;
                    foreach($day_fields as $f){
                        if(isset($_POST[$f])){
                            $d_f_e++;
                            $date[$f] = $_POST[$f];
                        }
                    }
                    if($d_f_e == 3){
                        updateInDB($tb,"p_send_date",cSQLTimefS_hour($date,12),"id",$_SESSION[$cf]->id);
                        updateInDB($tb,"recieve_date",cSQLTimefS_hour($date,12),"id",$_SESSION[$cf]->id);
                    }
                    break;
                default:
                    $change_shift = 0;
                    $day_fields = ["id","alt"];
                    $error = 0;
                    foreach($day_fields as $f){
                        if(!isset($_POST[$f]))
                        $error=1;
                    }
                    if($error != 1){
                        if(var_exist($_POST["id"],"send_date","id")){
                            $send_date=new send_date($_POST["id"],$_POST["shift"],$_POST["alt"]);
                            updateInDB($tb,"p_send_date",$send_date->sendDate_CSQL(),"id",$_SESSION[$cf]->id);
                            updateInDB($tb,"recieve_date",$send_date->recDate_CSQL(),"id",$_SESSION[$cf]->id);
                            $change_shift = 1;
                        }
                    }
                    break;
            }
            if($change_shift)
            updateInDB($tb,"recieve_shift",check_value("text",$_POST["shift"]),"id",$_SESSION[$cf]->id);
        }
//        edit sale & invoice prices
        if(isset($_POST["invoice"])){
            if($_POST["invoice"] == "sale" && isset($_POST["cart_sale"]) && isset($_POST["send_sale"])
            ){
                if($_SESSION["a_logged"]->get_level() == 2){
                    $fields[1] = ["pay_price","sale_total","cart_pure","cart_price"];
                    foreach($fields[1] as $f){
                        $invoice_value[$f] = getVarFromDB($tb,$f,"id",$_SESSION[$cf]->id);
                    }
                    $invoice_value["cart_sale"] = $_POST["cart_sale"];
                    $invoice_value["send_sale"] = $_POST["send_sale"];
                    
                    
                    
                    $invoice_value["send_cost"] = 
                    ($invoice_value["pay_price"] - $invoice_value["cart_pure"]) + 
                    ($invoice_value["sale_total"] - ($invoice_value["cart_price"] - $invoice_value["cart_pure"]));
                    
                    $invoice_value["full_price"] = 
                    $invoice_value["cart_price"] + 
                    $invoice_value["send_cost"];
                    
                    
                    if($invoice_value["cart_sale"]>$invoice_value["cart_price"])
                    $invoice_value["cart_sale"]=$invoice_value["cart_price"];
                    
                    $invoice_value["cart_pure"] = $invoice_value["cart_price"] - $invoice_value["cart_sale"];
                    
                    if($invoice_value["send_sale"]>$invoice_value["send_cost"])
                    $invoice_value["send_sale"]=$invoice_value["send_cost"];
                    
                    $invoice_value["sale_total"] = $invoice_value["cart_sale"] + $invoice_value["send_sale"];
                    
                    
                    $invoice_value["pay_price"] = $invoice_value["full_price"] - $invoice_value["sale_total"];
                    
                    unset($fields[1][3]);
                    foreach($fields[1] as $f){
                        updateInDB($tb,$f,$invoice_value[$f],"id",$_SESSION[$cf]->id);
                    }
                }
            }
        }
    }
?>
<?php
    if($_SESSION[$cf]->level == 3){
        if(isset($_FILES["file"])){
            $dir = $bu."admin/orders/post_excel/";
            $size_limit = 3;
            $file = $_FILES["file"];
            $ff = pathinfo(basename($file["name"]),PATHINFO_EXTENSION);
            if(($ff == "xlsx" || $ff == "xls" ) && checkFileType($ff) && $file["size"] < $size_limit*1024*1024){
                $file_name = checkFileType($ff).(time()*4).".$ff";
                $target_file = $dir.$file_name;
                if(move_uploaded_file($file["tmp_name"],$target_file)){
                    $type = checkFileType($ff);
                    $st = "INSERT INTO orders_post_xlsx (type,format,file_name) VALUES (?,?,?)";
                    $st = $mysqli->prepare($st);
                    $st->bind_param('sss',$type,$ff,$file_name);
                    $st->execute();
                    $_SESSION[$cf]->sub_id = last_id("orders_post_xlsx");
                }
            }
        }
    }
    if($_SESSION[$cf]->level == 5){
        if(isset($_FILES["file"])){
            $dir = $bu."admin/orders/pay_excel/";
            $size_limit = 3;
            $file = $_FILES["file"];
            $ff = pathinfo(basename($file["name"]),PATHINFO_EXTENSION);
            if($ff == "xlsx" && checkFileType($ff) && $file["size"] < $size_limit*1024*1024){
                $file_name = checkFileType($ff).(time()*4).".$ff";
                $target_file = $dir.$file_name;
                if(move_uploaded_file($file["tmp_name"],$target_file)){
                    $type = checkFileType($ff);
                    $st = "INSERT INTO orders_pay_xlsx (type,format,file_name) VALUES (?,?,?)";
                    $st = $mysqli->prepare($st);
                    $st->bind_param('sss',$type,$ff,$file_name);
                    $st->execute();
                    $_SESSION[$cf]->sub_id = $st->insert_id;
                }
            }
        }
    }
?>
<?php
        }
    }
?>
