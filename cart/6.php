<?php
    if(isset($indexed)){
        if($indexed == 1){

include_once $bu."modules/cart/cart_funcs.php";

$summary_oid = 0;
if(isset($_SESSION['oid'])){
    $summary_oid = (int)$_SESSION['oid'];
}

?>

<main>
    <div class="content checkout_flow_page">
        <div class="checkout_flow_header">
            <h1 class="checkout_flow_kicker">ثبت نهایی سفارش</h1>
            <p class="checkout_flow_title">پیش‌نمایش سفارش ثبت‌شده و دسترسی به ادامه فرایند پرداخت</p>
        </div>
        <?php
            if(isset($_SESSION["cart_notice"])){
                $error = $_SESSION["cart_notice"];
                unset($_SESSION["cart_notice"]);
            }
        ?>

            <div id="user_error" class="user_hint error <?php if(!isset($error))echo "hide"; ?>">
                <?php if(isset($error))echo substr($error,1); ?>
            </div>

        <div class="checkout_flow_split">
            <div class="checkout_flow_summary_col">
                <div class="checkout_flow_panel">
                    <?php
                        if($summary_oid > 0){
                            cart_render_paid_order_summary($summary_oid,array(
                                "checkout_scrollable" => true,
                                "checkout_mobile_cards" => true
                            ));
                        }
                    ?>
                </div>
            </div>
            <div class="checkout_flow_side_col is-sticky">
                <div class="checkout_flow_panel">
                    <h3 class="checkout_flow_panel_title">دسترسی سریع</h3>
                    <div class="checkout_flow_actions">
                        <a href="invoice.php" class="btn" target="_blank" title="مشاهده فاکتور">مشاهده فاکتور</a>
                        <a href="javascript:sub_show('clear','5');" class="btn" title="بازگشت به انتخاب روش پرداخت">بازگشت به انتخاب روش پرداخت</a>
                        <a href="<?php echo $s."cart/"; ?>" class="btn" title="بازگشت به سبد خرید">بازگشت به سبد خرید</a>
                    </div>
                </div>
                <?php
                    if(isset($_SESSION["a_logged"])){
                ?>
                <div class="checkout_flow_panel">
                    <h3 class="checkout_flow_panel_title">ثبت دستی اطلاعات پرداخت</h3>
                    <form class="info" action="<?php echo $URL; ?>" method="post">
                        <div class="form_item">
                            <label>کد رهگیری انتقال وجه</label>
                            <input class="tac" type="text" required name="pay_code">
                        </div>
                        <div class="form_item">
                            <label>4 رقم آخر شماره کارت</label>
                            <input class="tac" type="text" required name="pay_card">
                        </div>
                        <div class="checkout_nav_row">
                            <input name="submit" type="submit" class="btn middle" value="ثبت">
                        </div>
                    </form>
                </div>
                <?php
                    }
                ?>
            </div>
        </div>
    </div>
</main>
<?php
        }
    }
?>
