<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(isset($_SESSION["logged"])){}else{
?>

<main>
    <div class="content checkout_flow_page">
        <div class="checkout_flow_header">
            <h1 class="checkout_flow_kicker">ورود یا تایید شماره</h1>
            <p class="checkout_flow_title">برای ادامه خرید، شماره همراه خود را تایید کنید یا با حساب موجود وارد شوید</p>
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
        <div id="user_hint" class="user_hint hide"></div>
        

        <div class="checkout_flow_panel">
        <form id = "signup_form" class="info tac" action="<?php echo $URL; ?>" method="post">
            <div id="tel" class="form_holder <?php if(isset($error) && $error[0] == "E")echo "hide"; ?> ">

                    <h4 class="tac">شماره تلفن همراه خود را وارد نمایید.</h4><br>
<!--                    <h4 class="tac">نام کاربری (شماره تلفن همراه) خود را وارد نمایید.</h4><br>-->
                <br><div class="cb"></div><br>
                
                <div class="form_item fr">
                    <input class="tac" type="text" required name="tel" <?php if(isset($_SESSION["tel"]))echo ' value="'.$_SESSION["tel"].'" '; ?>  placeholder="*********09">
                    <div class="cb"></div>
                    <?php
                        if(isset($_SESSION["a_logged"])){
                    ?>
                    <div class="checkout_nav_row">
                        <a class="btn middle" onclick="sub_show('clear','<?php echo cart_step_back_level(); ?>')">
                            <?php echo cart_step_back_text(); ?>
                        </a>
                        <input name="submit" type="submit" class="btn middle" name="submit" value="<?php echo cart_step_next_text(); ?>">
                    </div>
                    <?php
                        }else{
                    ?>
                    <div class="checkout_nav_row">
                        <a class="btn middle" onclick="sub_show('clear','<?php echo cart_step_back_level(); ?>')">
                            <?php echo cart_step_back_text(); ?>
                        </a>
                        <a onclick="send_create_code(this)" data-send-create-code="1" href="javascript:void(0);" class="btn middle" title="<?php echo cart_step_next_text(); ?>" >
                            <?php echo cart_step_next_text(); ?>
                        </a>
                    </div>
                    <?php
                        }
                    ?>
                </div>
            </div>
            <div id="create_code" class="form_holder <?php if(!(isset($error) && $error[0] == "E"))echo "hide"; ?>">

                <div class="cb"></div>
                    <h4 class="tac"><?php
                    echo "گذرواژه خود را وارد نمایید" ?></h4>
                <div class="cb"></div><br>
                
                <div class="form_item fr">
                    <input type="password" <?php if(!isset($_SESSION["a_logged"])) echo "required"; ?> name="create_code">

                    <div class="cb"></div>
                    <div class="checkout_nav_row">
                        <a class="btn middle" onclick="sub_show('clear','<?php echo cart_step_back_level(); ?>')">
                            <?php echo cart_step_back_text(); ?>
                        </a>
                        <input name="submit" type="submit" class="btn middle" value="<?php echo cart_step_next_text(); ?>">
                    </div>
                                        <br>
                    <div id="timer_div" class="">
                        <div id="timer_holder">
                            زمان باقی‌مانده تا دریافت گذرواژه جدید: <div id="timer">00:30</div>
                        </div>
                        <div class="cb"></div>
                        <a onclick="rep_create_code(this)" class="btn middle hide" id="recode" title="دریافت کد جدید" >
                            دریافت کد جدید
                        </a>
                    </div>
                </div>
            </div>
            <div class="cb"></div>
        </form>
        </div>
        
    </div>
</main>


<script type="text/javascript">
base_url = "<?php echo $s; ?>";
</script>
<script src="<?php echo asset_url('js/code.js'); ?>"></script>
<script type="text/javascript">
    <?php if(isset($error) && $error[0] == "E"){echo ";start_timer();SH_E(document.getElementById('timer_div'));";} ?>
</script>

<?php
    }
?>
<?php
        }
    }
?>
