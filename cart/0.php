<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<main>
    <div class="content checkout_flow_page">
        <div class="checkout_flow_header">
            <h1 class="checkout_flow_kicker">سبد خرید</h1>
            <p class="checkout_flow_title">مرور اقلام انتخاب‌شده و آماده‌سازی برای ادامه فرایند خرید</p>
        </div>
    <?php
        if($_SESSION["cart"]->number() > 0){
    ?>

        <div class="checkout_flow_panel">
            <h3 class="checkout_flow_panel_title">اقلام سبد خرید</h3>
            <div id="cart_holder" class="checkout_flow_table_wrap">
                <?php include $bu."modules/cart/cart_table_tbody.php"; ?>
            </div>
        </div>
        <div class="checkout_flow_panel">
            <div class="checkout_flow_actions">
                <form action="<?php echo $URL; ?>" method="POST">
                    <input name="empty" type="submit" class="btn" value="خالی کردن سبد خرید">
                </form>
                <a href="<?php echo $s; ?>" class="btn" title="Shop">ادامه خرید از فروشگاه</a>
                <form action="<?php echo $URL; ?>" method="POST">
                    <input name="submit" type="submit" class="btn" value="<?php echo cart_step_next_text(0); ?>">
                </form>
            </div>
        </div>

    <?php
    }else{ 
    ?>
    <div class="checkout_flow_panel">
        <p class="checkout_flow_panel_text tac">سبد خرید شما خالی است.</p>
        <div class="checkout_nav_row" style="margin-top:14px;">
            <a href="<?php echo $s; ?>" class="btn middle" title="Shop">فروشگاه</a>
        </div>
    </div>
    <?php
    }   
    ?>
</div>
</main>
<?php
        }
    }
?>


