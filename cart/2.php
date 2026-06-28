<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(isset($_SESSION["logged"])){}else{
?><br><br>
<main>
    <div class="content checkout_flow_page">
        <div class="checkout_flow_header">
            <h1 class="checkout_flow_kicker">اطلاعات خریدار</h1>
            <p class="checkout_flow_title">اطلاعات پایه حساب خریدار را برای ادامه ثبت سفارش تکمیل یا بازبینی کنید</p>
        </div>

<div class="checkout_flow_panel">
<div class="user_hint">
    سلام مشتری گرامی،<br>
    خواهشمند است نکات زیر را در فرآیند خرید رعایت فرمایید:
    <ol>
        <li>
            نام و نام خانوادگی خود را وارد کنید.
        </li>
        <li>
            ورود رایانامه اختیاری است.
        </li>
        <li>
           قوانین و مقررات خرید از فروشگاه میوه خشک آبان را مطالعه و تایید کنید. 
        </li>
    </ol>
</div>
</div>
<?php
    if(isset($_SESSION["user"]) && $_SESSION["user"]->data()["error"][0] != 0 && sizeof($_SESSION["user"]->data()["error"][1]) > 0){
        $error = $_SESSION["user"]->data()["error"][1];
        $name = isset($_SESSION["user"]->data()["name"])?$_SESSION["user"]->data()["name"]:" ";
        $tel =isset($_SESSION["user"]->data()["tel"])?$_SESSION["user"]->data()["tel"]:" ";
?>
<div class="user_hint error">
    مشتری گرامی، خواهشمند است ایرادات زیر را برطرف نمایید:
    <ol>
        <?php
            if(isset($error["name"]) && $error["name"] == "not set"){
                echo "
                <li>
                    نام خود را به فارسی و صورت صحیح وارد کنید.
                </li>";
                $error_detail = "name not set";
                $st2 = "INSERT INTO user_register_errors (tel,name,error_detail) VALUES (?,?,?)";
                $st2 = $mysqli->prepare($st2);
                $st2->bind_param('sss',$tel,$name,$error_detail);
                $st2->execute();
            }
            if(isset($error["PP_state"]) && $error["PP_state"] == "not set"){
                echo "
                <li>
                    قوانین و مقررات را مطالعه و تایید کنید.
                </li>";
                $error_detail = "PP not set";
                $st2 = "INSERT INTO user_register_errors (tel,name,error_detail) VALUES (?,?,?)";
                $st2 = $mysqli->prepare($st2);
                $st2->bind_param('sss',$tel,$name,$error_detail);
                $st2->execute();
            }
            if(isset($error["create_code"]) && $error["create_code"] == "not set"){
                $st2 = "INSERT INTO user_register_errors (tel,name,error_detail) VALUES (?,?,?)";
                $st2 = $mysqli->prepare($st2);
                $st2->bind_param('sss',$tel,$name,"cc not set");
                $st2->execute();
            }
            if(isset($error["tel"]) && $error["tel"] == "not set"){
                $st2 = "INSERT INTO user_register_errors (tel,name,error_detail) VALUES (?,?,?)";
                $st2 = $mysqli->prepare($st2);
                $st2->bind_param('sss',$tel,$name,"tel not set");
                $st2->execute();
            }
        ?>
    </ol>
</div>
<?php
    }
    if(isset($_SESSION["user"]))$data = $_SESSION["user"]->data();
?>
            <div class="checkout_flow_panel">
            <form id = "signup_form" class="info" action="<?php echo $URL; ?>" method="post">

                <div id="data" class="form_holder">
                    <div class="form_item fr ">
                        <label for="name">نام <span style="color:red;">*</span></label>
                        <input type="text" required name="name"  placeholder="نام و نام خانوادگی" <?php if(isset($data["name"]))echo ' value="'.$data["name"].'" '; ?>>
                    </div>
                    <div class="form_item fr">
<?php
    if(isset($_SESSION["a_logged"])){
        $label = "شناسه شبکه اجتماعی";
        $place = "شناسه شبکه اجتماعی نظیر اینستاگرام یا تلگرام";
    }else{
        $label = 'رایانامه';
        $place = "example@example.com";
    }
?>
                        <label for="email"><?php echo $label; ?></label>
                        <input type="text" name="email"  placeholder="<?php echo $place; ?>" <?php if(isset($data["email"]))echo ' value="'.$data["email"].'" '; ?>>
                    </div>
                    <div class="form_item fr">
                        <div class="checkbox fr <?php if(isset($data["PP_state"]))echo '  icon-chkfl';else echo "icon-chk"; ?>" onclick="check_box(this)"></div>
                        <input type="checkbox" name="PP_state" <?php if(isset($data["PP_state"]))echo 'checked'; ?> value="yes" class="hide">
                        <a href="<?php echo $s."pages/rules.php"; ?>">قوانین و مقررات</a> را مطالعه کرده‌ام
                    </div>
                    <div class="cb"></div>
                    <div class="checkout_nav_row">
                        <a class="btn middle" onclick="sub_show('clear','<?php echo cart_step_back_level(); ?>')">
                            <?php echo cart_step_back_text(); ?>
                        </a>
                        <input name="submit" type="submit" class="btn middle" name="submit" value="<?php echo cart_step_next_text(); ?>">
                    </div>
                </div>
                <div class="cb"></div>
                
            </form>
            </div>
</div>
</main>


<script src="<?php echo asset_url('js/selection.js'); ?>"></script>
<?php
    }
?>
<?php
        }
    }
?>
