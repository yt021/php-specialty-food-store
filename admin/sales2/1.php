<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(!function_exists("cart_sale_normalize_type")){
        include_once $GLOBALS['bu']."modules/cart/cart_funcs.php";
    }
?>
        <form action="<?php echo $URL; ?>" method="post" id="sales_edit_form">
<?php
    $fields = array("name","type","amount","start_date","end_date","min_buy","min_t_buy","min_n_buy","admin_state","lksr");
    $data = array();
    if($_SESSION[$cf]->id == "new"){
        foreach($fields as $f){
            $data[$f] = "";
        }
        $data["type"] = "amount";
        $data["admin_state"] = "1";
    }else{
        foreach($fields as $f){
            $data[$f] = getVarFromDB($tb,$f,"id",$_SESSION[$cf]->id);
        }
    }

    $form_data_id = isset($_SESSION[$cf]->form_data_id) ? $_SESSION[$cf]->form_data_id : null;
    if(isset($_SESSION[$cf]->form_data) && is_array($_SESSION[$cf]->form_data) && $form_data_id === $_SESSION[$cf]->id){
        foreach($_SESSION[$cf]->form_data as $k=>$v){
            $data[$k] = $v;
        }
    }

    $form_errors = array();
    if(isset($_SESSION[$cf]->form_errors) && is_array($_SESSION[$cf]->form_errors) && $form_data_id === $_SESSION[$cf]->id){
        $form_errors = $_SESSION[$cf]->form_errors;
        unset($_SESSION[$cf]->form_errors);
    }

    $types = array(
        "percent"=>"درصد",
        "amount"=>"مبلغ",
        "fixed_price"=>"قیمت نهایی سبد",
        "send"=>"ارسال رایگان",
        "gift"=>"هدیه"
    );
    $data_type = cart_sale_normalize_type((string)$data["type"]);
    if(!array_key_exists($data_type,$types)){
        $data_type = "amount";
    }
    $data["type"] = $data_type;

    include_once $GLOBALS['bu']."modules/jdf.php";
    $s_day = 0; $s_month = 0; $s_year = 0;
    $e_day = 0; $e_month = 0; $e_year = 0;
    if($data["start_date"]){
        $s_time = strtotime($data["start_date"]);
        $s_day = (int)jdate("j",(int)$s_time,"","","en");
        $s_month = (int)jdate("n",(int)$s_time,"","","en");
        $s_year = (int)jdate("o",(int)$s_time,"","","en");
    }else{
        $s_day = (int)jdate("j","","","","en");
        $s_month = (int)jdate("n","","","","en");
        $s_year = (int)jdate("o","","","","en");
    }
    if($data["end_date"]){
        $e_time = strtotime($data["end_date"]);
        $e_day = (int)jdate("j",(int)$e_time,"","","en");
        $e_month = (int)jdate("n",(int)$e_time,"","","en");
        $e_year = (int)jdate("o",(int)$e_time,"","","en");
    }else{
        $e_day = $s_day;
        if($s_month != 12){
            $e_month = $s_month + 1;
            $e_year = $s_year;
        }else{
            $e_month = 1;
            $e_year = $s_year + 1;
        }
    }

    if(isset($data["s_day"])) $s_day = (int)$data["s_day"];
    if(isset($data["s_month"])) $s_month = (int)$data["s_month"];
    if(isset($data["s_year"])) $s_year = (int)$data["s_year"];
    if(isset($data["e_day"])) $e_day = (int)$data["e_day"];
    if(isset($data["e_month"])) $e_month = (int)$data["e_month"];
    if(isset($data["e_year"])) $e_year = (int)$data["e_year"];

    $s_l_year = min((int)jdate("Y","","","","en"),$s_year)-1;
    $e_l_year = max((int)jdate("Y","","","","en"),$e_year)+1;
?>
            <style type="text/css">
                #sales_edit_form .ssrv{
                    height:auto;
                    min-height:200px;
                }
                #sales_edit_form .ssrv div.form{
                    height:auto;
                    overflow:visible;
                }
                .sales_form_grid{
                    display:grid;
                    grid-template-columns:1fr;
                    gap:14px;
                }
                .sales_form_grid .form_item::after{
                    content:"";
                    display:block;
                    clear:both;
                }
                .sales_type_hint{
                    margin-top:8px;
                }
                .sales_code_preview{
                    margin-top:8px;
                }
                .sales_amount_wrap .user_hint{
                    margin-top:8px;
                }
                .sales_error_box{
                    margin-bottom:14px;
                }
                @media (max-width:760px){
                    .sales_form_grid .date_holder{
                        display:flex;
                        flex-direction:column;
                        gap:8px;
                    }
                }
            </style>

<?php if(sizeof($form_errors) > 0){ ?>
            <div class="user_hint error sales_error_box">
                <ol>
<?php foreach($form_errors as $err_item){ ?>
                    <li><?php echo htmlspecialchars((string)$err_item,ENT_QUOTES,'UTF-8'); ?></li>
<?php } ?>
                </ol>
            </div>
<?php } ?>

            <div class="ssrv">
                <div class="ssrv_title">مشخصات تخفیف</div>
                <div class="ssrv_dtl">
                    <div class="form fr sales_form_grid">
                        <div class="form_item">
                            <label for="sale_name">نام:</label>
                            <input id="sale_name" name="name" type="text" value="<?php echo htmlspecialchars((string)$data["name"],ENT_QUOTES,'UTF-8'); ?>">
                        </div>

                        <div class="form_item">
                            <label for="sale_type">نوع:</label>
                            <select id="sale_type" name="type" onchange="loadSaleAmountField(this.value);updateSaleTypeHint(this.value);">
<?php
                            foreach($types as $key=>$value){
                                $selected = ($key === $data_type) ? " selected " : "";
                                echo '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
                            }
?>
                            </select>
                            <div id="sale_type_hint" class="user_hint sales_type_hint"></div>
                        </div>

                        <div id="amount_input" class="form_item sales_amount_wrap">
                            <?php include($bu."modules/admin/sales_amount.php"); ?>
                        </div>

                        <div class="form_item">
                            انتخاب شروع بازه زمانی:
                            <div class="date_holder">
                                <select name="s_day" required>
                                    <?php
                                        for($i=1;$i<=31;$i++){
                                            $selected = ($s_day == $i) ? " selected " : "";
                                            echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                        }
                                    ?>
                                </select>

                                <select name="s_month" required>
                                    <?php
                                        for($i=1;$i<=12;$i++){
                                            $selected = ($s_month == $i) ? " selected " : "";
                                            $timetext = jmktime(1,0,0,$i,1,1397);
                                            $month = jdate("F",(int)$timetext,"","","en");
                                            echo '<option '.$selected.' value="'.$i.'">'.$month.'</option>';
                                        }
                                    ?>
                                </select>

                                <select name="s_year" required>
                                    <?php
                                        for($i=$s_l_year;$i<=$e_l_year;$i++){
                                            $selected = ($s_year == $i) ? " selected " : "";
                                            echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form_item">
                            انتخاب پایان بازه زمانی:
                            <div class="date_holder">
                                <select name="e_day" required>
                                    <?php
                                        for($i=1;$i<=31;$i++){
                                            $selected = ($e_day == $i) ? " selected " : "";
                                            echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                        }
                                    ?>
                                </select>

                                <select name="e_month" required>
                                    <?php
                                        for($i=1;$i<=12;$i++){
                                            $selected = ($e_month == $i) ? " selected " : "";
                                            $timetext = jmktime(1,0,0,$i,1,1397);
                                            $month = jdate("F",(int)$timetext,"","","en");
                                            echo '<option '.$selected.' value="'.$i.'">'.$month.'</option>';
                                        }
                                    ?>
                                </select>

                                <select name="e_year" required>
                                    <?php
                                        for($i=$s_l_year;$i<=$e_l_year;$i++){
                                            $selected = ($e_year == $i) ? " selected " : "";
                                            echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ssrv">
                <div class="ssrv_title">شرایط</div>
                <div class="ssrv_dtl">
                    <div class="form fr sales_form_grid">
                        <div class="form_item">
                            <label for="min_buy">حداقل مبلغ سفارش:</label>
                            <input id="min_buy" name="min_buy" type="text" value="<?php echo htmlspecialchars((string)$data["min_buy"],ENT_QUOTES,'UTF-8'); ?>">
                        </div>
                        <div class="form_item">
                            <label for="min_t_buy">حداقل مجموع خرید:</label>
                            <input id="min_t_buy" name="min_t_buy" type="text" value="<?php echo htmlspecialchars((string)$data["min_t_buy"],ENT_QUOTES,'UTF-8'); ?>">
                        </div>
                        <div class="form_item">
                            <label for="min_n_buy">حداقل تعداد خرید:</label>
                            <input id="min_n_buy" name="min_n_buy" type="text" value="<?php echo htmlspecialchars((string)$data["min_n_buy"],ENT_QUOTES,'UTF-8'); ?>">
                        </div>

                        <div class="form_item">
                            <label for="admin_state">روش سفارش:</label>
                            <select id="admin_state" name="admin_state">
                                <option value="1" <?php if((string)$data["admin_state"] === "1") echo " selected "; ?>>سفارش از طریق سایت</option>
                                <option value="0" <?php if((string)$data["admin_state"] === "0") echo " selected "; ?>>سفارش از طریق مدیریت</option>
                            </select>
                        </div>

                        <div class="form_item">
                            <label for="sale_lksr">کد تخفیف:</label>
                            <input id="sale_lksr" name="lksr" type="text" value="<?php echo htmlspecialchars((string)$data["lksr"],ENT_QUOTES,'UTF-8'); ?>" dir="ltr" autocomplete="off" oninput="previewSaleCode(this)">
                            <div id="sale_code_preview" class="user_hint sales_code_preview">کد ذخیره شده با حروف بزرگ ثبت می‌شود.</div>
                        </div>
                    </div>
                </div>
            </div>

            <input type="submit" name="edit" class="btn" value="ثبت">
        </form>

        <script type="text/javascript">
            function saleAmountCurrentValue(){
                var wrap = document.getElementById('amount_input');
                if(!wrap){ return ''; }
                var amountInput = wrap.querySelector('[name="amount"]');
                if(!amountInput){ return ''; }
                return amountInput.value || '';
            }

            function loadSaleAmountField(value){
                var xmlHR = new XMLHttpRequest();
                var url = base_url + 'modules/admin/sales_amount.php';
                var post_data = 'sale_type=' + encodeURIComponent(value) + '&amount=' + encodeURIComponent(saleAmountCurrentValue());
                xmlHR.open('POST', url, true);
                xmlHR.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xmlHR.onreadystatechange = function(){
                    if(xmlHR.readyState !== 4){ return; }
                    var holder = document.getElementById('amount_input');
                    if(!holder){ return; }
                    if(xmlHR.status === 200){
                        holder.innerHTML = xmlHR.responseText;
                    }else{
                        holder.innerHTML = '<div class="user_hint error">خطا در دریافت فیلد مقدار تخفیف.</div>';
                    }
                };
                xmlHR.send(post_data);
            }

            function updateSaleTypeHint(value){
                var hint = document.getElementById('sale_type_hint');
                if(!hint){ return; }
                var map = {
                    percent: 'درصد تخفیف روی کل سبد اعمال می‌شود.',
                    amount: 'مبلغ ثابت از کل سبد کسر می‌شود.',
                    fixed_price: 'مبلغ نهایی سبد را مشخص می‌کند.',
                    send: 'هزینه ارسال رایگان می‌شود.',
                    gift: 'یک محصول هدیه به سفارش اضافه می‌شود.'
                };
                hint.innerHTML = map[value] ? map[value] : '';
            }

            function previewSaleCode(input){
                var code = (input.value || '').trim();
                var normalized = code.toUpperCase();
                var preview = document.getElementById('sale_code_preview');
                if(!preview){ return; }
                if(normalized === ''){
                    preview.innerHTML = 'کد ذخیره شده با حروف بزرگ ثبت می‌شود.';
                    return;
                }
                preview.innerHTML = 'پیش‌نمایش کد: <span dir="ltr">' + normalized + '</span>';
            }

            (function(){
                var typeEl = document.getElementById('sale_type');
                if(typeEl){
                    updateSaleTypeHint(typeEl.value);
                    var amountHolder = document.getElementById('amount_input');
                    if(amountHolder && !amountHolder.querySelector('[name="amount"]')){
                        loadSaleAmountField(typeEl.value);
                    }
                }
                var lksrEl = document.getElementById('sale_lksr');
                if(lksrEl){
                    previewSaleCode(lksrEl);
                }
            })();
        </script>

<?php
        }
    }
?>
