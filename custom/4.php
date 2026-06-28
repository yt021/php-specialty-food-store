<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<main>
    <div class="content">
        <h1 class="tac">جعبه مخلوط انتخابی  به سبد خرید اضافه شد.</h1>

<?php
    if(isset($_SESSION["code_error"])){
        $error = $_SESSION["code_error"];
        unset($_SESSION["code_error"]);
    }
?>
        <div id="user_error" class="user_hint error <?php if(!isset($error))echo "hide"; ?>">
            <?php if(isset($error))echo substr($error,1); ?>
        </div>
        <div style="display: flex;justify-content:center;width:auto;">
        <a href="<?php echo $s; ?>" class="btn" title="Shop" style="margin:10px;">
            فروشگاه
        </a>
        <a onclick="sub_show('clear',0)" class="btn" title="Custom Box" style="margin:10px;">
            جعبه مخلوط انتخابی جدید
        </a>
        <a href="<?php echo $s; ?>cart/" class="btn" title="Cart" style="margin:10px;">
            مشاهده سبد خرید
        </a>
        </div>
</div>
</main>
<script src="<?php echo asset_url('js/custom.js'); ?>" type="text/javascript"></script>
<?php
        }
    }
?>


