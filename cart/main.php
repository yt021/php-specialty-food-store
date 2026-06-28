<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<main>
    <div class="content">
        <h1 class="tac">سبد خرید</h1>
    <?php
        if($_SESSION["cart"]->number() > 0){
    ?>
        <div id="cart_holder">
                    <?php include $bu."modules/cart/cart_table_tbody.php"; ?>
        </div>    
        <form action="user.php" method="POST" style="display: flex;justify-content:center;">
            <input name="empty" type="submit" class="btn" value="خالی کردن سبد خرید" style="margin:10px;">

            <a href="<?php echo $s; ?>" class="btn" title="Shop" style="margin:10px;">
                ادامه خرید از فروشگاه
            </a>
            <input type="submit" class="btn" value="تکمیل فرآیند خرید" style="margin:10px;">
        </form>

    <?php
    }else{ 
    ?>
    <p class="tac">سبد خرید شما خالی است.</p>
    <a href="<?php echo $s; ?>" class="btn middle" title="Shop">
        فروشگاه
    </a>
    <?php
    }   
    ?>
</div>
</main>
<?php
        }
    }
?>


