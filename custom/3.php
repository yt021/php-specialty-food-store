<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<main>
    <div class="content">
        <h1 class="tac">نهایی کردن جعبه</h1>
        <p class="tac">
            جعبه و محتوای انتخابی شما به شرح زیر است، در صورت تأیید، برای قرار گرفتن در سبد خرید، دکمه تأیید را فشار دهید.
        </p>
<?php
    if(isset($_SESSION["code_error"])){
        $error = $_SESSION["code_error"];
        unset($_SESSION["code_error"]);
    }
?>
        <div id="user_error" class="user_hint error <?php if(!isset($error))echo "hide"; ?>">
            <?php if(isset($error))echo substr($error,1); ?>
        </div>
        <div class="user_hint">
            <ol>
                <li>
                    جعبه: <?php echo getVarFromDB("products","name","id",$_SESSION["custom"]->pid); ?>
                </li>
                <li>
                    ظرفیت: <?php echo $_SESSION["custom"]->capacity; ?> باکس
                </li>
            </ol>
            
            
        </div>
        <div id="box_holder" class="content">
            <?php include ($bu."modules/custom/custom_table.php");  ?>
        </div>
        </div>
        <div class="cut"></div>
        <div class="content">
        <form action="<?php echo $URL; ?>" method="post" class="has_mid_btns">
            <input type="submit" class="btn middle w120p" name="submit" value="تأیید">
            <input type="submit" class="btn middle w120p" name="change_content" value="تغییر محتوا">
        </form>
</div>
</main>
<script src="<?php echo asset_url('js/custom.js'); ?>" type="text/javascript"></script>
<?php
        }
    }
?>


