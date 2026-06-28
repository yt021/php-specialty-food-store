<?php
// -----------------------------------------------------------------
// 1. HANDLE USER REQUEST SUBMISSION
// -----------------------------------------------------------------
if (isset($_POST['snappay_user_action']) && isset($_POST['snappay_user_csrf'])) {
    $type = $_POST['snappay_user_action'];
    $csrf = $_POST['snappay_user_csrf'];
    $qty_map = array();
    if (isset($_POST['snappay_qty']) && is_array($_POST['snappay_qty'])) {
        foreach ($_POST['snappay_qty'] as $soid => $qty) {
            $qty_map[(int)$soid] = (int)$qty;
        }
    }

    if (isset($_SESSION[$cf]) && isset($_SESSION[$cf]->id)) {
        $oid = (int)$_SESSION[$cf]->id;
        $handler_path = $bu . "modules/snappay/snappay_request_handler.php";
        if (file_exists($handler_path)) {
            $GLOBALS['snappay_notice_key'] = 'account_notice';
            require_once $handler_path;
            $result = snappay_handle_user_request($oid, $type, $csrf, $qty_map);
        } else {
            $_SESSION["account_notice"] = "E" . "Handler_File_Missing";
        }
    }
}

// -----------------------------------------------------------------
// 2. PAGE CONTENT LOGIC
// -----------------------------------------------------------------
if(isset($indexed)){
    if($indexed == 1){
?>
<main>
    <div class="content">
        <?php
            $oid = (int)$_SESSION[$cf]->id;
            $order_state = (int)getVarFromDB("orders","state","id",$oid);
            $order_status_name = substr(getVarFromDB("admin_orders_state","name","id",$order_state+1),0);
            $pay_id = (string)getVarFromDB("orders","pay_id","id",$oid);
            $snappay_tx = null;
            $is_snappay_order = false;
            $snappay_can_user_action = false;
            $snappay_user_csrf = '';
            $snappay_settle_ok = false;
            $snappay_request_status = 'none';
            $snappay_request = null;
            $snappay_latest_request = null;
            $snappay_latest_update_request = null;
            $snappay_latest_cancel_request = null;
            $snappay_update_request_status = 'none';
            $snappay_cancel_request_status = 'none';
            $snappay_has_pending_request = false;
            $snappay_cancel_approved = false;
            $snappay_cancel_snapshot = null;
            $snappay_update_approved = false;
            $snappay_update_snapshot = null;
            $snappay_total_qty = 0;
            $snappay_can_user_update = false;

            if(file_exists($bu."modules/snappay/snappay_request_handler.php")){
                require_once $bu."modules/snappay/snappay_request_handler.php";
            }elseif(file_exists($bu."modules/snappay/snappay_db.php")){
                require_once $bu."modules/snappay/snappay_db.php";
            }
            if(function_exists('snappay_tx_get_latest_for_order')){
                $snappay_tx = snappay_tx_get_latest_for_order($oid);
                if($snappay_tx && (string)($snappay_tx['payment_token'] ?? '') !== ''){
                    $is_snappay_order = true;
                    $sn_status = strtoupper(trim((string)($snappay_tx['snappay_status'] ?? '')));
                    $final_status = strtoupper(trim((string)($snappay_tx['final_status'] ?? '')));
                    $snappay_settle_ok = ($sn_status === 'SETTLE' || $final_status === 'SETTLE_OK');

                    if(function_exists('snappay_get_request_for_order')){
                        $snappay_latest_request = snappay_get_request_for_order($oid);
                        $snappay_latest_update_request = snappay_get_request_for_order($oid, 'update', null);
                        $snappay_latest_cancel_request = snappay_get_request_for_order($oid, 'cancel', null);

                        if($snappay_latest_update_request){
                            $snappay_update_request_status = strtolower(trim((string)($snappay_latest_update_request['status'] ?? 'none')));
                            $snappay_update_approved = ($snappay_update_request_status === 'approved');
                            if($snappay_update_approved && isset($snappay_latest_update_request['request_snapshot_data']) && is_array($snappay_latest_update_request['request_snapshot_data'])){
                                $snappay_update_snapshot = $snappay_latest_update_request['request_snapshot_data'];
                            }
                        }
                        if($snappay_latest_cancel_request){
                            $snappay_cancel_request_status = strtolower(trim((string)($snappay_latest_cancel_request['status'] ?? 'none')));
                            $snappay_cancel_approved = ($snappay_cancel_request_status === 'approved');
                            if($snappay_cancel_approved && isset($snappay_latest_cancel_request['request_snapshot_data']) && is_array($snappay_latest_cancel_request['request_snapshot_data'])){
                                $snappay_cancel_snapshot = $snappay_latest_cancel_request['request_snapshot_data'];
                            }
                        }
                        $snappay_has_pending_request = ($snappay_update_request_status === 'pending' || $snappay_cancel_request_status === 'pending');

                        $snappay_request = $snappay_latest_request;
                        if($snappay_request){
                            $snappay_request_status = strtolower(trim((string)($snappay_request['status'] ?? 'none')));
                        }
                    }

                    if($snappay_settle_ok && $order_state >= 0 && $order_state <= 2){
                        $snappay_can_user_action = true;
                        if(function_exists('snappay_get_order_total_qty')){
                            $snappay_total_qty = (int)snappay_get_order_total_qty($oid);
                        }
                        $snappay_can_user_update = ($snappay_total_qty > 1);

                        if(!isset($_SESSION['snappay_user_csrf']) || !is_string($_SESSION['snappay_user_csrf']) || strlen($_SESSION['snappay_user_csrf']) < 32){
                            if(function_exists('random_bytes')){
                                $_SESSION['snappay_user_csrf'] = bin2hex(random_bytes(16));
                            }else{
                                $_SESSION['snappay_user_csrf'] = sha1(uniqid('snappay_user_csrf_', true));
                            }
                        }
                        $snappay_user_csrf = (string)$_SESSION['snappay_user_csrf'];
                    }
                }
            }

            $summary_options = array(
                "checkout_scrollable" => true,
                "checkout_mobile_cards" => true,
                "discount_negative_display" => true,
                "shipping_row_mode" => "method_and_cost"
            );
            if($snappay_can_user_action && $snappay_can_user_update){
                $summary_options["qty_editable"] = true;
                $summary_options["qty_input_name"] = "snappay_qty";
                $summary_options["qty_control_mode"] = "decrease_only";
                $summary_options["qty_inputs_disabled"] = true;
            }
            if($snappay_cancel_approved && is_array($snappay_cancel_snapshot)){
                if(isset($snappay_cancel_snapshot["requested_qty_map"]) && is_array($snappay_cancel_snapshot["requested_qty_map"])){
                    $summary_options["qty_override_map"] = $snappay_cancel_snapshot["requested_qty_map"];
                }
                if(isset($snappay_cancel_snapshot["financial"]) && is_array($snappay_cancel_snapshot["financial"])){
                    $summary_options["financial_override"] = $snappay_cancel_snapshot["financial"];
                }
            } elseif($snappay_update_approved && is_array($snappay_update_snapshot)){
                if(isset($snappay_update_snapshot["requested_qty_map"]) && is_array($snappay_update_snapshot["requested_qty_map"])){
                    $summary_options["qty_override_map"] = $snappay_update_snapshot["requested_qty_map"];
                }
                if(isset($snappay_update_snapshot["financial"]) && is_array($snappay_update_snapshot["financial"])){
                    $summary_options["financial_override"] = $snappay_update_snapshot["financial"];
                }
            }
        ?>
        <style type="text/css">
            .account_order_page{
                max-width:1240px;
                margin:0 auto;
            }
            .account_order_header_card{
                border:1px solid rgba(0,0,0,0.12);
                border-radius:18px;
                background:linear-gradient(135deg,#fff 0%,#f7f2ef 100%);
                padding:10px 14px;
                margin-bottom:10px;
            }
            .account_order_header_top{
                display:flex;
                align-items:flex-start;
                justify-content:space-between;
                gap:12px;
                margin-bottom:8px;
            }
            .account_order_kicker{
                font-size:22px;
                font-weight:800;
                color:#4a2d22;
                margin-bottom:2px;
                line-height:1.15;
            }
            .account_order_title{
                margin:0;
                font-size:14px;
                line-height:1.35;
                color:#8a6d5d;
                font-weight:600;
                text-align:right;
            }
            .account_order_status_badge{
                display:inline-flex;
                align-items:center;
                justify-content:center;
                min-width:94px;
                padding:5px 10px;
                border-radius:999px;
                background:#8d0e0e;
                color:#fff;
                font-weight:700;
                white-space:nowrap;
                font-size:11px;
            }
            .account_order_meta_grid{
                display:grid;
                grid-template-columns:repeat(3,minmax(0,1fr));
                gap:8px;
            }
            .account_order_meta_item{
                border:1px solid rgba(128,0,0,0.18);
                border-radius:12px;
                background:rgba(255,255,255,0.88);
                padding:7px 9px;
                min-width:0;
            }
            .account_order_meta_label{
                display:block;
                font-size:11px;
                font-weight:700;
                color:#7a5847;
                margin-bottom:3px;
            }
            .account_order_meta_value{
                display:block;
                font-weight:700;
                font-size:13px;
                line-height:1.45;
                color:#231814;
                overflow-wrap:anywhere;
            }
            .account_order_edit_layout{
                display:grid;
                grid-template-columns:minmax(0,1fr);
                gap:18px;
                align-items:start;
            }
            .account_order_summary_col,
            .account_order_notice_col{
                min-width:0;
            }
            .account_order_summary_col table.cart{
                width:100%;
                margin:0;
            }
            .account_order_summary_col .checkout_summary_shell{
                border:1px solid rgba(0,0,0,0.12);
                border-radius:12px;
                padding:8px;
                background:#fff;
            }
            .account_order_summary_col h3.tac{
                margin:0 0 8px 0;
                font-size:16px;
                line-height:1.3;
                font-weight:700;
            }
            .account_order_summary_col table.cart thead th{
                padding-top:8px;
                padding-bottom:8px;
                font-size:12px;
                line-height:1.3;
                font-weight:700;
            }
            .account_order_summary_col table.cart tbody td{
                padding-top:10px;
                padding-bottom:10px;
            }
            .account_order_summary_col .checkout_summary_scroll_wrap{
                max-height:min(56vh,560px);
                overflow:auto;
            }
            .account_order_summary_col .checkout_summary_final_row td{
                position:sticky;
                bottom:0;
                background:#f4f0ee;
                z-index:1;
            }
            .account_order_notice_col{
                display:flex;
                flex-direction:column;
                gap:12px;
            }
            .account_order_panel{
                border:1px solid rgba(0,0,0,0.12);
                border-radius:12px;
                background:#fff;
                padding:14px 16px;
                box-sizing:border-box;
            }
            .account_order_panel h4{
                margin:0 0 10px 0;
                text-align:right;
            }
            .account_order_panel p{
                margin:0 0 8px 0;
                line-height:1.8;
            }
            .account_order_panel p:last-child{
                margin-bottom:0;
            }
            .account_order_address_text{
                color:#333;
            }
            .snappay-inline-actions{
                display:flex;
                flex-direction:column;
                gap:10px;
            }
            .snappay-inline-actions .btn,
            .snappay-inline-actions button.btn{
                width:100%;
                margin:0 !important;
                box-sizing:border-box;
                text-align:center;
            }
            .order_qty_editor{
                display:inline-flex;
                align-items:center;
                justify-content:center;
                gap:8px;
                direction:ltr;
            }
            .order_qty_editor.is-disabled{
                opacity:0.65;
            }
            .order_qty_btn{
                display:inline-flex;
                align-items:center;
                justify-content:center;
                min-width:28px;
                height:28px;
                cursor:pointer;
            }
            .order_qty_btn.is-disabled{
                opacity:0.45;
                cursor:not-allowed;
            }
            .order_qty_value{
                min-width:28px;
                text-align:center;
                font-weight:700;
            }
            .snappay-edit-notice.hide,
            .snappay-submit-btn.hide,
            .snappay-cancel-edit.hide,
            .snappay-start-edit.hide{
                display:none;
            }
            @media (min-width:1100px){
                .account_order_edit_layout{
                    grid-template-columns:minmax(0,1fr) 360px;
                    align-items:stretch;
                }
                .account_order_summary_col .checkout_summary_shell{
                    height:min(56vh,560px);
                    display:flex;
                    flex-direction:column;
                }
                .account_order_notice_col{
                    position:sticky;
                    top:16px;
                    align-self:stretch;
                    height:min(56vh,560px);
                    min-height:420px;
                    overflow:auto;
                }
                .account_order_summary_col .checkout_summary_scroll_wrap{
                    flex:1 1 auto;
                    max-height:none;
                }
            }
            @media (max-width:960px){
                .account_order_header_top{
                    flex-direction:column;
                    align-items:flex-start;
                }
                .account_order_meta_grid{
                    grid-template-columns:repeat(2,minmax(0,1fr));
                }
            }
            @media (max-width:900px){
                .account_order_summary_col .checkout_summary_shell{
                    display:none;
                }
                .account_order_summary_col .checkout_mobile_summary{
                    display:flex;
                    flex-direction:column;
                    gap:10px;
                    margin-top:6px;
                    min-width:0;
                }
                .account_order_summary_col .checkout_mobile_summary .cfp_item{
                    width:100%;
                    margin:0;
                    max-width:none;
                    min-width:0;
                    height:auto;
                    box-sizing:border-box;
                    padding:14px 16px;
                    display:flex;
                    flex-direction:column;
                    gap:8px;
                }
                .account_order_summary_col .checkout_mobile_summary .cfp_item h3{
                    height:auto;
                    min-height:0;
                    margin:0 0 2px 0;
                    line-height:1.5;
                }
                .account_order_summary_col .checkout_mobile_summary .cfpi_weight,
                .account_order_summary_col .checkout_mobile_summary .cfpi_price,
                .account_order_summary_col .checkout_mobile_summary .cfpi_number,
                .account_order_summary_col .checkout_mobile_summary .cfpi_total_price{
                    float:none;
                    width:100%;
                    min-width:0;
                    height:auto;
                    margin:0;
                    display:block;
                    line-height:1.75;
                    text-align:right;
                    overflow-wrap:anywhere;
                    word-break:break-word;
                }
                .account_order_summary_col .checkout_mobile_summary .cfpi_number{
                    display:flex;
                    align-items:center;
                    justify-content:space-between;
                    gap:10px;
                    flex-wrap:wrap;
                }
                .account_order_summary_col .checkout_mobile_summary .cfpi_number .order_qty_editor{
                    flex:0 0 auto;
                }
                .account_order_summary_col .checkout_mobile_summary .cb{
                    display:none;
                }
                .account_order_summary_col .checkout_mobile_summary .cfpi_total_price{
                    padding-top:8px;
                    border-top:1px solid rgba(128,0,0,0.15);
                    font-size:16px;
                    font-weight:700;
                }
                .account_order_summary_col .checkout_mobile_summary .checkout_mobile_totals_card{
                    gap:10px;
                }
                .account_order_summary_col .checkout_mobile_summary .checkout_mobile_totals_card .cfpi_price,
                .account_order_summary_col .checkout_mobile_summary .checkout_mobile_totals_card .cfpi_number,
                .account_order_summary_col .checkout_mobile_summary .checkout_mobile_totals_card .cfpi_total_price,
                .account_order_summary_col .checkout_mobile_summary .checkout_mobile_totals_card .cfpi_weight{
                    text-align:right;
                }
            }
            @media (max-width:640px){
                .account_order_header_card{
                    padding:10px 12px;
                }
                .account_order_kicker{
                    font-size:18px;
                }
                .account_order_title{
                    font-size:13px;
                }
                .account_order_meta_grid{
                    grid-template-columns:minmax(0,1fr);
                }
            }
        </style>
<?php
        $post_ref_id = getVarFromDB("orders","post_ref_id","id",$oid);
        $send_date = correctDate(getVarFromDB("orders","send_date","id",$oid));
        $rec_date = correctDate(getVarFromDB("orders","recieve_date","id",$oid));
        $rec_shift = getVarFromDB("orders","recieve_shift","id",$oid);
        $shipping_method_text = "";
        $delivery_text = "";
        if($rec_shift && var_exist($rec_shift,"sd_shifts","id")){
            $tp_id = (int)getVarFromDB("sd_shifts","transporter_id","id",$rec_shift);
            $shift_text = function_exists('cor_sendShift') ? trim((string)cor_sendShift($rec_shift)) : "";
            if($tp_id === 1){
                $shipping_method_text = "پست";
                if($shift_text !== "" && $shift_text !== $shipping_method_text){
                    $shipping_method_text .= " - ".$shift_text;
                }
            }elseif($tp_id === 2){
                $shipping_method_text = "پیک";
                if($shift_text !== "" && strpos($shift_text, "پیک") === false){
                    $shipping_method_text .= " - ".$shift_text;
                }
            }else{
                $transporter_name = trim((string)getVarFromDB("transporters","name","id",$tp_id));
                if($transporter_name !== ""){
                    $shipping_method_text = $transporter_name;
                    if($shift_text !== "" && $shift_text !== $transporter_name){
                        $shipping_method_text .= " - ".$shift_text;
                    }
                }else{
                    $shipping_method_text = $shift_text;
                }
            }
        }elseif($post_ref_id){
            if(strlen((string)$post_ref_id) === 24){
                $shipping_method_text = "پست";
            }elseif(var_exist($post_ref_id,"transporters","id")){
                $shipping_method_text = trim((string)getVarFromDB("transporters","name","id",$post_ref_id));
            }
        }
        if($order_state == 4){
            if($post_ref_id){
                ob_start();
                echo correct_post_state_client($post_ref_id,$send_date,$rec_date,$rec_shift);
                $delivery_text = trim((string)ob_get_clean());
            }
        }else{
            if($rec_shift){
                ob_start();
                echo correct_rec_time_client($rec_date,$rec_shift);
                $delivery_text = trim((string)ob_get_clean());
            }
        }
        $address_display = "آدرسی برای این سفارش ثبت نشده است.";
        if($aid = getVarFromDB("orders","aid","id",$oid)){
            $address = db_get_address($aid);
            $address = $address->data();
            $address_display = "استان: ".$address["county"]."، شهر: ".$address["city"]."<br>آدرس: ".$address["address"]."، کد پستی: ".$address["post_code"];
        }
?>
<?php
    $_SESSION["a_oid"] = $oid;
    if($shipping_method_text !== ""){
        $summary_options["shipping_method_text"] = $shipping_method_text;
    }
?>
        <div class="account_order_page">
        <div class="account_order_header_card">
            <div class="account_order_header_top">
                <div>
                    <div class="account_order_kicker" onclick="sub_show('clear',0);">حساب من</div>
                    <h1 class="account_order_title" onclick="sub_show('clear',1);">جزئیات سفارش شناسه <?php echo (int)$_SESSION[$cf]->id; ?></h1>
                </div>
                <div class="account_order_status_badge"><?php echo $order_status_name; ?></div>
            </div>
            <div class="account_order_meta_grid">
                <div class="account_order_meta_item">
                    <span class="account_order_meta_label">شناسه پرداخت</span>
                    <span class="account_order_meta_value"><?php echo htmlspecialchars($pay_id, ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="account_order_meta_item">
                    <span class="account_order_meta_label">وضعیت سفارش</span>
                    <span class="account_order_meta_value"><?php echo $order_status_name; ?></span>
                </div>
                <div class="account_order_meta_item">
                    <span class="account_order_meta_label">زمان تحویل / پیگیری</span>
                    <span class="account_order_meta_value"><?php echo $delivery_text !== "" ? $delivery_text : "-"; ?></span>
                </div>
            </div>
        </div>
        <?php
            if(isset($_SESSION["account_notice"])){
                $sn_msg = (string)$_SESSION["account_notice"];
                unset($_SESSION["account_notice"]);

                if($sn_msg !== ''){
                    $sn_err = ($sn_msg[0] === 'E');
                    $msg_display = substr($sn_msg,1);
                    echo '<div class="user_hint '.($sn_err ? 'error' : '').'">'.$msg_display.'</div>';
                }
            }
        ?>
        <?php if($snappay_cancel_approved): ?>
        <div class="user_hint success">
            این سفارش لغو شده است. جزئیات نمایش‌داده‌شده مربوط به آخرین وضعیت سفارش قبل از تایید درخواست لغو است.
            <?php if(is_array($snappay_request) && !empty($snappay_request['admin_note'])): ?>
                <br><strong>توضیح مدیر:</strong> <?php echo htmlspecialchars((string)$snappay_request['admin_note'], ENT_QUOTES, 'UTF-8'); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <form action="<?php echo $URL; ?>" method="post" class="snappay-user-form" data-snappay-form>
            <input type="hidden" name="snappay_user_csrf" value="<?php echo htmlspecialchars($snappay_user_csrf, ENT_QUOTES, 'UTF-8'); ?>">
            <div class="account_order_edit_layout">
                <div class="account_order_summary_col">
                    <?php cart_render_paid_order_summary($oid,$summary_options); ?>
                </div>
                <div class="account_order_notice_col">
                    <?php if(!$snappay_cancel_approved): ?>
                    <div class="account_order_panel">
                        <h4>آدرس تحویل</h4>
                        <p class="account_order_address_text"><?php echo $address_display; ?></p>
                    </div>
                    <div class="account_order_panel">
                        <h4>دسترسی سریع</h4>
                        <div class="snappay-inline-actions">
                            <a href="invoice.php" class="btn">مشاهده فاکتور</a>
                            <a onclick="sub_show('clear',1);" class="btn">بازگشت</a>
                        </div>
                    </div>
                    <?php if($is_snappay_order): ?>
                    <?php if($snappay_can_user_action): ?>
                    <?php if(!$snappay_can_user_update): ?>
                    <div class="user_hint">
                        وقتی مجموع تعداد این سفارش ۱ است، بروزرسانی مجاز نیست و فقط می‌توانید درخواست لغو ثبت کنید.
                    </div>
                    <?php endif; ?>
                    <?php if($snappay_has_pending_request): ?>
                    <div class="user_hint">
                        <strong>درخواست در حال بررسی:</strong>
                        یکی از درخواست‌های قبلی شما هنوز در انتظار بررسی است. تا زمان پاسخ مدیر، امکان ثبت درخواست جدید وجود ندارد.
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <div class="account_order_panel">
                        <h4>اقدامات سفارش</h4>
                        <div class="snappay-inline-actions">
                            <?php if($snappay_can_user_action && $snappay_can_user_update && $snappay_has_pending_request): ?>
                            <button type="button" class="btn" disabled style="opacity:0.5;">درخواست بروزرسانی ثبت شده</button>
                            <?php elseif($snappay_can_user_action && $snappay_can_user_update): ?>
                            <button type="button" class="btn snappay-start-edit" data-edit-toggle>درخواست بروزرسانی سفارش</button>
                            <button type="submit" name="snappay_user_action" value="update" class="btn snappay-submit-btn hide" data-update-submit>ثبت درخواست بروزرسانی</button>
                            <button type="button" class="btn snappay-cancel-edit hide" data-edit-cancel>انصراف از ویرایش</button>
                            <?php endif; ?>
                            <?php
                                $cancel_disabled = $snappay_has_pending_request ? 'disabled style="opacity:0.5"' : '';
                                $cancel_text = $snappay_has_pending_request ? 'درخواست لغو ثبت شده' : 'درخواست لغو';
                            ?>
                            <button type="submit" name="snappay_user_action" value="cancel" class="btn" <?php echo $cancel_disabled; ?>><?php echo $cancel_text; ?></button>
                        </div>
                    </div>
                    <div class="user_hint snappay-edit-notice hide" data-edit-notice>
                        نکته: در حالت بروزرسانی فقط می‌توانید تعداد آیتم‌ها را کاهش دهید و باید حداقل یک آیتم در سفارش باقی بماند. برای صفر کردن کل سفارش از دکمه لغو استفاده کنید.
                    </div>
                    <div class="account_order_panel">
                        <h4>راهنما</h4>
                        <p>بروزرسانی فقط برای سفارش‌های تسویه‌شده اسنپ‌پی و تا مرحله «در حال آماده‌سازی» مجاز است.</p>
                        <p>مبلغ جدید سفارش باید کمتر از مبلغ اولیه باشد.</p>
                        <p>لغو فقط پس از تسویه اسنپ‌پی مجاز است و برای مرجوعی کامل سبد خرید استفاده می‌شود.</p>
                        <p style="color:#d9534f; font-weight:bold;">تمامی درخواست‌های تغییر یا لغو نیازمند تایید مدیر سایت هستند.</p>
                    </div>
                    <?php if($snappay_request_status === 'approved' && is_array($snappay_request)): ?>
                    <div class="user_hint success">
                        درخواست <?php echo ($snappay_request['request_type'] === 'cancel') ? 'لغو' : 'بروزرسانی'; ?> این سفارش تایید و اجرا شده است.
                        <?php if(!empty($snappay_request['admin_note'])): ?>
                            <br><strong>توضیح مدیر:</strong> <?php echo htmlspecialchars((string)$snappay_request['admin_note'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php if($snappay_request_status === 'denied'): ?>
                    <div class="user_hint" style="background:#fff3cd; color:#856404; border:1px solid #ffeeba;">
                        درخواست قبلی شما رد شده است و اکنون می‌توانید پس از اعمال تغییرات جدید دوباره درخواست ثبت کنید.
                        <?php if(is_array($snappay_request) && !empty($snappay_request['admin_note'])): ?>
                            <br><strong>توضیح مدیر:</strong> <?php echo htmlspecialchars((string)$snappay_request['admin_note'], ENT_QUOTES, 'UTF-8'); ?>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php if($snappay_cancel_approved): ?>
                    <div class="account_order_panel">
                        <h4>بازگشت</h4>
                        <div class="snappay-inline-actions">
                            <a onclick="sub_show('clear',1);" class="btn">بازگشت</a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
        </div>
    </div>
    <script>
function initSnappayAccountOrderForm() {
    const form = document.querySelector('[data-snappay-form]');
    if (!form) return;
    if (form.getAttribute('data-snappay-bound') === '1') return;
    form.setAttribute('data-snappay-bound', '1');
    const cancelApproved = <?php echo $snappay_cancel_approved ? 'true' : 'false'; ?>;

    const editToggleBtn = form.querySelector('[data-edit-toggle]');
    const editCancelBtn = form.querySelector('[data-edit-cancel]');
    const submitBtn = form.querySelector('[data-update-submit]');
    const editNotice = form.querySelector('[data-edit-notice]');
    const qtyInputs = Array.prototype.slice.call(form.querySelectorAll('[data-qty-input]'));
    const qtyEditors = Array.prototype.slice.call(form.querySelectorAll('[data-qty-editor]'));
    const qtyInputsByKey = {};

    if (cancelApproved) {
        const noticeCol = form.querySelector('.account_order_notice_col');
        if (noticeCol) {
            const panels = Array.prototype.slice.call(noticeCol.querySelectorAll('.account_order_panel'));
            const backPanel = panels.find(function(panel) {
                return !!panel.querySelector('a[onclick*="sub_show(\'clear\',1)"]');
            });

            panels.forEach(function(panel) {
                if (panel !== backPanel) {
                    panel.style.display = 'none';
                }
            });

            const extraHints = Array.prototype.slice.call(noticeCol.querySelectorAll('.user_hint'));
            extraHints.forEach(function(hint) {
                hint.style.display = 'none';
            });

            if (backPanel) {
                const heading = backPanel.querySelector('h4');
                if (heading) heading.textContent = 'بازگشت';

                const invoiceLink = backPanel.querySelector('a[href="invoice.php"]');
                if (invoiceLink) {
                    invoiceLink.style.display = 'none';
                }
            }
        }
    }

    (function normalizeSummaryTotals() {
        const finalRow = form.querySelector('.checkout_summary_final_row');
        if (finalRow) {
            const cells = Array.prototype.slice.call(finalRow.querySelectorAll('td'));
            if (cells.length >= 4) {
                const weightLabel = cells[0] ? cells[0].textContent.trim() : 'وزن کل';
                const weightValue = cells[1] ? cells[1].textContent.trim() : '-';
                const amountValue = cells[cells.length - 1] ? cells[cells.length - 1].textContent.trim() : '0';
                const hasDiscountCode = !!form.querySelector('span[dir="ltr"]');
                const totalLabel = hasDiscountCode ? 'جمع قبل از کد تخفیف' : 'جمع کل';
                finalRow.innerHTML = ''
                    + '<td>' + weightLabel + '</td>'
                    + '<td colspan="2">' + weightValue + '</td>'
                    + '<td colspan="3">' + totalLabel + '</td>'
                    + '<td>' + amountValue + '</td>';
            }
        }

        const mobileTotalsCard = form.querySelector('.checkout_mobile_totals_card');
        if (mobileTotalsCard) {
            const redundantLine = Array.prototype.slice.call(mobileTotalsCard.querySelectorAll('.cfpi_number')).find(function(node) {
                return node.textContent.indexOf('جمع بدون تخفیف') > -1;
            });
            if (redundantLine) {
                redundantLine.remove();
            }
        }
    })();

    qtyInputs.forEach(function(input) {
        const key = input.getAttribute('data-qty-key') || '';
        if (!key) return;
        if (!qtyInputsByKey[key]) {
            qtyInputsByKey[key] = [];
        }
        qtyInputsByKey[key].push(input);
    });

    function hasQtyChanges() {
        for (let i = 0; i < qtyInputs.length; i++) {
            const input = qtyInputs[i];
            const initial = parseInt(input.getAttribute('data-initial-qty') || input.defaultValue || '0', 10);
            const current = parseInt(input.value || '0', 10);
            if (current !== initial) {
                return true;
            }
        }
        return false;
    }

    function getTotalQty() {
        let total = 0;
        for (let i = 0; i < qtyInputs.length; i++) {
            total += Math.max(0, parseInt(qtyInputs[i].value || '0', 10) || 0);
        }
        return total;
    }

    function syncQtyEditor(editor) {
        if (!editor) return;
        const input = editor.querySelector('[data-qty-input]');
        const valueNode = editor.querySelector('[data-qty-value]');
        const incBtn = editor.querySelector('[data-qty-increase]');
        const decBtn = editor.querySelector('[data-qty-decrease]');
        if (!input || !valueNode) return;

        const current = Math.max(0, parseInt(input.value || '0', 10) || 0);
        const initial = Math.max(0, parseInt(input.getAttribute('data-initial-qty') || input.defaultValue || current, 10) || 0);
        valueNode.textContent = current;
        if (incBtn) {
            incBtn.classList.toggle('is-disabled', input.disabled || current >= initial);
        }
        if (decBtn) {
            const totalQty = getTotalQty();
            decBtn.classList.toggle('is-disabled', input.disabled || current <= 0 || totalQty <= 1);
        }
    }

    function syncQtyGroup(key, nextValue) {
        const group = qtyInputsByKey[key] || [];
        group.forEach(function(input) {
            input.value = nextValue;
        });
        qtyEditors.forEach(function(editor) {
            const editorInput = editor.querySelector('[data-qty-input]');
            if (!editorInput) return;
            if ((editorInput.getAttribute('data-qty-key') || '') !== key) return;
            syncQtyEditor(editor);
        });
    }

    function setEditMode(isEditing) {
        form.setAttribute('data-editing', isEditing ? '1' : '0');
        if (editToggleBtn) editToggleBtn.classList.toggle('hide', isEditing);
        if (submitBtn) submitBtn.classList.toggle('hide', !isEditing);
        if (editCancelBtn) editCancelBtn.classList.toggle('hide', !isEditing);
        if (editNotice) editNotice.classList.toggle('hide', !isEditing);

        qtyInputs.forEach(function(input) {
            if (!isEditing) {
                input.value = input.getAttribute('data-initial-qty') || input.defaultValue || input.value;
            }
            input.disabled = !isEditing;
        });

        qtyEditors.forEach(function(editor) {
            editor.classList.toggle('is-disabled', !isEditing);
            syncQtyEditor(editor);
        });
    }

    qtyEditors.forEach(function(editor) {
        syncQtyEditor(editor);
        const decBtn = editor.querySelector('[data-qty-decrease]');
        const incBtn = editor.querySelector('[data-qty-increase]');
        if (incBtn) {
            incBtn.addEventListener('click', function() {
                const input = editor.querySelector('[data-qty-input]');
                if (incBtn.classList.contains('is-disabled')) return;
                if (!input || input.disabled) return;
                const current = Math.max(0, parseInt(input.value || '0', 10) || 0);
                const initial = Math.max(0, parseInt(input.getAttribute('data-initial-qty') || input.defaultValue || current, 10) || 0);
                const nextValue = Math.min(initial, current + 1);
                const key = input.getAttribute('data-qty-key') || '';
                if (key) {
                    syncQtyGroup(key, nextValue);
                } else {
                    input.value = nextValue;
                    syncQtyEditor(editor);
                }
            });
        }
        if (decBtn) {
            decBtn.addEventListener('click', function() {
                const input = editor.querySelector('[data-qty-input]');
                if (decBtn.classList.contains('is-disabled')) return;
                if (!input || input.disabled) return;
                const current = Math.max(0, parseInt(input.value || '0', 10) || 0);
                const nextValue = Math.max(0, current - 1);
                const key = input.getAttribute('data-qty-key') || '';
                if (key) {
                    syncQtyGroup(key, nextValue);
                } else {
                    input.value = nextValue;
                    syncQtyEditor(editor);
                }
            });
        }
    });

    if (editToggleBtn) {
        editToggleBtn.addEventListener('click', function() {
            setEditMode(true);
        });
    }

    if (editCancelBtn) {
        editCancelBtn.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            setEditMode(false);
        });
    }

    form.addEventListener("submit", function(event) {
        const action = event.submitter ? event.submitter.value : null;

        if (action === "update") {
            if (form.getAttribute('data-editing') !== '1') {
                event.preventDefault();
                setEditMode(true);
                return;
            }
            if (!hasQtyChanges()) {
                event.preventDefault();
                alert("تغییری در تعداد آیتم‌ها ایجاد نشده است.");
                return;
            }
            if (getTotalQty() < 1) {
                event.preventDefault();
                alert("برای صفر کردن کل سفارش باید از دکمه لغو سفارش استفاده کنید.");
                return;
            }
            const ok = confirm("آیا از ارسال درخواست بروزرسانی سفارش مطمئن هستید؟");
            if (!ok) {
                event.preventDefault();
            }
        }

        if (action === "cancel") {
            const ok = confirm("آیا از ارسال درخواست لغو کامل سفارش اطمینان دارید؟\nتوجه: این عملیات برای مرجوعی کامل است.");
            if (!ok) {
                event.preventDefault();
            }
        }
    });

    setEditMode(false);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSnappayAccountOrderForm);
} else {
    initSnappayAccountOrderForm();
}
</script>
</main>
<?php
        }
    }
?>
