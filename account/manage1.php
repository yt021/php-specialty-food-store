<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<main>
    <div class="content account_dash_page">
        <div class="account_dash_header">
            <h1 class="account_dash_kicker" onclick="sub_show('clear',0);">حساب من</h1>
            <p class="account_dash_title">مدیریت حساب</p>
        </div>
        <div class="account_dash_forms">
            <div class="account_dash_panel">
                <h3 class="account_dash_panel_title">اطلاعات تماس</h3>
                <form class="info" action="<?php echo $URL; ?>" method="post">
                <div class="form_holder">
                    <div class="form_item fr">
                        <label for="email">رایانامه</label>
                        <input type="text" required name="email" <?php if(isset($_SESSION["user"]->data()["email"]))echo ' value="'.$_SESSION["user"]->data()["email"].'" '; ?> placeholder="example@example.com">
                    </div>
                </div>
                <div class="cb"></div>
                <div class="account_dash_form_actions">
                    <input type="submit" class="btn middle" name="edit" value="ثبت تغییر">
                </div>
            </form>  
            </div>
            <div class="account_dash_panel">
                <h3 class="account_dash_panel_title">تغییر گذرواژه</h3>
                <form class="info" action="<?php echo $URL; ?>" method="post">
                <div class="form_holder">
                    <div class="form_item">
                        <label>گذرواژه کنونی:</label>
                        <input name="old_pass" required type="password" value="">
                    </div>
                    <div class="form_item">
                        <label>گذرواژه جدید:</label>
                        <input name="new_pass" required type="password">
                    </div>
                    <div class="form_item">
                        <label>تکرار گذرواژه:</label>
                        <input name="cnf_new_pass" required type="password">
                    </div>
                </div>
                <div class="cb"></div>
                <div class="account_dash_form_actions">
                    <input type="submit" class="btn middle" name="edit" value="ثبت تغییر">
                </div>
            </form>  
            </div>
        </div>
        <div class="account_dash_back">
            <a onclick="sub_show('clear',0);" class="btn">بازگشت</a>
        </div>
    </div>
</main>

    
<?php
        }
    }
?>
