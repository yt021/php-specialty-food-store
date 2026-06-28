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
            <p class="auth_screen_title">برای ساخت حساب کاربری، اطلاعات پایه و شماره همراه خود را ثبت کنید</p>
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
            <div id="user_hint" class="user_hint hide"></div>
            
            <form id = "signup_form" class="info tac" action="<?php echo $URL; ?>" method="post">
                <div id="tel" class="form_holder <?php if(isset($error) && $error[0] == "E")echo "hide"; ?> ">
                    <br><br>
                        <h4 class="tac"></h4><br>
                    <br><div class="cb"></div><br>
                    
                    <div class="form_item fr">
                        <label for="tel">تلفن همراه<span style="color:red;">*</span></label>
                        <input class="tac" type="text" required name="tel" <?php if(isset($_SESSION["tel"]))echo ' value="'.$_SESSION["tel"].'" '; ?>  placeholder="*********09">
                        <div class="cb"></div>
                    </div>
                    <div class="form_item fr ">
                        <label for="name">نام <span style="color:red;">*</span></label>
                        <input type="text" required name="name"  placeholder="نام و نام خانوادگی" <?php if(isset($data["name"]))echo ' value="'.$data["name"].'" '; ?>>
                    </div>
                    <div class="form_item fr">
                        <label for="email">رایانامه</label>
                        <input type="text" name="email"  placeholder="example@example.com" <?php if(isset($data["email"]))echo ' value="'.$data["email"].'" '; ?>>
                    </div>
                    <div class="form_item fr">
                        <div class="checkbox fr <?php if(isset($data["PP_state"]))echo '  icon-chkfl';else echo "icon-chk"; ?>" onclick="check_box(this)"></div>
                        <input type="checkbox" name="PP_state" <?php if(isset($data["PP_state"]))echo 'checked'; ?> value="yes" required class="hide">
                        <a href="<?php echo $s."pages/rules.php"; ?>">قوانین و مقررات</a> را مطالعه کرده‌ام
                    </div>
                    <br><br>
                    <div class="cut"></div>
                    <a onclick="send_create_code(this)" data-send-create-code="1" href="javascript:void(0);" class="btn middle" title="ثبت" >
                        ثبت
                    </a>
                </div>
                <div id="create_code" class="form_holder <?php if(!(isset($error) && $error[0] == "E"))echo "hide"; ?>">
                    <br><br>
                        <h4 class="tac">کد تایید خود را وارد نمایید.</h4><br>
                    <br><div class="cb"></div><br>
                    
                    <div class="form_item fr">
                        <input type="password" name="create_code">
                        <br>
                        <div id="timer_div" class="hide">
                            <div id="timer_holder">
                                زمان باقی‌مانده تا ارسال مجدد: <div id="timer">00:30</div>
                            </div>
                            <div class="cb"></div>
                            <a onclick="rep_create_code(this)" class="btn middle hide" id="recode" title="دریافت کد جدید" >
                                دریافت کد جدید
                            </a>
                        </div>
                        <div class="cb"></div>
                        <input name="submit" type="submit" class="btn middle" value="ساخت حساب">
                    </div>
                </div>
                <div class="cb"></div>
            </form>
        </div>

    </div>
</main>
<script src="<?php echo asset_url('js/selection.js'); ?>"></script>
<script type="text/javascript">
base_url = "<?php echo $s; ?>";
</script>
<script src="<?php echo asset_url('js/code.js'); ?>"></script>
<?php
        }
    }
?>
