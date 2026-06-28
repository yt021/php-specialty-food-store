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
            <h1 class="checkout_flow_kicker">سفارش ثبت شد</h1>
            <p class="checkout_flow_title">مرور نتیجه پرداخت و دسترسی سریع به پیگیری سفارش</p>
        </div>

        <?php
            if(isset($_SESSION["cart_notice"])){
                $error = $_SESSION["cart_notice"];
                unset($_SESSION["cart_notice"]);
            }
        ?>

            <div id="user_error" class="user_hint <?php if(!isset($error))echo "hide"; ?>">
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
                    <h3 class="checkout_flow_panel_title">اقدام بعدی</h3>
                    <div class="checkout_flow_actions">
                        <a href="invoice.php" class="btn" target="_blank" title="مشاهده فاکتور">مشاهده فاکتور</a>
                        <a href="<?php echo $s."account/"; ?>" class="btn" title="پیگیری سفارش از بخش حساب کاربری">پیگیری سفارش از بخش حساب کاربری</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php
        }
    }
?>
