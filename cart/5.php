<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if($_SESSION["send_date"]->shift == 3 && !isset($_SESSION["shift3_excess"])){
        $out_of_price = getVarFromDB('sd_shifts','price','id',3);
?>
    <main>
        <div class="content">
            <p class="tac">
                 در ارسال خارج از نوبت، حداقل هزینه ارسال <?php echo price_sep($out_of_price); ?> تومان است. 
                 <b>
                 مازاد هزینه پیک نیز بر عهده گیرنده است و در زمان تحویل تسویه خواهد شد.</b>
                
            </p>
            <div style="display: flex;justify-content:center;">
                <a class="btn  " onclick="sub_show('excess_submit','agree')" style="margin:10px;">
                    <?php echo cart_step_next_text(); ?>
                </a>
                <a class="btn " onclick="sub_show('clear','<?php echo cart_step_back_level(); ?>')" style="margin:10px;"> 
                    <?php echo cart_step_back_text(); ?>
                </a>
            </div>
            </div>
    </main>
<?php
    }else{
    
    if($_SESSION["address"]->data()["county"] != "شهر تهران"){

?>
<br>
<div class="content">
<div class="user_hint tac">
    مشتری گرامی خواهشمند است در نظر داشته باشید که سفارش‌ها <b>3 تا 5 روز کاری پس از ثبت سفارش</b> ارسال می‌گردند.
</div>
</div>
<?php
        
    }
    
    
    unset($_SESSION["sales"]);
    $admin_state = 1;
    if(isset($_SESSION["a_logged"]))$admin_state = 0;
    if(isset($_SESSION["logged"])){$mode = "new";$uid = $_SESSION["logged"]->uid;}
    else{$mode = "first";$uid=0;}
    
    
    //    Send Price Calculation
    if(isset($_SESSION["send_date"])){
        $_SESSION["send_cost"] = getVarFromDB("sd_shifts","price","id",$_SESSION["send_date"]->shift);
        // if($_SESSION["address"]->data()["county"] == "استان تهران" && $_SESSION["send_date"]->shift == 2)$_SESSION["send_cost"] = 14000;
    }else{
        $_SESSION["send_cost"] =  $_SESSION["address"]->data()["cost"];
        if($_SESSION["address"]->data()["county"] == "استان تهران")$_SESSION["send_date"] = new send_date('post',2,0);
    }
    if($_SESSION["address"]->data()["county"] == "استان تهران")
    {
        $_SESSION["send_cost"] =  $_SESSION["address"]->data()["cost"];
        $_SESSION["send_date"] = new send_date('post',2,0);
    }
    
    $send_applicable = true;
    if($_SESSION["send_date"]->shift == 3){
    $send_applicable = false;}
    
    $_SESSION["sales"]= new sales($mode,$uid,$_SESSION["cart"]->price(),$_SESSION["send_cost"],$admin_state,$send_applicable);
    
    // if(isset($_SESSION['a_logged'])){var_dump($_SESSION["sales"]);echo "<br>";var_dump($_SESSION['lksr']);unset($_SESSION['lksr']);echo "<br>";var_dump($_SESSION['s_lk']);}
    // var_dump($_SESSION["sales"]);
    $_SESSION["cart_pure"] = (int)max($_SESSION["cart"]->price()-$_SESSION["sales"]->cart_sale(),0);
    

    
    $_SESSION["total_price"] = $_SESSION["cart"]->price()+$_SESSION["send_cost"];
    $_SESSION["pay_price"] = (int)max($_SESSION["total_price"] - $_SESSION["sales"]->total_sale(),0);
    
    $_SESSION["sale_total"] = (int)min($_SESSION["sales"]->total_sale(),$_SESSION["total_price"]);
    $send_cost_raw = is_numeric($_SESSION["send_cost"]) ? (int)$_SESSION["send_cost"] : 0;
    
    if($_SESSION["sales"]->send_sale())$_SESSION["send_cost"] = "ارسال رایگان";
    $send_cost_display = $_SESSION["send_cost"];
    
?>

<main>
    <div class="content checkout_flow_page">
        <div class="checkout_flow_header">
            <h1 class="checkout_flow_kicker">پرداخت سفارش</h1>
            <p class="checkout_flow_title">مرور نهایی سبد خرید، کد تخفیف و انتخاب روش پرداخت</p>
        </div>
        <?php
            if(isset($_SESSION["cart_notice"])){
                $error = $_SESSION["cart_notice"];
                unset($_SESSION["cart_notice"]);
            }
            $discount_code_notice = "";
            if(isset($_SESSION["discount_code_notice"])){
                $discount_code_notice = (string)$_SESSION["discount_code_notice"];
                unset($_SESSION["discount_code_notice"]);
            }
        ?>
        
            <div id="user_error" class="user_hint error <?php if(!isset($error))echo "hide"; ?>">
                <?php if(isset($error))echo substr($error,1); ?>
            </div>
        <?php
            $lksr_code = isset($_SESSION['lksr']) ? (string)$_SESSION['lksr'] : "";
            $shipping_note = "";
            $shipping_method_text = "";
            $is_tehran_region = false;
            if(isset($_SESSION["address"]) && is_object($_SESSION["address"])){
                $address_data = $_SESSION["address"]->data();
                $county_name = trim((string)($address_data["county"] ?? ""));
                $city_name = trim((string)($address_data["city"] ?? ""));
                $is_tehran_region = ($county_name === "شهر تهران" || $county_name === "استان تهران" || $city_name === "تهران");
            }
            if(isset($_SESSION["send_date"]) && is_object($_SESSION["send_date"]) && isset($_SESSION["send_date"]->shift)){
                $shift_id = (int)$_SESSION["send_date"]->shift;
                if($shift_id > 0 && var_exist($shift_id,"sd_shifts","id")){
                    $shift_name = trim((string)getVarFromDB("sd_shifts","name","id",$shift_id));
                    $shift_start = trim((string)getVarFromDB("sd_shifts","start_hour","id",$shift_id));
                    $shift_end = trim((string)getVarFromDB("sd_shifts","end_hour","id",$shift_id));
                    $shipping_method_text = $shift_name;
                    if($is_tehran_region){
                        $shipping_method_text = "پیک تهران";
                        if($shift_start !== "" && $shift_end !== ""){
                            $shipping_method_text .= " - ساعت ".$shift_start." تا ".$shift_end;
                        }
                    }
                }
            }
            if(isset($_SESSION["address"]) && is_object($_SESSION["address"])){
                $shipping_note = "آدرس: ".$_SESSION["address"]->full_address();
            }
        ?>
        <div class="checkout_flow_split checkout_payment_layout">
            <div class="checkout_flow_summary_col checkout_payment_summary_col">
        <?php
            cart_render_checkout_payment_summary(
                $_SESSION["cart"],
                $_SESSION["sales"],
                $send_cost_display,
                $send_cost_raw,
                (int)$_SESSION["pay_price"],
                (int)$_SESSION["sale_total"],
                $lksr_code,
                $shipping_note,
                $shipping_method_text
            );
        ?>

        <div class="checkout_flow_panel">
            <h3 class="checkout_flow_panel_title">بازبینی مرحله‌های قبلی</h3>
            <div class="checkout_change_row">
                <a class="btn cart_show checkout_change_btn" onclick="sub_show('clear','0')">مشاهده و تغییر سبد خرید</a>
                <a class="btn cart_show checkout_change_btn" onclick="sub_show('clear','3')">مشاهده و تغییر زمان/هزینه ارسال</a>
            </div>
        </div>
        <style type="text/css">
            .checkout_payment_layout{
                align-items:start;
            }
            .checkout_payment_summary_col,
            .checkout_payment_action_col{
                min-width:0;
            }
            .checkout_payment_summary_col table.cart{
                width:100%;
                margin:0;
            }
            .checkout_payment_summary_col .checkout_summary_shell{
                border:1px solid rgba(0,0,0,0.12);
                border-radius:12px;
                padding:8px;
                background:#fff;
            }
            .checkout_payment_summary_col h3.tac{
                margin:0 0 8px 0;
                font-size:16px;
                line-height:1.3;
                font-weight:700;
            }
            .checkout_payment_summary_col table.cart thead th{
                padding-top:8px;
                padding-bottom:8px;
                font-size:12px;
                line-height:1.3;
                font-weight:700;
            }
            .checkout_payment_summary_col .checkout_summary_scroll_wrap{
                max-height:min(56vh,560px);
                overflow:auto;
            }
            .checkout_payment_summary_col .checkout_summary_final_row td{
                position:sticky;
                bottom:0;
                background:#f4f0ee;
                z-index:1;
            }
            .checkout_change_row{
                display:flex;
                justify-content:center;
                align-items:stretch;
                gap:12px;
                margin:0;
            }
            .checkout_change_row .checkout_change_btn{
                width:280px;
                max-width:46%;
                margin:0 !important;
                text-align:center;
                box-sizing:border-box;
            }
            .checkout_discount_form{
                width:100% !important;
                max-width:none !important;
                display:block !important;
                margin:0 !important;
            }
            .discount_inline_row{
                display:flex;
                align-items:center;
                justify-content:flex-end;
                gap:8px;
                flex-wrap:wrap;
            }
            .checkout_discount_form label{
                width:auto;
                height:auto;
                line-height:1.5;
                margin:0;
            }
            .checkout_discount_form #lksr{
                width:170px;
                margin:0 !important;
                height:34px;
            }
            .checkout_discount_form .btn.middle{
                min-width:126px;
                margin:0 !important;
                padding:8px 12px;
            }
            .discount_code_notice{
                margin:10px 0 0 0;
                max-width:none;
                padding:8px 12px;
                font-size:13px;
                line-height:1.5;
                text-align:right;
            }
            .checkout_payment_summary_col .checkout_mobile_summary{
                display:none;
                min-width:0;
            }
            .checkout_payment_summary_col > .checkout_flow_panel{
                display:none;
            }
            @media (min-width:1100px){
                .checkout_payment_action_col{
                    position:sticky;
                    top:16px;
                    align-self:stretch;
                    height:min(56vh,560px);
                    min-height:420px;
                    overflow:auto;
                }
            }
            @media (max-width:900px){
                .checkout_payment_summary_col .checkout_summary_shell{
                    display:none;
                }
                .checkout_payment_summary_col .checkout_mobile_summary{
                    display:flex;
                    margin-top:6px;
                }
                .checkout_payment_summary_col .checkout_mobile_summary .cfp_item{
                    width:100%;
                    margin:6px 0;
                    max-width:none;
                    min-width:0;
                    height:auto;
                    box-sizing:border-box;
                }
            }
            @media (max-width:760px){
                .checkout_change_row{
                    flex-direction:column;
                    align-items:center;
                    gap:8px;
                }
                .checkout_change_row .checkout_change_btn{
                    width:100%;
                    max-width:100%;
                }
                .checkout_discount_form #lksr{
                    width:min(240px,100%);
                }
                .checkout_discount_form .btn.middle{
                    min-width:0;
                    width:100%;
                }
            }
        </style>
            </div>
            <div class="checkout_flow_side_col checkout_payment_action_col is-sticky">
<?php
    $payment_method_selected = "zarinpal";
    if(isset($_SESSION["payment_method"]) && check_value("text",$_SESSION["payment_method"])) {
        $payment_method_selected = $_SESSION["payment_method"];
    }

    $snappay_show = false;
    $snappay_title = "";
    $snappay_desc = "";
    if(file_exists($bu."modules/snappay/snappay_config.php")){
        require_once($bu."modules/snappay/snappay_client.php");
        if(function_exists('snappay_checkout_enabled') && snappay_checkout_enabled()){
            $amount_irr = snappay_amount_to_irr((int)$_SESSION["pay_price"]);
            $eligible_res = snappay_api_eligible($amount_irr);
            if(isset($eligible_res["ok"]) && $eligible_res["ok"]){
                $snappay_show = (bool)($eligible_res["json"]["response"]["eligible"] ?? false);
                $snappay_title = (string)($eligible_res["json"]["response"]["title_message"] ?? "");
                $snappay_desc = (string)($eligible_res["json"]["response"]["description"] ?? "");
            }
        }
    }

    if(!$snappay_show || $payment_method_selected !== "snappay"){
        $payment_method_selected = "zarinpal";
    }
?>

        <?php $lksr_value = isset($_SESSION['lksr']) ? (string)$_SESSION['lksr'] : ""; ?>
        <div class="checkout_flow_panel checkout_flow_note">
            <h3 class="checkout_flow_panel_title">کد تخفیف</h3>
            <form id="discount_code_form" class="info checkout_discount_form" action="<?php echo $URL; ?>" method="post">
                <div class="discount_inline_row">
                    <label for="lksr">کد تخفیف:</label>
                    <input id="lksr" type="text" name="lksr" value="<?php echo htmlspecialchars($lksr_value, ENT_QUOTES, 'UTF-8'); ?>" dir="ltr" autocomplete="off">
                    <input id="lksr_apply_btn" name="lksr_s" type="submit" class="btn middle" value="اعمال">
                    <?php if(isset($_SESSION['lksr'])){ ?>
                    <input name="lksr_clear" type="submit" class="btn middle" value="حذف">
                    <?php } ?>
                </div>
                <?php if($discount_code_notice !== ""){ ?>
                <?php
                    $discount_notice_type = substr($discount_code_notice,0,1);
                    $discount_notice_text = ($discount_notice_type === "E" || $discount_notice_type === "D")
                        ? substr($discount_code_notice,1)
                        : $discount_code_notice;
                ?>
                <div class="user_hint discount_code_notice <?php if($discount_notice_type === "E") echo "error"; ?>">
                    <?php echo htmlspecialchars((string)$discount_notice_text,ENT_QUOTES,'UTF-8'); ?>
                </div>
                <?php } ?>
            </form>
        </div>

        <div class="checkout_flow_panel">
            <h3 class="checkout_flow_panel_title">انتخاب روش پرداخت</h3>
            <form class="info pay_gate_form" action="<?php echo $URL; ?>" method="post">
            <style type="text/css">
                .pay_gate_form{
                    width:100% !important;
                    max-width:none !important;
                    margin:0 !important;
                    box-sizing:border-box;
                }
                .pay_methods{
                    width:100%;
                    max-width:100%;
                    margin:0 0 14px 0;
                    display:grid;
                    grid-template-columns:1fr;
                    gap:12px;
                }
                .pay_methods.single{
                    max-width:520px;
                }
                .pay_option{
                    display:block;
                    width:100%;
                    padding:0;
                    border:none;
                    background:none;
                    text-align:inherit;
                    cursor:pointer;
                }
                .pay_methods.single .pay_option{
                    width:100%;
                    max-width:none;
                }
                .pay_card{
                    display:flex;
                    flex-direction:row-reverse;
                    justify-content:flex-start;
                    align-items:center;
                    gap:12px;
                    padding:12px 16px;
                    border:1px solid #D2D4DD;
                    border-radius:20px;
                    background:#FFFFFF;
                    cursor:pointer;
                    user-select:none;
                    width:100%;
                    min-height:72px;
                    box-sizing:border-box;
                    transition:border-color 160ms ease, box-shadow 160ms ease, background-color 160ms ease;
                }
                .pay_card .txt{
                    flex:1 1 auto;
                    min-width:0;
                    direction:rtl;
                    text-align:left;
                    display:flex;
                    flex-direction:column;
                    align-items:flex-start;
                    justify-content:center;
                    gap:2px;
                }
                .pay_card .title{
                    margin:0;
                    font-weight:700;
                    font-size:15px;
                    line-height:1.5;
                    color:#1A1C23;
                    white-space:normal;
                    word-break:break-word;
                }
                .pay_card .desc{
                    margin-top:2px;
                    font-weight:400;
                    font-size:13px;
                    line-height:1.45;
                    color:#616475;
                    white-space:normal;
                    word-break:break-word;
                }
                .pay_card .right{
                    display:flex;
                    align-items:center;
                    justify-content:center;
                    flex-shrink:0;
                }
                .pay_logo{
                    width:48px;
                    height:48px;
                    display:block;
                }
                .pay_option:hover .pay_card{
                    border-color:#008EFA;
                }
                .pay_option:focus-visible{
                    outline:none;
                }
                .pay_option:focus-visible .pay_card{
                    border-color:#008EFA;
                    box-shadow:0 0 0 3px rgba(0,142,250,0.18);
                }
                .pay_option.zarinpal .right{
                    display:none;
                }
                .pay_option.zarinpal .title{
                    font-size:14px;
                    line-height:1.45;
                }
                .pay_option.zarinpal .desc{
                    font-size:12px;
                }
                .pay_option.snappay .pay_card{
                    border-color:#D2D4DD;
                    background:#FFFFFF;
                    gap:10px;
                }
                .pay_option.snappay .title{
                    font-size:14px;
                    line-height:1.45;
                }
                .pay_option.snappay .desc{
                    font-size:12px;
                }
                .pay_gate_form .btn.middle{
                    display:table !important;
                    margin:14px auto 0 auto !important;
                    clear:both;
                }
                @media (min-width:1024px){
                    .pay_option.zarinpal .pay_card{
                        min-height:76px;
                        padding:13px 18px;
                        gap:12px;
                    }
                    .pay_option.zarinpal .title{
                        font-size:15px;
                    }
                    .pay_option.zarinpal .desc{
                        font-size:13px;
                    }
                    .pay_option.snappay .pay_card{
                        min-height:76px;
                        padding:13px 18px;
                        gap:12px;
                    }
                    .pay_option.snappay .title{
                        font-size:15px;
                    }
                    .pay_option.snappay .desc{
                        font-size:13px;
                    }
                    .pay_logo{
                        width:52px;
                        height:52px;
                    }
                }
                @media (min-width:1280px){
                    .pay_option.zarinpal .pay_card,
                    .pay_option.snappay .pay_card{
                        min-height:60px;
                        padding:10px 16px;
                    }
                }
                @media (max-width:768px){
                    .pay_methods{
                        max-width:100%;
                    }
                    .pay_card{
                        min-height:68px;
                        padding:10px 12px;
                        gap:14px;
                        border-radius:18px;
                    }
                    .pay_logo{
                        width:44px;
                        height:44px;
                    }
                    .pay_option.snappay .title{
                        font-size:13px;
                    }
                    .pay_option.snappay .desc{
                        font-size:11px;
                    }
                    .pay_option.zarinpal .title{
                        font-size:13px;
                    }
                    .pay_option.zarinpal .desc{
                        font-size:11px;
                    }
                }
                @media (max-width:480px){
                    .pay_methods{
                        gap:10px;
                    }
                    .pay_card{
                        gap:10px;
                        min-height:64px;
                        padding:10px 10px;
                        border-radius:16px;
                    }
                    .pay_logo{
                        width:40px;
                        height:40px;
                    }
                    .pay_option.snappay .title{
                        font-size:12px;
                    }
                    .pay_option.snappay .desc{
                        font-size:10px;
                    }
                    .pay_option.zarinpal .title{
                        font-size:12px;
                    }
                    .pay_option.zarinpal .desc{
                        font-size:10px;
                    }
                }
                @media (max-width:360px){
                    .pay_methods{
                        max-width:100%;
                    }
                    .pay_card{
                        min-height:60px;
                    }
                    .pay_logo{
                        width:34px;
                        height:34px;
                    }
                    .pay_option.snappay .title{
                        font-size:11px;
                    }
                    .pay_option.snappay .desc{
                        font-size:9px;
                    }
                    .pay_option.zarinpal .title{
                        font-size:11px;
                    }
                    .pay_option.zarinpal .desc{
                        font-size:9px;
                    }
                }
            </style>

            <input type="hidden" name="submit" value="پرداخت از درگاه">
            <div class="pay_methods<?php if(!$snappay_show) echo " single"; ?>">
                <?php if($snappay_show){ ?>
                    <button type="submit" class="pay_option snappay" name="payment_method" value="snappay">
                        <div class="pay_card snappay">
                            <div class="txt">
                                <div class="title"><?php echo htmlspecialchars($snappay_title, ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="desc"><?php echo htmlspecialchars($snappay_desc, ENT_QUOTES, 'UTF-8'); ?></div>
                            </div>
                            <div class="right">
                                <picture>
                                    <source media="(max-width: 360px)" srcset="<?php echo $s; ?>img/Snapp!-Pay-Logotype-Mobile.svg">
                                    <img class="pay_logo" src="<?php echo $s; ?>img/Snapp!-Pay-Logotype-Desktop &amp; Tablet.svg" alt="SnappPay">
                                </picture>
                            </div>
                        </div>
                    </button>
                <?php } ?>

                <button type="submit" class="pay_option zarinpal" name="payment_method" value="zarinpal">
                    <div class="pay_card">
                        <div class="txt">
                            <div class="title">پرداخت آنلاین</div>
                            <div class="desc">پرداخت امن و فوری از طریق درگاه بانکی</div>
                        </div>
                        <div class="right"></div>
                    </div>
                </button>
            </div>

            <?php if(isset($_SESSION["a_logged"])){ ?>
                <div style="text-align:center;margin-top:10px;">
                    <button type="submit" name="admin_manual_pay" value="1" class="btn middle">
                        ثبت دستی پرداخت (ادمین)
                    </button>
                </div>
            <?php } ?>
            </form>
        </div>
        <div class="checkout_flow_panel">
            <h3 class="checkout_flow_panel_title">بازبینی مرحله‌های قبلی</h3>
            <div class="checkout_change_row">
                <a class="btn cart_show checkout_change_btn" onclick="sub_show('clear','0')">مشاهده و تغییر سبد خرید</a>
                <a class="btn cart_show checkout_change_btn" onclick="sub_show('clear','3')">مشاهده و تغییر زمان/هزینه ارسال</a>
            </div>
        </div>
            </div>
        </div>
    </div>
</main>
<?php } ?>
<?php
        }
    }
?>
