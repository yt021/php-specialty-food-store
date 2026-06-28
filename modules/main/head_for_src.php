<?php

    if(isset($indexed)){

        if($indexed == 1){?>

<?php

    if(isset($_GET,$_GET['lksr']) && !isset($_SESSION['lksr'])){
        if(!function_exists("cart_discount_code_validate_for_cart")){
            include_once $GLOBALS['bu']."modules/cart/cart_funcs.php";
        }

        $lksr_raw = trim((string)$_GET['lksr']);
        $is_ref_link = false;
        $is_sales_code = false;
        $lksr_to_store = "";

        if($lksr_raw !== ""){
            $is_ref_link = var_exist($lksr_raw,'ref_links','link_val');
            if(function_exists("cart_discount_code_validate_for_cart")){
                $mode = "first";
                $uid = 0;
                if(isset($_SESSION["logged"]) && is_object($_SESSION["logged"]) && isset($_SESSION["logged"]->uid)){
                    $mode = "new";
                    $uid = (int)$_SESSION["logged"]->uid;
                }
                $cart_cost = 0;
                if(isset($_SESSION["cart"]) && is_object($_SESSION["cart"]) && method_exists($_SESSION["cart"],"price")){
                    $cart_cost = (int)$_SESSION["cart"]->price();
                }
                $send_cost = 0;
                if(isset($_SESSION["send_cost"]) && is_numeric($_SESSION["send_cost"])){
                    $send_cost = (int)$_SESSION["send_cost"];
                }
                $admin_state = isset($_SESSION["a_logged"]) ? 0 : 1;
                $validation = cart_discount_code_validate_for_cart(
                    $lksr_raw,
                    $mode,
                    $uid,
                    $cart_cost,
                    $send_cost,
                    $admin_state,
                    true,
                    array(
                        "ignore_cart_requirements" => true,
                        "ignore_send_applicable" => true
                    )
                );
                if(!empty($validation["ok"])){
                    $is_sales_code = true;
                    $lksr_to_store = (string)$validation["normalized_code"];
                }
            }
        }

        if(($is_ref_link || $is_sales_code) && $lksr_to_store === ""){
            $lksr_to_store = $lksr_raw;
        }

        if($lksr_to_store !== ""){

            $ipAddress = $_SERVER['REMOTE_ADDR'];

            if(array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {

                $forwarded_for = explode(',', (string)$_SERVER['HTTP_X_FORWARDED_FOR']);
                $ipAddress = trim((string)end($forwarded_for));

            }

            $browser = $_SERVER['HTTP_USER_AGENT'];

            $referer = NULL;

            if(isset($_SERVER['HTTP_REFERER']))$referer = $_SERVER['HTTP_REFERER'];

            $_SESSION['lksr'] = $lksr_to_store;

            $st = "INSERT INTO link_users (link,ip,brw,ref) VALUES (?,?,?,?)";

            $st = $mysqli->prepare($st);

            $st->bind_param('ssss',$_SESSION['lksr'],$ipAddress,$browser,$referer);

            $st->execute();

        }

    }

?>



<?php

        }

    }

?>
