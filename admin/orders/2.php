<?php

    if(isset($indexed)){

        if($indexed == 1){?>

<?php

//// Data Gathering

// Overall Data

    $oid = $_SESSION[$cf]->id;
    $unified_id = getVarFromDB($tb, "unified_id", "id", $oid);
    if (!$unified_id) $unified_id = $oid;

    include_once $bu."modules/cart/cart_funcs.php";

    $uid = getVarFromDB($tb,"uid","id",$oid);

    $aid = getVarFromDB($tb,"aid","id",$oid);

    $user = db_get_user($uid);

    $address = db_get_address($aid);

    $addresses = db_get_all_address($uid);

    $order_detail = getVarFromDB("orders","order_detail","id",$oid);

//Financial Data

    if($_SESSION["a_logged"]->get_level() >= 2){

        $fields[1] = ["pay_price","sale_total","cart_price","cart_pure"];

        $fields[2] = ["cart_sale","send_sale"];

        $labels = array(

            "full_price"=>"مجموع فاکتور",

            "pay_price"=>"مبلغ قابل پرداخت",

            "sale_total"=>"مجموع تخفیف",

            "cart_price"=>"مجموع سبد خرید(ناخالص)",

            "cart_pure"=>"مجموع سبد خرید(خالص)",

            "cart_sale"=>"تخفیف سبد خرید",

            "send_cost"=>"هزینه ارسال",

            "send_sale"=>"تخفیف ارسال"

        );

        foreach($fields[1] as $f){

            $invoice_value[$f] = getVarFromDB($tb,$f,"id",$_SESSION[$cf]->id);

        }

        $invoice_value["send_cost"] = 

        ($invoice_value["pay_price"] - $invoice_value["cart_pure"]) + 

        ($invoice_value["sale_total"] - ($invoice_value["cart_price"] - $invoice_value["cart_pure"]));

        

        $invoice_value["full_price"] = 

        $invoice_value["cart_price"] + 

        $invoice_value["send_cost"];

        

        $invoice_value["cart_sale"] = $invoice_value["cart_price"] - $invoice_value["cart_pure"];

        $invoice_value["send_sale"] = $invoice_value["sale_total"] - $invoice_value["cart_sale"];

        $fields[1] = array_merge(["full_price"],$fields[1],["send_cost"]);

    }

?> 

<style type="text/css">

    div.ssrv{

        box-sizing:border-box;

        min-height:0;

        padding-right:72px;

    }

    div.ssrv div.ssrv_title{

        position:absolute;

        top:0;

        right:0;

        bottom:0;

        width:72px;

        height:auto;

        min-height:100%;

        padding:12px 8px;

        box-sizing:border-box;

        transform:none;

        transform-origin:center;

        writing-mode:vertical-rl;

        text-orientation:mixed;

        display:flex;

        align-items:center;

        justify-content:center;

        line-height:1.35;

        text-align:center;

    }

    div.ssrv div.ssrv_dtl{

        width:100%;

        float:none;

        box-sizing:border-box;

    }

    div.form.flex{

        display:flex;

        justify-content:space-between;

        flex-wrap:wrap;

    }

    div.column{

        width:280px;

/*        border-right:1px solid red;*/

        padding:10px;

        float:right;   

    }

    div.column div.form_item{

        width:260px;

    }

    div.column:nth-child(1){

        border-right:0px;

    }

    div.column h4{

        text-align: center;

        border-bottom:1px solid red;

    }

    div.column .form_item input{

        width:175px;

    }

    

    div.date_box{

        display:inline-block;

        margin-top:5px;

    }

    div.snappay-admin-section .ssrv_dtl{
        overflow:hidden;
        box-sizing:border-box;
    }
    div.ssrv.snappay-admin-section{
        padding-right:72px;
        padding-left:18px;
    }
    div.ssrv.snappay-admin-section .ssrv_dtl{
        padding-left:4px;
        padding-right:4px;
        box-sizing:border-box;
    }
    div.snappay-card{
        border:1px solid rgba(0,0,0,0.10);
        border-radius:18px;
        padding:16px;
        background:linear-gradient(180deg,#ffffff 0%,#fbfaf8 100%);
        box-shadow:0 10px 26px rgba(71,43,22,0.05);
        display:flex;
        flex-direction:column;
        gap:12px;
        width:100%;
        max-width:100%;
        min-width:0;
        overflow:hidden;
        box-sizing:border-box;
    }
    div.snappay-card form{
        width:100%;
        max-width:100%;
        min-width:0;
        overflow:visible;
        box-sizing:border-box;
    }
    div.snappay-card .account_order_summary_col,
    div.snappay-card .account_order_notice_col{
        min-width:0;
    }
    div.snappay-card .snappay-user-summary-clone{
        min-width:0;
        width:100%;
    }
    div.snappay-card .snappay-user-summary-clone .checkout_summary_shell{
        width:100%;
        max-width:100%;
        min-width:0;
        margin-top:12px;
        border:1px solid rgba(0,0,0,0.12);
        border-radius:18px;
        padding:18px 18px 12px 18px;
        background:#fff;
        box-sizing:border-box;
        overflow:hidden;
        box-shadow:none;
    }
    div.snappay-card .snappay-user-summary-clone h3.tac{
        margin:0 0 12px 0;
        padding-top:14px;
        border-top:2px solid #b80f28;
        font-size:16px;
        line-height:1.5;
        font-weight:800;
        color:#111;
    }
    div.snappay-card .snappay-user-summary-clone .checkout_summary_scroll_wrap{
        width:100%;
        max-width:100%;
        min-width:0;
        max-height:min(56vh,560px);
        overflow:auto;
    }
    div.snappay-card .snappay-user-summary-clone table.cart{
        width:100%;
        min-width:0;
        margin:0;
        border-collapse:separate;
        border-spacing:0;
        table-layout:fixed;
    }
    div.snappay-card .snappay-user-summary-clone table.cart thead th{
        padding:10px 8px 12px 8px;
        font-size:12px;
        line-height:1.5;
        font-weight:800;
        color:#111;
        background:#fff !important;
        border-bottom:1px solid #c83434;
    }
    div.snappay-card .snappay-user-summary-clone table.cart tbody td{
        padding:14px 8px;
        background:#fff !important;
        color:#111;
        border-bottom:1px solid rgba(0,0,0,0.08);
    }
    div.snappay-card .snappay-user-summary-clone table.cart td,
    div.snappay-card .snappay-user-summary-clone table.cart th{
        padding:12px 10px;
        white-space:normal;
        vertical-align:middle;
    }
    div.snappay-card .snappay-user-summary-clone .checkout_summary_final_row td{
        position:sticky;
        bottom:0;
        background:#f4f0ee !important;
        z-index:1;
        font-weight:700;
    }
    div.snappay-card .snappay-user-summary-clone .checkout_mobile_summary{
        display:none;
        min-width:0;
        width:100%;
    }
    div.snappay-card .snappay-user-summary-clone .checkout_mobile_summary .cfp_item{
        min-width:0;
        max-width:none;
        height:auto;
        box-sizing:border-box;
        border:1px solid rgba(0,0,0,0.12);
        border-radius:16px;
        background:#fff;
        box-shadow:none;
    }
    div.snappay-card .snappay-user-summary-clone .checkout_mobile_summary .cfp_item.total{
        background:#f7f3f1;
    }
    div.snappay-card .user_hint{
        white-space:normal;
        overflow-wrap:anywhere;
        word-break:break-word;
    }
    div.snappay-section-stack{
        display:flex;
        flex-direction:column;
        gap:12px;
        align-items:stretch;
        justify-content:flex-start;
        float:none;
        width:100%;
        max-width:100%;
        min-width:0;
    }
    div.snappay-section-stack .column{
        flex:1 1 100%;
        width:100%;
        max-width:100%;
        min-width:0;
        padding:10px;
        float:none;
        box-sizing:border-box;
    }
    div.snappay-summary-bar{
        margin-bottom:4px;
        padding:12px 14px;
        border:1px solid rgba(216,171,63,0.32);
        border-radius:14px;
        background:linear-gradient(180deg,#fff9e8 0%,#fff4d6 100%);
        color:#6c5414;
        line-height:1.8;
    }
    div.snappay-summary-bar.empty{
        border-color:rgba(0,0,0,0.1);
        background:linear-gradient(180deg,#f8f8f8 0%,#f3f3f3 100%);
        color:#555;
    }
    div.snappay-card.pending{
        border-color:rgba(205,45,86,0.18);
    }
    div.snappay-card.history{
        border-color:rgba(0,0,0,0.10);
    }
    div.snappay-card .snappay-card-header{
        align-items:stretch;
        display:flex;
        justify-content:space-between;
        gap:10px;
        flex-wrap:wrap;
    }
    div.snappay-card .snappay-card-title{
        font-size:16px;
        line-height:1.6;
        font-weight:bold;
    }
    div.snappay-card .snappay-card-section-title{
        display:block;
        margin-bottom:4px;
        font-size:16px;
        line-height:1.6;
        font-weight:bold;
    }
    div.snappay-card .snappay-card-meta{
        font-size:12px;
        color:#666;
        display:flex;
        align-items:center;
        gap:8px;
    }
    div.snappay-card .snappay-card-intro{
        margin:0;
        font-size:13px;
        line-height:1.8;
        color:#444;
    }
    div.snappay-card .snappay-change-list{
        margin:0;
        padding:12px;
        border:1px solid rgba(0,0,0,0.08);
        border-radius:10px;
        background:#fbfbfb;
        line-height:1.9;
    }
    div.snappay-card .snappay-review-box{
        border-top:1px solid rgba(128,0,0,0.18);
        padding-top:12px;
        display:flex;
        gap:12px;
        align-items:stretch;
        width:100%;
        max-width:100%;
        min-width:0;
        box-sizing:border-box;
    }
    div.snappay-card .snappay-review-note{
        flex:1 1 auto;
        min-width:0;
    }
    div.snappay-card .snappay-review-label{
        display:block;
        margin-bottom:8px;
        font-weight:bold;
    }
    div.snappay-card .snappay-review-note textarea{
        width:100%;
        min-height:120px;
        box-sizing:border-box;
        margin:0;
        resize:vertical;
    }
    div.snappay-card .snappay-review-actions{
        flex:0 0 240px;
        display:flex;
        flex-direction:column;
        gap:10px;
        border:1px solid rgba(0,0,0,0.12);
        border-radius:16px;
        padding:14px;
        background:#fff;
        box-sizing:border-box;
        min-width:0;
    }
    div.snappay-card .snappay-review-actions .btn{
        width:100%;
        margin:0 !important;
        box-sizing:border-box;
        text-align:center;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:48px;
        padding:12px 16px;
        border-radius:12px;
        font-size:15px;
        line-height:1.4;
        font-weight:800;
    }
    div.snappay-card .snappay-review-actions .btn.approve{
        background:#28a745;
        color:#fff;
    }
    div.snappay-card .snappay-review-actions .btn.deny{
        background:#dc3545;
        color:#fff;
    }
    div.snappay-card .snappay-review-actions .user_hint{
        margin:0;
        font-size:12px;
        line-height:1.6;
    }
    div.snappay-card .snappay-history-row{
        padding:10px 0;
        border-top:1px solid #eee;
        line-height:1.9;
        color:#444;
    }
    div.snappay-card .snappay-history-note{
        margin-top:5px;
        color:#555;
    }
    div.snappay-card.direct-action-card{
        border-color:rgba(40,167,69,0.18);
        background:linear-gradient(180deg,#ffffff 0%,#f8fff8 100%);
    }
    div.snappay-card .snappay-direct-actions{
        display:flex;
        flex-direction:row;
        align-items:stretch;
        flex-wrap:wrap;
        gap:10px;
    }
    div.snappay-card .snappay-direct-actions form{
        flex:1 1 0;
        width:auto;
        margin:0;
    }
    div.snappay-card .snappay-direct-update-shell.hide,
    div.snappay-card .snappay-direct-start.hide,
    div.snappay-card .snappay-direct-cancel-edit.hide{
        display:none;
    }
    div.snappay-card .snappay-direct-update-shell{
        margin-top:12px;
        padding-top:12px;
        border-top:1px solid rgba(0,0,0,0.08);
        width:100%;
        max-width:100%;
        min-width:0;
        box-sizing:border-box;
    }
    div.snappay-card .snappay-action-row{
        display:flex;
        align-items:stretch;
        gap:10px;
        flex-wrap:wrap;
    }
    div.snappay-card .snappay-action-btn,
    div.snappay-card .snappay-direct-actions .btn,
    div.snappay-card .snappay-review-actions .btn,
    div.snappay-card .snappay-direct-start,
    div.snappay-card .snappay-direct-cancel-edit{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        width:100%;
        min-height:48px;
        padding:12px 16px;
        border-radius:12px;
        font-size:15px;
        line-height:1.4;
        font-weight:800;
        text-align:center;
        box-sizing:border-box;
        margin:0 !important;
        border:0;
    }
    div.snappay-card .snappay-direct-actions > .btn,
    div.snappay-card .snappay-action-row > .btn,
    div.snappay-card .snappay-action-row > button,
    div.snappay-card .snappay-action-row > form{
        flex:1 1 0;
    }
    div.snappay-card .snappay-action-row > form .btn{
        width:100%;
    }
    div.snappay-card .snappay-action-btn.secondary,
    div.snappay-card .snappay-direct-cancel-edit{
        background:#efefef;
        color:#111;
    }
    div.snappay-card .snappay-action-btn.approve,
    div.snappay-card .snappay-review-actions .btn.approve{
        background:#28a745;
        color:#fff;
    }
    div.snappay-card .snappay-action-btn.deny,
    div.snappay-card .snappay-review-actions .btn.deny{
        background:#dc3545;
        color:#fff;
    }
    div.snappay-card .order_qty_editor{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:8px;
        direction:ltr;
    }
    div.snappay-card .snappay-user-summary-clone .order_qty_btn{
        min-width:28px;
        width:28px;
        height:28px;
        border-radius:6px;
        background:#d9b1b3;
        color:#fff;
        font-size:22px;
        line-height:1;
        border:0;
    }
    div.snappay-card .snappay-user-summary-clone .order_qty_btn[data-qty-increase]{
        background:#d9b1b3;
    }
    div.snappay-card .snappay-user-summary-clone .order_qty_btn.is-disabled{
        opacity:0.45;
    }
    div.snappay-card .snappay-user-summary-clone .order_qty_value{
        min-width:26px;
        font-size:18px;
        font-weight:700;
        color:#111;
    }
    div.snappay-card .order_qty_editor.is-disabled{
        opacity:0.65;
    }
    div.snappay-card .order_qty_btn{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-width:28px;
        height:28px;
        cursor:pointer;
    }
    div.snappay-card .order_qty_btn.is-disabled{
        opacity:0.45;
        cursor:not-allowed;
    }
    div.snappay-card .order_qty_value{
        min-width:28px;
        text-align:center;
        font-weight:700;
    }
    @media only screen and (max-width:780px){
        div.ssrv.snappay-admin-section{
            padding-left:12px;
        }
        div.snappay-card .snappay-direct-actions,
        div.snappay-card .snappay-action-row{
            flex-direction:column;
        }
        div.snappay-card .snappay-user-summary-clone .checkout_summary_shell{
            display:none;
        }
        div.snappay-card .snappay-user-summary-clone .checkout_mobile_summary{
            display:flex;
            margin-top:10px;
        }
        div.snappay-card .snappay-user-summary-clone .checkout_mobile_summary .cfp_item{
            width:100%;
            margin:6px 0;
        }
        div.snappay-card .snappay-review-box{
            flex-direction:column;
        }
        div.snappay-card .snappay-review-actions{
            flex:1 1 auto;
            width:100%;
        }
    }

</style>

        <div class="ssrv">

            <div class="ssrv_title">مشخصات سفارش <span style="font-size:14px;color:#888;">(کد: <?php echo $unified_id; ?>)</span></div>        

            <div class="ssrv_dtl">

                <form action="<?php echo $URL; ?>" method="post">

                    <div class="form fr flex">

                        <div class="column">

                            <h4>خریدار</h4>

                            <div class="form_item">

                                <label>نام:</label>

                                <input disabled type="text" value="<?php echo $user->data()["name"]; ?>">

                            </div>

                            <div class="form_item">

                                <label>تلفن همراه:</label>

                                <input disabled type="text" value="<?php echo $user->data()["tel"]; ?>">

                            </div>

                            <div class="form_item">

                                <label>رایانامه:</label>

                                <input disabled type="text" value="<?php echo $user->data()["email"]; ?>">

                            </div>

                            

                            <a class="btn" onclick="sub_show('uid','<?php echo $_SESSION["$cf"]->sub_id;?>','clients/')">مشاهده حساب</a> 

                        </div>

                        <div class="column">

                            <h4>آدرس</h4>

                            <div class="form_item">

                                <label>استان:</label>

                                <input disabled type="text" value="<?php echo $address->data()["county"]; ?>">

                            </div>

                            <div class="form_item">

                                <label>شهر:</label>

                                <input disabled type="text" value="<?php echo $address->data()["city"]; ?>">

                            </div>

                            <div class="form_item">

                                <label>آدرس:</label>

                                <input disabled type="text" value="<?php echo $address->data()["address"]; ?>">

                            </div>

                            

                            <a class="btn" onclick="show_addresses(this)">تغییر آدرس (انتخاب)</a> 

                        </div>

                        <div class="column">

                            <h4>زمان‌ها</h4>

                            <div class="form_item">

                                <label>تاریخ ثبت:</label>

                                <input disabled type="text" value="<?php echo correctDate(getVarFromDB($tb,"create_date","id",$oid)); ?>">

                            </div>

                            <div class="form_item">

                                <label>تاریخ ارسال:</label>

                                <input disabled type="text" value="<?php echo correctDate(getVarFromDB($tb,"p_send_date","id",$oid)); ?>">

                            </div>

<?php

    if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state)==1 && getVarFromDB($tb,"admin_state","id",$_SESSION[$cf]->id) == 1){

?>

                            <div class="form_item">

                                <label>4 رقم آخر کارت:</label>

                                <input type="text" name="pay_request_auth" value="<?php echo getVarFromDB($tb,"pay_request_auth","id",$oid); ?>" style="width:95px;">

                                <input type="text" name="edit" class="hide" value="ثبت">

                                <a class="btn small fl" style="" onclick="this.parentNode.parentNode.parentNode.parentNode.submit()"><span class="icon icon-chkfl"></span></a>

                            </div>

<?php        

    }else{

?>

                            <div class="form_item">

                                <label>تاریخ تحویل:</label>

                                <input disabled type="text" value="<?php echo correctDate(getVarFromDB($tb,"recieve_date","id",$oid)); ?>">

                            </div>

                            

<?php

    }

?>

                            <div class="form_item">

                                <label>کد رهگیری پرداخت:</label>

                                <input type="text" name="pay_id" value="<?php echo getVarFromDB($tb,"pay_id","id",$oid); ?>" style="width:95px;">

                                <input type="text" name="edit" class="hide" value="ثبت">

                                <a class="btn small fl" style="" onclick="this.parentNode.parentNode.parentNode.parentNode.submit()"><span class="icon icon-chkfl"></span></a>

                            </div>

                            

                        </div>

                    </div>

                </form>

            </div>

        </div>

<?php
    require_once $bu."modules/snappay/snappay_db.php";
    require_once $bu."modules/snappay/snappay_helpers.php";
    if(file_exists($bu."modules/snappay/snappay_request_handler.php")) {
        require_once $bu."modules/snappay/snappay_request_handler.php";
    }

    $pending_requests = [];
    $request_history = [];
    $latest_processed_request = null;
    $snappay_tx = snappay_tx_get_latest_for_order($oid);
    $snappay_csrf = '';
    $show_snappay_section = false;
    $snappay_direct_action_available = false;
    $snappay_direct_update_summary = null;
    $snappay_direct_admin_uid = 0;
    $snappay_direct_status_note = '';
    $snappay_total_qty = 0;
    $snappay_direct_update_available = false;

    if (function_exists('snappay_list_requests_for_order')) {
        $request_history = snappay_list_requests_for_order($oid, null);
        if (is_array($request_history)) {
            $pending_requests = array_values(array_filter($request_history, function($row){
                return isset($row['status']) && $row['status'] === 'pending';
            }));
            $request_history = array_values(array_filter($request_history, function($row){
                return isset($row['status']) && $row['status'] !== 'pending';
            }));
            if (!empty($request_history)) {
                $latest_processed_request = $request_history[0];
            }
            if (!empty($pending_requests) || !empty($request_history)) {
                $show_snappay_section = true;
            }
        }
    }

    if ($snappay_tx) {
        $final = strtoupper((string)($snappay_tx['final_status'] ?? ''));
        $sn_status = strtoupper((string)($snappay_tx['snappay_status'] ?? ''));
        $show_snappay_section = true;
        if (function_exists('snappay_get_order_total_qty')) {
            $snappay_total_qty = (int)snappay_get_order_total_qty($oid);
        }

        if (isset($_SESSION['a_logged']) && is_object($_SESSION['a_logged'])) {
            if (method_exists($_SESSION['a_logged'], 'get_uid')) {
                $snappay_direct_admin_uid = (int)$_SESSION['a_logged']->get_uid();
            } elseif (property_exists($_SESSION['a_logged'], 'uid')) {
                $snappay_direct_admin_uid = (int)$_SESSION['a_logged']->uid;
            }
        }

        if ((strpos($sn_status, 'SETTLE') !== false) || (strpos($final, 'SETTLE') !== false)) {
            $snappay_direct_action_available = true;
        } else {
            $snappay_direct_status_note = 'اقدام مستقیم مدیر فقط زمانی فعال است که تراکنش اسنپ‌پی در وضعیت تسویه‌شده باشد.';
        }
        $snappay_direct_update_available = ($snappay_direct_action_available && $snappay_total_qty > 1);
        if ($snappay_direct_action_available && $snappay_total_qty <= 1) {
            $snappay_direct_status_note = 'وقتی مجموع تعداد سفارش ۱ است، بروزرسانی مستقیم غیرفعال است و فقط لغو سفارش مجاز است.';
        }

        if (!isset($_SESSION['snappay_csrf']) || !is_string($_SESSION['snappay_csrf']) || strlen($_SESSION['snappay_csrf']) < 32) {
            if (function_exists('random_bytes')) {
                $_SESSION['snappay_csrf'] = bin2hex(random_bytes(16));
            } else {
                $_SESSION['snappay_csrf'] = sha1(uniqid('snappay_csrf_', true));
            }
        }
        $snappay_csrf = (string)$_SESSION['snappay_csrf'];

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST' &&
            isset($_POST['snappay_direct_action']) &&
            isset($_POST['snappay_csrf']) &&
            hash_equals($snappay_csrf, (string)$_POST['snappay_csrf'])
        ) {
            $order_state_before = (int)getVarFromDB('orders', 'state', 'id', $oid);
            $direct_action = strtolower(trim((string)$_POST['snappay_direct_action']));
            if ($direct_action === 'cancel') {
                if ($snappay_direct_action_available) {
                    snappay_apply_cancel($oid, $snappay_direct_admin_uid, $snappay_tx, $order_state_before, 'admin_direct_cancel');
                } else {
                    snappay_request_message(true, 'لغو مستقیم در وضعیت فعلی تراکنش اسنپ‌پی مجاز نیست.');
                }
            } elseif ($direct_action === 'update') {
                $qty_map = [];
                if (isset($_POST['snappay_direct_qty']) && is_array($_POST['snappay_direct_qty'])) {
                    foreach($_POST['snappay_direct_qty'] as $soid => $qty){
                        $qty_map[(int)$soid] = (int)$qty;
                    }
                }
                if ($snappay_direct_update_available) {
                    snappay_apply_update($oid, $snappay_direct_admin_uid, $snappay_tx, $order_state_before, $qty_map, 'admin_direct_update');
                } elseif ($snappay_direct_action_available) {
                    snappay_request_message(true, 'وقتی مجموع تعداد سفارش ۱ است، بروزرسانی مستقیم مجاز نیست و باید از لغو مستقیم سفارش استفاده کنید.');
                } else {
                    snappay_request_message(true, 'بروزرسانی مستقیم در وضعیت فعلی تراکنش اسنپ‌پی مجاز نیست.');
                }
            }
            $snappay_tx = snappay_tx_get_latest_for_order($oid);
            $final = strtoupper((string)($snappay_tx['final_status'] ?? ''));
            $sn_status = strtoupper((string)($snappay_tx['snappay_status'] ?? ''));
            $snappay_direct_action_available = ((strpos($sn_status, 'SETTLE') !== false) || (strpos($final, 'SETTLE') !== false));
            if (function_exists('snappay_get_order_total_qty')) {
                $snappay_total_qty = (int)snappay_get_order_total_qty($oid);
            }
            $snappay_direct_update_available = ($snappay_direct_action_available && $snappay_total_qty > 1);
        }

        if (
            $_SERVER['REQUEST_METHOD'] === 'POST' &&
            isset($_POST['snappay_admin_action']) &&
            isset($_POST['snappay_user_action']) &&
            isset($_POST['snappay_csrf']) &&
            (string)$_POST['snappay_admin_action'] !== ''
        ) {
            $admin_note = isset($_POST['snappay_admin_note']) ? (string)$_POST['snappay_admin_note'] : '';
            $snappay_request_id = isset($_POST['snappay_request_id']) ? (int)$_POST['snappay_request_id'] : 0;
            $qty_map = [];
            if (isset($_POST['snappay_qty']) && is_array($_POST['snappay_qty'])) {
                foreach($_POST['snappay_qty'] as $soid => $qty){
                    $qty_map[(int)$soid] = (int)$qty;
                }
            }
            snappay_handle_admin_request($oid, (string)$_POST['snappay_user_action'], (string)$_POST['snappay_admin_action'], (string)$_POST['snappay_csrf'], $admin_note, $qty_map, $snappay_request_id);
            $snappay_tx = snappay_tx_get_latest_for_order($oid);
        }

        if ($snappay_direct_update_available) {
            $snappay_direct_update_summary = [
                'qty_editable' => true,
                'qty_input_name' => 'snappay_direct_qty',
                'qty_control_mode' => 'decrease_only',
                'qty_inputs_disabled' => true,
                'checkout_scrollable' => true,
                'checkout_mobile_cards' => true
            ];
        }

        if (function_exists('snappay_list_requests_for_order')) {
            $pending_requests = snappay_list_requests_for_order($oid, 'pending');
            $request_history = snappay_list_requests_for_order($oid, null);
            if (is_array($request_history)) {
                $request_history = array_values(array_filter($request_history, function($row){
                    return isset($row['status']) && $row['status'] !== 'pending';
                }));
                if (!empty($request_history)) {
                    $latest_processed_request = $request_history[0];
                }
            }
        }
    }
?>

<?php if($show_snappay_section): ?>
        <div class="ssrv snappay-admin-section">
            <div class="ssrv_title">درخواست‌های تغییر سفارش (اسنپ‌پی)</div>
            <div class="ssrv_dtl">
                <?php if(isset($_SESSION["code_error"])):
                    $snappay_msg = $_SESSION["code_error"];
                    unset($_SESSION["code_error"]);
                    if($snappay_msg !== ''){
                        if($snappay_msg[0] == 'E') echo '<div class="user_hint error">'.substr($snappay_msg,1).'</div>';
                        else echo '<div class="user_hint success">'.substr($snappay_msg,1).'</div>';
                    }
                endif; ?>

                <div class="form fr flex snappay-section-stack">
                    <div class="column">
                        <?php if(is_array($snappay_tx) && !empty($snappay_tx['payment_token'])): ?>
                        <div class="snappay-card direct-action-card">
                            <div class="snappay-card-header">
                                <strong class="snappay-card-title">اقدام مستقیم مدیر</strong>
                                <span class="snappay-card-meta">برای همین سفارش اسنپ‌پی</span>
                            </div>
                            <p class="snappay-card-intro">
                                بدون نیاز به درخواست کاربر، مدیر می‌تواند همین‌جا سفارش اسنپ‌پی را بروزرسانی یا لغو کند.
                            </p>
                            <?php if($snappay_direct_status_note !== ''): ?>
                            <div class="user_hint"><?php echo htmlspecialchars($snappay_direct_status_note, ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endif; ?>
                            <?php if($snappay_direct_action_available): ?>
                            <div class="snappay-direct-actions">
                                <?php if($snappay_direct_update_available && is_array($snappay_direct_update_summary)): ?>
                                <button type="button" class="btn snappay-action-btn approve snappay-direct-start" data-snappay-direct-start>بروزرسانی مستقیم سفارش</button>
                                <?php endif; ?>
                                <form action="<?php echo $URL; ?>" method="post" onsubmit="return snappayDirectCancelSubmit(this);" style="margin:0;">
                                    <input type="hidden" name="snappay_csrf" value="<?php echo htmlspecialchars($snappay_csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="snappay_direct_action" value="cancel">
                                    <input type="hidden" name="edit" value="1">
                                    <button type="submit" class="btn snappay-action-btn deny">لغو مستقیم سفارش</button>
                                </form>
                            </div>
                            <?php if($snappay_direct_update_available && is_array($snappay_direct_update_summary)): ?>
                            <form action="<?php echo $URL; ?>" method="post" class="snappay-direct-update-shell hide" data-snappay-direct-form onsubmit="return snappayDirectUpdateSubmit(this);">
                                <input type="hidden" name="snappay_csrf" value="<?php echo htmlspecialchars($snappay_csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="snappay_direct_action" value="update">
                                <input type="hidden" name="edit" value="1">
                                <div class="snappay-change-list" style="margin-bottom:12px;">
                                    در بروزرسانی باید حداقل یک آیتم در سفارش باقی بماند. برای صفر کردن کل سفارش از دکمه لغو مستقیم سفارش استفاده کنید.
                                </div>
                                <div class="account_order_summary_col snappay-user-summary-clone">
                                <?php cart_render_paid_order_summary($oid, $snappay_direct_update_summary); ?>
                                </div>
                                <div class="snappay-review-actions" style="margin-top:12px;">
                                    <div class="user_hint">بعد از تایید، خلاصه جدید مستقیماً برای اسنپ‌پی ارسال و داخل سفارش ثبت می‌شود.</div>
                                    <div class="snappay-action-row">
                                        <button type="submit" class="btn snappay-action-btn approve">تایید و اجرای بروزرسانی</button>
                                        <button type="button" class="btn snappay-action-btn secondary snappay-direct-cancel-edit" data-snappay-direct-cancel>انصراف</button>
                                    </div>
                                </div>
                            </form>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if(!empty($pending_requests)): ?>
                        <div class="snappay-summary-bar">
                            <strong>وضعیت:</strong> یک درخواست در انتظار بررسی وجود دارد.
                        </div>
                        <?php else: ?>
                        <div class="snappay-summary-bar empty">
                            در حال حاضر درخواستی برای این سفارش ثبت نشده است.
                        </div>
                        <?php endif; ?>

                        <?php if(empty($pending_requests) && is_array($latest_processed_request)): ?>
                        <?php
                            $latest_type_label = ($latest_processed_request['request_type'] == 'update') ? 'آخرین درخواست بروزرسانی سفارش' : 'آخرین درخواست لغو سفارش';
                            $latest_status_label = ($latest_processed_request['status'] == 'approved') ? 'تایید شده' : 'رد شده';
                            $latest_status_class = ($latest_processed_request['status'] == 'approved') ? 'success' : 'error';
                            $latest_snapshot = (isset($latest_processed_request['request_snapshot_data']) && is_array($latest_processed_request['request_snapshot_data'])) ? $latest_processed_request['request_snapshot_data'] : null;
                            $latest_qty_map = (is_array($latest_snapshot) && isset($latest_snapshot['requested_qty_map']) && is_array($latest_snapshot['requested_qty_map'])) ? $latest_snapshot['requested_qty_map'] : array();
                            $latest_financial = (is_array($latest_snapshot) && isset($latest_snapshot['financial']) && is_array($latest_snapshot['financial'])) ? $latest_snapshot['financial'] : array();
                        ?>
                        <div class="snappay-card history">
                            <div class="snappay-card-header">
                                <strong class="snappay-card-title"><?php echo $latest_type_label; ?></strong>
                                <span class="snappay-card-meta"><?php echo htmlspecialchars((string)$latest_processed_request['request_date'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="user_hint <?php echo $latest_status_class; ?>">
                                وضعیت نهایی این درخواست: <?php echo $latest_status_label; ?>
                                <?php if(!empty($latest_processed_request['action_date'])): ?>
                                    - <?php echo htmlspecialchars((string)$latest_processed_request['action_date'], ENT_QUOTES, 'UTF-8'); ?>
                                <?php endif; ?>
                            </div>
                            <?php if($latest_processed_request['request_type'] === 'update' && is_array($latest_snapshot) && !empty($latest_snapshot['changed_items'])): ?>
                            <div class="snappay-change-list">
                                <strong>خلاصه تغییرات ثبت‌شده:</strong><br>
                                <?php foreach($latest_snapshot['changed_items'] as $change_row): ?>
                                    <?php if(!is_array($change_row)) continue; ?>
                                    <?php echo htmlspecialchars((string)($change_row['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>:
                                    <?php echo (int)($change_row['old_qty'] ?? 0); ?> -> <?php echo (int)($change_row['new_qty'] ?? 0); ?><br>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            <?php if(is_array($latest_snapshot)): ?>
                                <div class="account_order_summary_col snappay-user-summary-clone">
                                <?php cart_render_paid_order_summary($oid, [
                                    'qty_override_map' => $latest_qty_map,
                                    'financial_override' => $latest_financial,
                                    'checkout_scrollable' => true,
                                    'checkout_mobile_cards' => true
                                ]); ?>
                                </div>
                            <?php endif; ?>
                            <?php if(!empty($latest_processed_request['admin_note'])): ?>
                            <div class="snappay-history-note"><strong>توضیح مدیر:</strong> <?php echo htmlspecialchars((string)$latest_processed_request['admin_note'], ENT_QUOTES, 'UTF-8'); ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php foreach($pending_requests as $req):
                            $type_label = ($req['request_type'] == 'update') ? 'درخواست بروزرسانی سفارش' : 'درخواست لغو سفارش';
                            $request_snapshot = (isset($req['request_snapshot_data']) && is_array($req['request_snapshot_data'])) ? $req['request_snapshot_data'] : null;
                            $request_qty_map = (is_array($request_snapshot) && isset($request_snapshot['requested_qty_map']) && is_array($request_snapshot['requested_qty_map'])) ? $request_snapshot['requested_qty_map'] : array();
                            $request_financial = (is_array($request_snapshot) && isset($request_snapshot['financial']) && is_array($request_snapshot['financial'])) ? $request_snapshot['financial'] : array();
                        ?>
                        <div class="snappay-card pending">
                            <div class="snappay-card-header">
                                <strong class="snappay-card-title"><?php echo $type_label; ?></strong>
                                <span class="snappay-card-meta"><?php echo htmlspecialchars((string)$req['request_date'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>

                            <p class="snappay-card-intro">
                                این درخواست توسط کاربر ثبت شده است. در صورت تایید، عملیات نهایی اسنپ‌پی همین‌جا اجرا می‌شود.
                            </p>

                            <form action="<?php echo $URL; ?>" method="post" onsubmit="return snappayAdminFormGuard(this);">
                                <input type="hidden" name="snappay_csrf" value="<?php echo htmlspecialchars($snappay_csrf, ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="snappay_request_id" value="<?php echo (int)$req['id']; ?>">
                                <input type="hidden" name="snappay_user_action" value="<?php echo htmlspecialchars((string)$req['request_type'], ENT_QUOTES, 'UTF-8'); ?>">
                                <input type="hidden" name="snappay_admin_action" value="">
                                <input type="hidden" name="edit" value="1">
                                <?php if($req['request_type'] === 'update' && is_array($request_snapshot) && !empty($request_snapshot['changed_items'])): ?>
                                <div class="snappay-change-list">
                                    <strong>خلاصه تغییرات ثبت‌شده:</strong><br>
                                    <?php foreach($request_snapshot['changed_items'] as $change_row): ?>
                                        <?php if(!is_array($change_row)) continue; ?>
                                        <?php echo htmlspecialchars((string)($change_row['name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>:
                                        <?php echo (int)($change_row['old_qty'] ?? 0); ?> -> <?php echo (int)($change_row['new_qty'] ?? 0); ?><br>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                <div class="account_order_summary_col snappay-user-summary-clone">
                                <?php cart_render_paid_order_summary($oid, [
                                    'qty_override_map' => $request_qty_map,
                                    'financial_override' => $request_financial,
                                    'checkout_scrollable' => true,
                                    'checkout_mobile_cards' => true
                                ]); ?>
                                </div>
                                <div class="snappay-review-box">
                                    <div class="snappay-review-note">
                                        <label for="snappay_admin_note_<?php echo (int)$req['id']; ?>" class="snappay-review-label">توضیح مدیر</label>
                                        <textarea id="snappay_admin_note_<?php echo (int)$req['id']; ?>" name="snappay_admin_note" placeholder="برای تایید یا رد درخواست، توضیح خود را اینجا بنویسید."><?php echo !empty($req['admin_note']) ? htmlspecialchars((string)$req['admin_note'], ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                                    </div>
                                    <div class="snappay-review-actions">
                                        <div class="user_hint">عملیات بررسی از همین بخش انجام می‌شود. متن بالا برای تایید یا رد درخواست ثبت خواهد شد.</div>
                                        <div class="snappay-action-row">
                                            <button type="button" class="btn snappay-action-btn approve" onclick="return snappaySubmitAdminDecision(this.form, 'approve', 'آیا از تایید درخواست <?php echo $type_label; ?> برای این سفارش مطمئن هستید؟');">
                                                <span class="icon icon-chkfl"></span> تایید و اجرای درخواست
                                            </button>
                                            <button type="button" class="btn snappay-action-btn deny" onclick="return snappaySubmitAdminDecision(this.form, 'deny', 'آیا از رد درخواست <?php echo $type_label; ?> مطمئن هستید؟');">
                                                <span class="icon icon-close"></span> رد درخواست
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <?php endforeach; ?>

                        <?php if(!empty($request_history)): ?>
                        <div class="snappay-card history">
                            <strong class="snappay-card-section-title">سوابق درخواست‌ها</strong>
                            <?php foreach($request_history as $req):
                                $type_label = ($req['request_type'] == 'update') ? 'بروزرسانی' : 'لغو';
                                $status_label = 'در انتظار';
                                $status_color = '#856404';
                                if($req['status'] == 'approved'){
                                    $status_label = 'تایید شده';
                                    $status_color = '#155724';
                                }elseif($req['status'] == 'denied'){
                                    $status_label = 'رد شده';
                                    $status_color = '#721c24';
                                }
                            ?>
                            <div class="snappay-history-row">
                                <strong><?php echo $type_label; ?></strong>
                                - <span style="color:<?php echo $status_color; ?>;"><?php echo $status_label; ?></span>
                                - <span><?php echo htmlspecialchars((string)$req['request_date'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php if(!empty($req['action_date'])): ?>
                                    <span> / <?php echo htmlspecialchars((string)$req['action_date'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                                <?php if(!empty($req['admin_note'])): ?>
                                    <div class="snappay-history-note"><strong>توضیح مدیر:</strong> <?php echo htmlspecialchars((string)$req['admin_note'], ENT_QUOTES, 'UTF-8'); ?></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
<?php endif; ?>

<script type="text/javascript">
function snappayAdminFormGuard(form){
    if(!form){ return false; }
    var actionInput = form.querySelector('input[name="snappay_admin_action"]');
    return !!(actionInput && actionInput.value);
}

function snappaySubmitAdminDecision(form, action, message){
    if(!form){ return false; }
    if(action !== 'approve' && action !== 'deny'){ return false; }
    if(message && !window.confirm(message)){ return false; }
    var actionInput = form.querySelector('input[name="snappay_admin_action"]');
    if(!actionInput){ return false; }
    actionInput.value = action;
    form.submit();
    return false;
}

function snappayDirectCancelSubmit(form){
    if(!form){ return false; }
    return window.confirm('آیا از لغو مستقیم این سفارش اسنپ‌پی مطمئن هستید؟');
}

function snappayDirectUpdateSubmit(form){
    if(!form){ return false; }
    var inputs = form.querySelectorAll('[data-qty-input]');
    var changed = false;
    var totalQty = 0;
    for(var i = 0; i < inputs.length; i++){
        var input = inputs[i];
        var initial = parseInt(input.getAttribute('data-initial-qty') || input.defaultValue || '0', 10);
        var current = parseInt(input.value || '0', 10);
        totalQty += Math.max(0, current || 0);
        if(current !== initial){
            changed = true;
        }
    }
    if(!changed){
        alert('تغییری در تعداد آیتم‌ها ثبت نشده است.');
        return false;
    }
    if(totalQty < 1){
        alert('برای صفر کردن کل سفارش باید از دکمه لغو مستقیم سفارش استفاده کنید.');
        return false;
    }
    return window.confirm('آیا از اجرای مستقیم بروزرسانی این سفارش اسنپ‌پی مطمئن هستید؟');
}

function snappayNormalizeSummaryTotals(scope){
    if(!scope){ return; }
    var finalRow = scope.querySelector('.checkout_summary_final_row');
    if(finalRow){
        var cells = Array.prototype.slice.call(finalRow.querySelectorAll('td'));
        if(cells.length >= 4){
            var weightLabel = cells[0] ? cells[0].textContent.trim() : 'وزن کل';
            var weightValue = cells[1] ? cells[1].textContent.trim() : '-';
            var amountValue = cells[cells.length - 1] ? cells[cells.length - 1].textContent.trim() : '0';
            var hasDiscountCode = !!scope.querySelector('span[dir="ltr"]');
            var totalLabel = hasDiscountCode ? 'جمع قبل از کد تخفیف' : 'جمع کل';
            finalRow.innerHTML = ''
                + '<td>' + weightLabel + '</td>'
                + '<td colspan="2">' + weightValue + '</td>'
                + '<td colspan="3">' + totalLabel + '</td>'
                + '<td>' + amountValue + '</td>';
        }
    }

    var mobileTotalsCard = scope.querySelector('.checkout_mobile_totals_card');
    if(mobileTotalsCard){
        var totals = Array.prototype.slice.call(mobileTotalsCard.querySelectorAll('.cfpi_number'));
        totals.forEach(function(node){
            if(node.textContent.indexOf('جمع بدون تخفیف') > -1){
                node.remove();
            }
        });
    }
}

function initSnappayAdminSummaries(){
    var sections = document.querySelectorAll('.snappay-user-summary-clone');
    Array.prototype.forEach.call(sections, function(section){
        snappayNormalizeSummaryTotals(section);
    });
}

(function(){
    function initSnappayDirectUpdateCard(){
        var form = document.querySelector('[data-snappay-direct-form]');
        if(!form || form.getAttribute('data-snappay-direct-bound') === '1'){ return; }
        form.setAttribute('data-snappay-direct-bound', '1');
        snappayNormalizeSummaryTotals(form);

        var startBtn = document.querySelector('[data-snappay-direct-start]');
        var cancelBtn = form.querySelector('[data-snappay-direct-cancel]');
        var qtyInputs = Array.prototype.slice.call(form.querySelectorAll('[data-qty-input]'));
        var qtyEditors = Array.prototype.slice.call(form.querySelectorAll('[data-qty-editor]'));
        var qtyInputsByKey = {};

        qtyInputs.forEach(function(input){
            var key = input.getAttribute('data-qty-key') || '';
            if(!key){ return; }
            if(!qtyInputsByKey[key]){
                qtyInputsByKey[key] = [];
            }
            qtyInputsByKey[key].push(input);
        });

        function getTotalQty(){
            var total = 0;
            qtyInputs.forEach(function(input){
                total += Math.max(0, parseInt(input.value || '0', 10) || 0);
            });
            return total;
        }

        function syncQtyEditor(editor){
            if(!editor){ return; }
            var input = editor.querySelector('[data-qty-input]');
            var valueNode = editor.querySelector('[data-qty-value]');
            var incBtn = editor.querySelector('[data-qty-increase]');
            var decBtn = editor.querySelector('[data-qty-decrease]');
            if(!input || !valueNode){ return; }
            var current = Math.max(0, parseInt(input.value || '0', 10) || 0);
            var initial = Math.max(0, parseInt(input.getAttribute('data-initial-qty') || input.defaultValue || current, 10) || 0);
            valueNode.textContent = current;
            if(incBtn){
                var increaseDisabled = input.disabled || current >= initial;
                incBtn.classList.toggle('is-disabled', increaseDisabled);
                incBtn.setAttribute('aria-disabled', increaseDisabled ? 'true' : 'false');
            }
            if(decBtn){
                decBtn.classList.toggle('is-disabled', input.disabled || current <= 0 || getTotalQty() <= 1);
            }
        }

        function syncQtyGroup(key, nextValue){
            var group = qtyInputsByKey[key] || [];
            group.forEach(function(input){
                input.value = nextValue;
            });
            qtyEditors.forEach(function(editor){
                var editorInput = editor.querySelector('[data-qty-input]');
                if(!editorInput){ return; }
                if((editorInput.getAttribute('data-qty-key') || '') !== key){ return; }
                syncQtyEditor(editor);
            });
        }

        function setEditMode(isEditing){
            form.classList.toggle('hide', !isEditing);
            if(startBtn){ startBtn.classList.toggle('hide', isEditing); }
            qtyInputs.forEach(function(input){
                if(!isEditing){
                    input.value = input.getAttribute('data-initial-qty') || input.defaultValue || input.value;
                }
                input.disabled = !isEditing;
            });
            qtyEditors.forEach(function(editor){
                editor.classList.toggle('is-disabled', !isEditing);
                syncQtyEditor(editor);
            });
        }

        qtyEditors.forEach(function(editor){
            syncQtyEditor(editor);
            var incBtn = editor.querySelector('[data-qty-increase]');
            var decBtn = editor.querySelector('[data-qty-decrease]');
            if(incBtn){
                incBtn.addEventListener('click', function(){
                    var input = editor.querySelector('[data-qty-input]');
                    if(incBtn.classList.contains('is-disabled') || !input || input.disabled){ return; }
                    var current = Math.max(0, parseInt(input.value || '0', 10) || 0);
                    var initial = Math.max(0, parseInt(input.getAttribute('data-initial-qty') || input.defaultValue || current, 10) || 0);
                    var nextValue = Math.min(initial, current + 1);
                    var key = input.getAttribute('data-qty-key') || '';
                    if(key){
                        syncQtyGroup(key, nextValue);
                    }else{
                        input.value = nextValue;
                        syncQtyEditor(editor);
                    }
                });
            }
            if(decBtn){
                decBtn.addEventListener('click', function(){
                    var input = editor.querySelector('[data-qty-input]');
                    if(decBtn.classList.contains('is-disabled') || !input || input.disabled){ return; }
                    var current = Math.max(0, parseInt(input.value || '0', 10) || 0);
                    var nextValue = Math.max(0, current - 1);
                    var key = input.getAttribute('data-qty-key') || '';
                    if(key){
                        syncQtyGroup(key, nextValue);
                    }else{
                        input.value = nextValue;
                        syncQtyEditor(editor);
                    }
                });
            }
        });

        if(startBtn){
            startBtn.addEventListener('click', function(){
                setEditMode(true);
            });
        }
        if(cancelBtn){
            cancelBtn.addEventListener('click', function(){
                setEditMode(false);
            });
        }

        setEditMode(false);
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', function(){
            initSnappayAdminSummaries();
            initSnappayDirectUpdateCard();
        });
    }else{
        initSnappayAdminSummaries();
        initSnappayDirectUpdateCard();
    }
})();
</script>

<!--/\\\\\\\\\\\\\\\\\\\\\\\\\\\/-->
    <div class="addresses_holder hide">

        <table class="tracking">

            <thead>

                <tr>

                    <th>

                        انتخاب

                    </th>

                    <th class="meX">

                        ردیف

                    </th>

                    <th>

                        استان

                    </th>

                    <th>

                        شهر

                    </th>

                    <th>

                        آدرس 

                    </th>

                    <th class="meX">

                        کد پستی

                    </th>

                    <th class="meX">

                        نام گیرنده

                    </th>

                    <th class="meX">

                        تلفن گیرنده

                    </th>

                    <th class="meX">

                        تحویل به نگهبان

                    </th>



                </tr>

            </thead>

            <tbody>

<?php    

    $i=0;

    foreach($addresses as $address){

        $data_a = $address->data();

        $i++;

?>

                <tr>

                    <td >

                            <a onclick ="sub_show('change_aid','<?php echo $data_a["aid"] ?>')" class="btn middle">انتخاب </a>

                    </td>

                    <td class="meX">

                        <?php echo $i;?> 

                    </td>

                    <td>

                        <?php echo $data_a["county"];?>

                    </td>

                    <td>

                        <?php echo $data_a["city"];?>

                    </td>

                    <td>

                        <?php echo $data_a["address"];?>

                    </td>

                    <td class="meX">

                        <?php echo $data_a["post_code"];?>

                    </td>

                    <td class="meX">

                        <?php echo $data_a["rec_name"];?>

                    </td>

                    <td class="meX">

                        <?php echo $data_a["rec_tel"];?>

                    </td>

                    <td class="meX">

<?php

    $class = "icon-chk";

    if($data_a["janitor"] == "yes")$class = "icon-chkfl";

?>

                        <span class="checkbox <?php echo $class; ?>"></span>

                    </td>



                </tr>

<?php

    }

?>

            </tbody>

        </table>

        برای تغییر یا افزودن آدرس، وارد حساب کاربری مشتری شوید.

    </div>

<!--/\\\\\\\\\\\\\\\\\\\\\\\\\\\/-->



<script type="text/javascript">

function show_addresses(item){

    holder = document.getElementsByClassName('addresses_holder')[0];

    if(holder.classList.contains('hide')){

        document.getElementsByClassName('addresses_holder')[0].classList.remove('hide');

        item.innerHTML = 'بستن لیست آدرس‌ها';

    }else{

        document.getElementsByClassName('addresses_holder')[0].classList.add('hide');

        item.innerHTML = 'تغییر آدرس (انتخاب)';

    }

}

</script>

<style type="text/css">

div.addresses_holder{

    margin:auto;

}

div.addresses_holder table{

    margin-bottom:10px;

}

div.addresses_holder a.btn.middle{

    font-size: 14px;

    padding:2px;

}



@media only screen and (max-width:700px){

    table.tracking .meX{

        display:none;

    }

    table.tracking td,table.tracking th{

        font-size:13px;

        padding:6px !important;

    }

    table.tracking a.btn.middle{

        font-size: 10px;

        padding:2px;

    }

}





@media only screen and (max-width:700px)

{

    div#sect1 div.title{

        font-size:20px;

        display:table;

        margin:auto;

    }

    div.ssrv{

        width:90%;

        height:auto;

        display:table;

        margin:auto;

        margin-bottom:10px;

        padding:5px;

        padding-right:5px;

    }

    div.ssrv div.ssrv_title{

        transform:none;

        width:100%;

        height:40px;

        line-height:40px;

        font-size:20px;

        position:relative;

        padding:0;

        

    }

    div.ssrv div.ssrv_dtl{

        width:100%;

        margin:auto;

        height:auto;

    }

    div.ssrv div.form{

        width:100%;

        margin:0;

        height:auto;

    }

    div.ssrv div.form.flex{

        justify-content:center;

    }

    div.ssrv textarea{

        width:100%;

        min-width:280px;

        margin:auto;

        margin-bottom:5px;

        display:block;

    }

    div.ssrv div#invoice_values .enabled input[type="text"]{

        width:200px;

        clear:both;

    }

    div.ssrv div#invoice_values input.btn{

        margin-top:5px;

    }

    div.ssrv div.form_item.multi{

        width:100%;

    }

}

@media only screen and (max-width:500px){

    div#sect1 div.title{

        margin-top:30px;

    }

    form div.form_item.date label{

        margin:0;

    }

}

@media only screen and (max-width:400px){

    div.ssrv{

        width:100%;

        min-width:300px;

    }

}

</style>

    <div class="cut w100p"></div>

            <div class="ssrv">

                <div class="ssrv_title">توضیحات</div>        

                <div class="ssrv_dtl">

                    <form action="<?php echo $URL; ?>" method="post">

                        <div class="form fr">

                            <div class="form_item">

                                <label>توضیحات:</label>

                                <textarea name="order_detail" type="text" ><?php echo $order_detail;?></textarea>

                            </div>

                            <input type="submit" class="btn" name="edit" value="ثبت">

                        </div>

                    </form>

                </div>

            </div>



<?php

    if($_SESSION["a_logged"]->get_level() >= 2){

?>

            <div class="ssrv">

                <div class="ssrv_title">تخفیف و پرداخت</div>        

                <div class="ssrv_dtl">

                    <form class="" action="<?php echo $URL; ?>" method="post">

                        <div class="form fr" id="invoice_values">

<?php

    foreach($fields[1] as $f){

?>

                            <div class="form_item multi">

                                <label><?php echo $labels[$f]; ?>: </label>

                                <input disabled type="text" value="<?php echo $invoice_value[$f]; ?>">

                            </div>

<?php

    }

?>                    

    <div class="cb"></div>

<?php

    foreach($fields[2] as $f){

?>

                            <div class="form_item enabled">

                                <label><?php echo $labels[$f]; ?>: </label>

                                <input name="<?php echo $f; ?>" type="text" value="<?php echo $invoice_value[$f]; ?>">

                                <a class="btn small fl" style="" onclick="fill_value('<?php echo $f; ?>',this)"><span class="icon icon-chkfl"></span></a>

                            </div>

<?php

    }

?>

                            <input class="hide" name="invoice" value="sale">

                            <input type="submit" class="btn" name="edit" value="ثبت">

                        </div>

                    </form>

                </div>

            </div>

<?php

    }

?>                

            <div class="ssrv">

                <div class="ssrv_title">ارسال</div>        

                <div class="ssrv_dtl">

                    <form class="" action="<?php echo $URL; ?>" method="post">

                        <div class="form fr">

                            <div class="form_item date" id="date_box">

<?php

    $dsbl_date="";$dsbl_prid="";

    if(getVarFromDB($atb,"id","flag",$_SESSION[$cf]->state) == 5){$dsbl_date = "disabled";}

    else{$dsbl_prid = "disabled";}

    

    if($rec_shift = getVarFromDB($tb,"recieve_shift","id",$oid)){

        if($rec_shift < 3){

            ?>

                                <label for="shift">نوع ارسال:</label> 

                                <select name="shift" <?php echo $dsbl_date; ?>>

<?php

    $tp_id = getVarFromDB("transporters","id","name","پست");

    $st = "SELECT id,name FROM sd_shifts WHERE transporter_id = $tp_id AND del_flag = 0 ORDER BY id ASC";

    $st = $mysqli->prepare($st);

    if(!$st->execute()){

        echo "E";

        exit;

    }

    $res = $st->get_result();

    $shifts = array();

    while($row = $res->fetch_assoc())

    {

        $shifts[$row["id"]] = $row["name"];

    }

    $shifts[3] = cor_sendShift(3);

    foreach($shifts as $shift=>$name){

        $selected = "";

        if($rec_shift == $shift)$selected=" selected ";

        echo '<option '.$selected.' value="'.$shift.'">'.$name.'</option>';

    }

?>

                                </select>

<?php

        }else{

            if($rec_date = getVarFromDB($tb,"recieve_date","id",$oid)){

                $date_name="rec_date";

                $_SESSION[$cf]->sql_where[$date_name]=CaSDate(strtotime($rec_date));

            }

            $date_box="";$date_btns="";

            if($rec_shift == 3){    

                $date_btns = "disabled";

            }else{

                $date_box = "disabled";

            }

?>

                                تحویل: 

                                <label for="shift">نوبت:</label> 

                                <select name="shift" <?php echo $dsbl_date; ?> onchange="date_field_toggle(this[this.selectedIndex].value)">

<?php

    $active_transporter = getVarFromDB("sd_setting","value","flag","tp_id");

    $st = "SELECT id FROM sd_shifts WHERE transporter_id = $active_transporter AND del_flag = 0 ORDER BY id ASC";

    $st = $mysqli->prepare($st);

    if(!$st->execute()){

        echo "E";

        exit;

    }

    $res = $st->get_result();

    $shifts = array();

    $shifts[2] = cor_sendShift(2);

    $shifts[3] = cor_sendShift(3);

    while($row = $res->fetch_assoc())

    {

        $shifts[$row["id"]] = cor_sendShift($row["id"]);

    }

    foreach($shifts as $shift=>$name){

        $selected = "";

        if($rec_shift == $shift)$selected=" selected ";

        echo '<option '.$selected.' value="'.$shift.'">'.$name.'</option>';

    }

?>

                                </select>

                                <div class="date_box">

                                    <label for="day">روز:</label> 

                                    <select name="day" <?php echo $dsbl_date." ".$date_box; ?> >

                                        <?php

                                            include $GLOBALS['bu']."modules/time/select_day.php";

                                        ?>

                                    </select>

                                    <label for="month">ماه:</label> 

                                    <select name="month" <?php echo $dsbl_date." ".$date_box; ?> >

                                        <?php

                                            include $GLOBALS['bu']."modules/time/select_month.php";

                                        ?>

                                    </select>

                                    <label for="year">سال:</label> 

                                    <select name="year" <?php echo $dsbl_date." ".$date_box; ?> >

                                        <?php

                                            include $GLOBALS['bu']."modules/time/select_year.php";

                                        ?>

                                    </select>

                                </div>

                                

                            </div>

                            <div class="form_item">

                                <input id="shift_input" name="shift_" value="" class="hide" type="text">

                                <input id="id_input" name="id" value="" class="hide" type="text">

                                <input id="alt_input" name="alt" value="" class="hide" type="text">

                                <div id="date_btns" class="send_date_selection <?php echo $date_btns." ".$dsbl_date; ?>">

<?php

    $timestamp = time();

    $sd_start_user = getVarFromDB("sd_setting","value","flag","sd_start");

    $sd_start = -1;

    $sd_length = getVarFromDB("sd_setting","value","flag","sd_length");

    $sd_limit = (int)getVarFromDB("sd_setting","value","flag","sd_limit");

    $start_date = $timestamp + $sd_start*24*3600 - 2.5*3600;

    $end_date = $timestamp + ($sd_start_user+$sd_length+2)*24*3600;

    $start_ts = date("Y-m-d H:i:s",$start_date);

    $end_ts = date("Y-m-d H:i:s",$end_date);

    

    // echo $sd_limit;

    

    $st = "SELECT id,date,state,order_no FROM send_date WHERE date>'$start_ts' AND date<'$end_ts' ORDER BY date ASC";

    $st = $mysqli->prepare($st);

    if(!$st->execute()){

        echo "E";

        exit;

    }

    $res = $st->get_result();

    $k = 0;

    $days = array();

    include_once $GLOBALS['bu']."modules/jdf.php";

    while($row = $res->fetch_assoc())

    {

        

        $id = $row["id"];

        $state = $row["state"];

        $datets = strtotime($row["date"]);

        $date = cd_show_date($datets);

        $order_no = $row["order_no"];

        $days[$date] = [$id,$state,$datets,$order_no,$k];

        $k++;

        // echo "<br>";

        // var_dump($days[$date]);

    }

    $show_turns = array();

    

    $pickup_hour = (int)getVarFromDB("sd_setting","value","flag","pickup");

    $active_transporter = getVarFromDB("sd_setting","value","flag","tp_id");

    $st = "SELECT * FROM sd_shifts WHERE transporter_id = $active_transporter AND del_flag = 0";

    $st = $mysqli->prepare($st);

    if(!$st->execute()){

        echo "E";

        exit;

    }

    $res = $st->get_result();

    $shifts = array();

    while($row = $res->fetch_assoc())

    {

        $shifts[$row["id"]] = [$row["name"],(int)$row["start_hour"],(int)$row["end_hour"]];

    }

    foreach($days as $date=>$value){

        switch($value[1]){

            case "official":

                if(isset($show_turns[$date])){

                    foreach($shifts as $shift =>$v){

                        if(isset($show_turns[$date][$shift])){

                            if($show_turns[$date][$shift][1] == "send"){

                                if($show_turns[$date][$shift][3] > 0 && $show_turns[$date][$shift][3] < 2){

                                    $show_turns[$date][$shift][1] = "send_postpone";

                                    $date_p1_ts = $show_turns[$date][$shift][2]+24*3600;

                                    $date_p1 = cd_show_date($date_p1_ts);

                                    $show_turns[$date_p1][$shift] = [$show_turns[$date][$shift][0],"send",$date_p1_ts,$show_turns[$date][$shift][3]+1,$show_turns[$date][$shift][4],$show_turns[$date][$shift][5]];

                                }else{

                                    $show_turns[$date][$shift][1] = "official";

                                    $date_p1_ts = $show_turns[$date][$shift][2]+24*3600;

                                    $date_p1 = cd_show_date($date_p1_ts);

                                    $show_turns[$date_p1][$shift][1] = "official";

                                }

                            }

                        }

                    }

                }else{ 

                    foreach($shifts as $shift=>$v){

                        $show_turns[$date][$shift] = $value;

                    }

                }

                break;

            case "internal":

                break;

            default:

                $date_p1_ts = $value[2];

                $date_p1 = cd_show_date($date_p1_ts);

                foreach($shifts as $shift=>$v){

                    if($v[1]>$pickup_hour){

                        $show_turns[$date_p1][$shift] = [$value[0],"send",$date_p1_ts,0,$value[3],$value[4]];

                    }

                }

                $date_p2_ts = $date_p1_ts+24*3600;

                $date_p2 = cd_show_date($date_p2_ts);

                foreach($shifts as $shift=>$v){

                        $show_turns[$date_p2][$shift] = [$value[0],"send",$date_p2_ts,1,$value[3],$value[4]];

                }

                break;

        }

    }

?>



<?php

$k = 0;

    foreach($show_turns as $date=>$turn){

        if($k > $sd_length)break;

        else{$k++;}

        

        $thisdate = 0;

        foreach($shifts as $shift=>$v){

            if(isset($turn[$shift]) && $turn[$shift][1] == "send"){

                $thisdate = 1;   

            }

        }

        if($thisdate == 1){

            foreach($shifts as $shift=>$v){

                $v_name = $v[0];

                $v_time = $v[1]." - ".$v[2];

                $shift_class = " shift_$shift ";

                if($rec_shift != $shift)$shift_class.=" hide ";

                // if(isset($turn[$shift]) && $turn[$shift][1] == "send" && $turn[$shift][4]<$sd_limit){

                if(isset($turn[$shift]) && $turn[$shift][1] == "send"){

                    $sdid = $turn[$shift][0];

                    $n2s = $turn[$shift][3];

                    $admin_color = "";

                    if($turn[$shift][5]<$sd_start_user)$admin_color = " admin ";

                    echo "

                            <a class='btn mid half fr $shift_class $admin_color' onclick='select_date(this)' id='$sdid' alt='$n2s' name='$shift' >$date</a>

                        ";

                }

            }

        }

    }

?>

<br>

           <div class="cb"></div> 

</div>

<script type="text/javascript">

    function select_date(item){

        if(item.parentNode.getElementsByClassName('selected')[0])

        item.parentNode.getElementsByClassName('selected')[0].classList.remove('selected');

        item.classList.add('selected');

//        date = item.innerHTML;

//        date = date.substring(0,date.indexOf("-"));

        document.getElementById('id_input').value = item.id;

        document.getElementById('shift_input').value = item.getAttribute('name');

        document.getElementById('alt_input').value = item.getAttribute('alt');

//        document.getElementById('date_input').value = date;

        return;

    }

</script>

<?php

   }

?>                               

<?php

        }

?>

                            </div>

                            

<?php

    if(getVarFromDB($tb,"state","id",$_SESSION[$cf]->id) > 3){

?>

                            <div class="form_item date">

<?php

    $post_ref_id = getVarFromDB($tb,"post_ref_id","id",$oid);

    if(strlen($post_ref_id) == 24){

        $tp_id = 1;

        $input_class = " ";

    }else{

        $tp_id = $post_ref_id;

        $post_ref_id = "";

        $input_class = " hide ";

    }

?>

                                <label>ارسال‌کننده:</label>

                                <select name="tp_id" <?php echo $dsbl_prid; ?> onchange="pr_id_toggle(this[this.selectedIndex].value)">

<?php

    $st = "SELECT id,name FROM transporters

    WHERE del_flag = 0";

    $st = $mysqli->prepare($st);

    if(!$st->execute()){

        echo "E";

        exit;

    }

    $res = $st->get_result();

    while($row = $res->fetch_assoc())

    {

        $name = $row["name"];

        $id = $row["id"];

        $selected = "";

        if($tp_id == $id)$selected=" selected ";

?>

                                    <option <?php echo $selected; ?> value="<?php echo $id; ?>"><?php echo $name; ?></option>

<?php

    }

?>                                    

                                </select>

                                <label class="post_ref_id <?php echo $input_class; ?>" for="post_ref_id" >کد مرسوله:</label>

                                <input class="post_ref_id <?php echo $input_class; ?>" <?php echo $dsbl_prid; ?>  name="post_ref_id" type="text" value="<?php echo $post_ref_id ?>">

<script type="text/javascript">

    function pr_id_toggle(tp_id){

        items = document.getElementsByClassName("post_ref_id");

        if(tp_id == 1){

            for(i=0;i<items.length;i++){

                items[i].classList.remove("hide");

            }

        }else{

            for(i=0;i<items.length;i++){

                items[i].classList.add("hide");

            }

        }

    }

</script>

                            </div>

<?php

    }

?>

                            <input class="btn" type="submit" name="edit" value="ثبت"> 

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>



    <div class="cut w100p"></div>



    <div id="sect2" class="sect ">

        <div class="middle container">

            <h2 class="tac">سبد خرید</h2><br>

        <table class="tracking">

            <thead>

                <tr>

                    <th>

                        ردیف

                    </th>

                    <th>

                        شناسه

                    </th>

                    <th>

                        نام

                    </th>

                    <th>

                        وزن 

                    </th>

                    <th>

                        #

                    </th>

                    <th>

                        قیمت

                    </th>

                    <th>

                        مجموع

                    </th>

                </tr>

            </thead>

            <tbody>

<?php

    $cart = new cart_read($oid);

    $i = 0;

    foreach($cart->orders as $item){

        $i++;

        $name = getVarFromDB("products","name","id",$item->pid);

?>

                <tr>

                    <td>

                        <?php echo ($i);?> 

                    </td>

                    <td>

                        <?php echo $item->pid;?>

                    </td>

                    <td>

                        <?php echo $name;?>

                    </td>

                    <td>

                        <?php echo $item->weight;?>

                    </td>

                    <td>

                        <?php echo $item->no;?>

                    </td>

                    <td>

                        <?php echo price_sep($item->price);?>

                    </td>

                    <td>

                        <?php echo price_sep($item->price*$item->no); ?>

                    </td>

                </tr>

<?php

    }

?> 

                <tr>

                    <td colspan="3">

                        

                    مجموع

                        

                    </td>

                    <td>

                        <?php echo $cart->weight();?>

                    </td>

                    <td>

                        <?php echo $cart->number()?>

                    </td>

                    <td>

                        

                    </td>

                    <td>

                        <?php echo price_sep($cart->price());?>

                    </td>

                </tr>

            </tbody>

        </table>



<?php

//    Gifts

    $gifts = array();

    $st = "SELECT name,sales.amount FROM sales CROSS JOIN orders_sale WHERE orders_sale.oid = $oid AND orders_sale.sid = sales.id AND sales.type = 'gift' ";

    $res = return_sel_sql($st);

    $k = 0;

    while($row = $res->fetch_assoc()){

        $k++;

        $gifts[] = [$row['name'],getVarFromDB('products','name','id',$row['amount'])];

    }

    if(sizeof($gifts)){

        ?>

        

<br><br>

        <h2 class="tac">هدایا</h2>

        <table class="tracking">

            <thead>

                <tr>

                    <th>ردیف</th>

                    <th>جشنواره</th>

                    <th>محصول</th>

                </tr>

            </thead>

            <tbody>

        <?php

        foreach($gifts as $k=>$g){

        ?>

                <tr>

                    <td>

                        

                    <?php echo $k+1;?>

                        

                    </td>

                    <td >

                        <?php echo $g[0];?>

                    </td>

                    <td>

                        <?php echo $g[1];?>

                    </td>

                </tr>

        

        <?php

        }

        ?>

            </tbody>

        </table>

        <?php

    }

?>

            

<?php

if(getVarFromDB($tb,"state","id",$_SESSION[$cf]->id) >= 2){

?>

<br>

        <a class="btn submit" href="send_detail.php" target="blank">چاپ مشخصات ارسال</a>

<?php

 }      

?>

    </div>  

</div>          

<div class="cut w100p"></div>

<div id="sect2" class="sect">

    <div class="middle container">

        <a class="btn submit" href="invoice.php" target="_blank">مشاهده فاکتور سفارش</a>

<?php

if($submit_text = getVarFromDB($atb,"submit_text","flag",$_SESSION[$cf]->state)){

?>

    </div>

</div>          

<div class="cut w100p"></div>

<div id="sect2" class="sect">

    <div class="middle container">

        <a class="btn submit" onclick="sub_show('edit','submit')"><?php echo $submit_text; ?></a>

<?php

}      

?>

</div>  

</div>          

<div class="cut w100p"></div>

    <div id="sect2" class="sect">

        <div class="middle container">

<?php

if(getVarFromDB($tb,"state","id",$_SESSION[$cf]->id) > 0){

?>

            <a class="btn submit red" onclick="if(confirm('آیا مطمئنید?'))sub_show('edit','return');">بازگشت سفارش به مرحله قبل</a>

<?php

}

?>

            <a class="btn submit red" onclick="if(confirm('آیا از حذف سفارش مطمئنید?'))sub_show('edit','delete');">حذف سفارش</a>

            

<script type="text/javascript">

    function date_field_toggle(shift){

        if(shift == "3"){

            document.getElementById("date_btns").classList.add("disabled");

            date_box_toggle('');

        }else{

            document.getElementById("date_btns").classList.remove("disabled");

            date_box_toggle('true');

            all_btns = document.getElementById("date_btns").getElementsByClassName("btn");

            for(i=0;i<all_btns.length;i++){

                all_btns[i].classList.add("hide");

            }

            

            btns = document.getElementById("date_btns").getElementsByClassName("shift_"+shift);

            for(i=0;i<btns.length;i++){

                btns[i].classList.remove("hide");

            }

        }

        return;

    }

    function date_box_toggle(dsbl_value){

        selects = document.getElementById("date_box").getElementsByTagName("select");

        for(i=1;i<4;i++){

            selects[i].disabled = dsbl_value;

        }

    }

    

    function fill_value(name,item){

        switch(name){

            case "cart_sale":

                price = 3;

                break;

            case "send_sale":

                price = 5;

                break;

        }

        price = document.getElementById("invoice_values").getElementsByTagName("input")[price].value;

        item.parentNode.getElementsByTagName("input")[0].value = price;

        return;

    }

</script>

<?php

        }

    }

?>

