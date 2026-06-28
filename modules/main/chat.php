<div id="chat">
<?php
    $state = 1;
    if(isset($_SESSION['logged']) || isset($_SESSION['chat_id'])){
        $state = 0;
    }
    echo "<script>var state = $state;</script>";
?>
    <div id="chat_open" class="vibrate" onclick="open_chat()">
        <picture>
            <source srcset="<?php echo asset_url('img/chat_logo_.webp'); ?>" type="image/webp">
            <img src="<?php echo asset_url('img/chat_logo_.png'); ?>" />        
        </picture>
    </div>
    <div id="chat_box" class="hide">
        <div class="top_bar"><span class="icon-x" onclick="close_chat()"></span> پشتیبانی آنلاین</div>
<?php if($state){?>
    <div id="login_holder">
        <div class="login">
            شما از این طریق می‌توانید سوالات خود را بپرسید و به سرعت پاسخ‌ خود را دریافت کنید.

        </div>
        <div class="login">
            برای شروع نام خود را وارد نمایید.
        </div>
        <input id="logname" class="logname" name="name" type="text" placeholder="نام خود را وارد نمایید">
        <a class="social" onclick="start_chat()">
            <span class="icon-a"></span>
        </a>
    </div>
<?php }else{?>
        <ul id="chat_holder" class="chat_holder">
	    </ul>
        <div class="chat_input">
            <input type="text" name="chat" placeholder="پیام خود را بنویسید">
            <span class="icon-a" onclick="send_pm()"></span>
        </div>
<?php } ?>
    </div>
</div>

<script src="<?php echo asset_url('js/chat.js'); ?>" type="text/javascript" async></script>
