<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<main>
    <div class="content account_dash_page">
        <style type="text/css">
            .auth_screen_header{border:1px solid rgba(0,0,0,0.12);border-radius:18px;background:linear-gradient(135deg,#fff 0%,#f7f2ef 100%);padding:10px 14px;margin-bottom:12px;}
            .auth_screen_kicker{margin:0 0 2px 0;font-size:22px;line-height:1.15;font-weight:800;color:#4a2d22;text-align:right;}
            .auth_screen_title{margin:0;font-size:14px;line-height:1.35;font-weight:600;color:#8a6d5d;text-align:right;}
            .auth_screen_panel{border:1px solid rgba(0,0,0,0.12);border-radius:14px;background:#fff;padding:14px 16px;box-sizing:border-box;}
            .auth_screen_panel .title.tac{display:none;}
            @media (max-width:640px){.auth_screen_header{padding:10px 12px;}.auth_screen_kicker{font-size:18px;}.auth_screen_title{font-size:13px;}}
        </style>
        <div class="auth_screen_header">
            <h1 class="auth_screen_kicker">ثبت نام</h1>
            <p class="auth_screen_title">حساب کاربری شما ساخته شد و از اینجا می‌توانید وارد حساب شوید</p>
        </div>
        <div class="auth_screen_panel">
        <div class="title tac">
            <h2>ثبت نام</h2>
            <div class="cut"></div>
        </div>
        <div>
<?php
    if(isset($_SESSION["code_error"])){
        $error = $_SESSION["code_error"];
        unset($_SESSION["code_error"]);
    }
?>
            <div id="user_error" class="user_hint error <?php if(!isset($error))echo "hide"; ?>">
                <?php if(isset($error))echo substr($error,1); ?>
            </div>
            <div id="user_hint" class="user_hint">
                سلام<br>
                ضمن تشکر بابت عضویت در سایت، خواهشمند است نکات زیر را رعایت فرمایید:
                <ol>
                    <li>
                        از این پس برای استفاده از امکانات سایت، از این حساب کاربری استفاده نمایید.
                    </li>
                    <li>
                        گذرواژه پیش‌فرض، کد تایید ارسال شده از طریق پیامک است.
                    </li>
                    <li>
                        برای تغییر گذرواژه، می‌توانید از بخش مدیریت حساب در حساب کاربری خود استفاده نمایید.
                    </li>
                </ol>
                <br><br>پیشاپیش از همکاری شما سپاسگزاریم.
            </div>
            
            <a href="<?php echo $s."account/" ?>" class="btn middle" title="ورود به حساب کاربری" >
                ورود به حساب
            </a>
        </div>

    </div>
</main>

<?php
        }
    }
?>
