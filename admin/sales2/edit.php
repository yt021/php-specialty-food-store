<?php

    if(isset($indexed)){

        if($indexed == 1){?>

<?php
    if(!function_exists("cart_discount_code_normalize")){
        include_once $GLOBALS['bu']."modules/cart/cart_funcs.php";
    }

    if($_SESSION[$cf]->level == 1){
        $allowed_types = array("percent","amount","fixed_price","send","gift");
        $errors = array();
        $form_data = array();

        $form_data["name"] = isset($_POST["name"]) ? trim((string)$_POST["name"]) : "";
        $raw_type = isset($_POST["type"]) ? (string)$_POST["type"] : "";
        $form_data["type"] = cart_sale_normalize_type($raw_type);
        if(!in_array($form_data["type"],$allowed_types,true)){
            $form_data["type"] = "amount";
        }

        $form_data["amount"] = isset($_POST["amount"]) ? trim((string)$_POST["amount"]) : "";
        $form_data["lksr"] = isset($_POST["lksr"]) ? cart_discount_code_normalize($_POST["lksr"]) : "";

        $form_data["min_buy"] = (string)max((int)round(cart_sale_parse_numeric(isset($_POST["min_buy"]) ? $_POST["min_buy"] : 0,0)),0);
        $form_data["min_t_buy"] = (string)max((int)round(cart_sale_parse_numeric(isset($_POST["min_t_buy"]) ? $_POST["min_t_buy"] : 0,0)),0);
        $form_data["min_n_buy"] = (string)max((int)round(cart_sale_parse_numeric(isset($_POST["min_n_buy"]) ? $_POST["min_n_buy"] : 0,0)),0);
        $form_data["admin_state"] = (isset($_POST["admin_state"]) && (string)$_POST["admin_state"] === "0") ? "0" : "1";

        $s_day = isset($_POST["s_day"]) ? (int)$_POST["s_day"] : 0;
        $s_month = isset($_POST["s_month"]) ? (int)$_POST["s_month"] : 0;
        $s_year = isset($_POST["s_year"]) ? (int)$_POST["s_year"] : 0;
        $e_day = isset($_POST["e_day"]) ? (int)$_POST["e_day"] : 0;
        $e_month = isset($_POST["e_month"]) ? (int)$_POST["e_month"] : 0;
        $e_year = isset($_POST["e_year"]) ? (int)$_POST["e_year"] : 0;

        $form_data["s_day"] = (string)$s_day;
        $form_data["s_month"] = (string)$s_month;
        $form_data["s_year"] = (string)$s_year;
        $form_data["e_day"] = (string)$e_day;
        $form_data["e_month"] = (string)$e_month;
        $form_data["e_year"] = (string)$e_year;

        if($form_data["name"] === ""){
            $errors[] = "نام تخفیف را وارد کنید.";
        }

        if($s_day < 1 || $s_month < 1 || $s_year < 1 || $e_day < 1 || $e_month < 1 || $e_year < 1){
            $errors[] = "تاریخ شروع و پایان تخفیف را کامل انتخاب کنید.";
            $start_date = "";
            $end_date = "";
        }else{
            $start_date = createSQLTime($s_year,$s_month,$s_day);
            $end_date = createSQLTime($e_year,$e_month,$e_day);
            if(strtotime($end_date) < strtotime($start_date)){
                $errors[] = "تاریخ پایان باید بزرگ‌تر یا مساوی تاریخ شروع باشد.";
            }
        }

        $amount_store = "0";
        switch($form_data["type"]){
            case "percent":
                $percent = cart_sale_parse_numeric($form_data["amount"],-1);
                if($percent <= 0 || $percent > 100){
                    $errors[] = "برای نوع درصد، مقدار باید بین 1 تا 100 باشد.";
                }
                $amount_store = (string)$percent;
                break;
            case "amount":
            case "fixed_price":
                $money = cart_sale_parse_numeric($form_data["amount"],-1);
                if($money < 0){
                    $errors[] = "برای این نوع تخفیف، مقدار نمی‌تواند منفی باشد.";
                }
                $amount_store = (string)(int)round(max($money,0));
                break;
            case "send":
                $amount_store = "0";
                break;
            case "gift":
                $gift_id = (int)round(max(cart_sale_parse_numeric($form_data["amount"],0),0));
                $amount_store = (string)$gift_id;
                if($gift_id < 1 || !var_exist($gift_id,"products","id")){
                    $errors[] = "محصول هدیه معتبر نیست.";
                }else{
                    $gift_type = (string)getVarFromDB("products","type","id",$gift_id);
                    $gift_del = (int)getVarFromDB("products","del_flag","id",$gift_id);
                    if($gift_type !== "pack" || $gift_del !== 0){
                        $errors[] = "هدیه باید از بین محصولات فعال نوع پک انتخاب شود.";
                    }
                }
                break;
        }

        if($form_data["lksr"] !== ""){
            $duplicate_id = 0;
            $exclude_id = ($_SESSION[$cf]->id === "new") ? 0 : (int)$_SESSION[$cf]->id;
            $st_dup = $mysqli->prepare("SELECT id,lksr FROM $tb WHERE del_flag = 0 AND lksr IS NOT NULL AND lksr <> ''");
            if($st_dup && $st_dup->execute()){
                $res_dup = $st_dup->get_result();
                while($row_dup = $res_dup->fetch_assoc()){
                    $row_id = (int)$row_dup["id"];
                    if($exclude_id > 0 && $row_id === $exclude_id){
                        continue;
                    }
                    $row_code = cart_discount_code_normalize((string)$row_dup["lksr"]);
                    if($row_code !== "" && $row_code === $form_data["lksr"]){
                        $duplicate_id = $row_id;
                        break;
                    }
                }
            }
            if($duplicate_id > 0){
                $errors[] = "این کد تخفیف قبلا ثبت شده است (شناسه: ".$duplicate_id.").";
            }
        }

        if(sizeof($errors) === 0){
            if($_SESSION[$cf]->id == "new"){
                $st = $mysqli->prepare("INSERT INTO $tb (name,type,amount,start_date,end_date,min_buy,min_t_buy,min_n_buy,admin_state,lksr) VALUES (?,?,?,?,?,?,?,?,?,?)");
                if(!$st){
                    $errors[] = "خطا در ثبت تخفیف جدید.";
                }else{
                    $st->bind_param(
                        'ssssssssss',
                        $form_data["name"],
                        $form_data["type"],
                        $amount_store,
                        $start_date,
                        $end_date,
                        $form_data["min_buy"],
                        $form_data["min_t_buy"],
                        $form_data["min_n_buy"],
                        $form_data["admin_state"],
                        $form_data["lksr"]
                    );
                    if(!$st->execute()){
                        $errors[] = "ثبت تخفیف جدید ناموفق بود.";
                    }
                }
                if(sizeof($errors) === 0){
                    unset($_SESSION[$cf]->form_errors);
                    unset($_SESSION[$cf]->form_data);
                    unset($_SESSION[$cf]->form_data_id);
                    $_SESSION[$cf] = new where($cf);
                }
            }else if(var_exist($_SESSION[$cf]->id,$tb,"id")){
                $edit_id = (int)$_SESSION[$cf]->id;
                $updates = array(
                    "name" => $form_data["name"],
                    "type" => $form_data["type"],
                    "amount" => $amount_store,
                    "min_buy" => $form_data["min_buy"],
                    "min_t_buy" => $form_data["min_t_buy"],
                    "min_n_buy" => $form_data["min_n_buy"],
                    "admin_state" => $form_data["admin_state"],
                    "start_date" => $start_date,
                    "end_date" => $end_date,
                    "lksr" => $form_data["lksr"]
                );
                foreach($updates as $field_name=>$field_value){
                    updateInDB($tb,$field_name,$field_value,"id",$edit_id);
                }
                unset($_SESSION[$cf]->form_errors);
                unset($_SESSION[$cf]->form_data);
                unset($_SESSION[$cf]->form_data_id);
            }else{
                $errors[] = "شناسه تخفیف برای ویرایش معتبر نیست.";
            }
        }

        if(sizeof($errors) > 0){
            $_SESSION[$cf]->form_errors = $errors;
            $_SESSION[$cf]->form_data = $form_data;
            $_SESSION[$cf]->form_data_id = $_SESSION[$cf]->id;
        }
    }

?>

<?php

        }

    }

?>
