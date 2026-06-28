<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<main>
    <div class="content checkout_flow_page">
        <div class="checkout_flow_header">
            <h1 class="checkout_flow_kicker">اطلاعات گیرنده</h1>
            <p class="checkout_flow_title">نشانی و اطلاعات تحویل‌گیرنده سفارش را انتخاب یا ثبت کنید</p>
        </div>
<?php
    if(isset($_SESSION["address"]) && $_SESSION["address"]->data()["error"][0] != 0){
        $error["address"] = $_SESSION["address"]->data()["error"][1];
    }
?>
<?php
    if(isset($_SESSION["logged"])){
?>
<?php
    $addresses = db_get_all_address($_SESSION["logged"]->uid);
    if($addresses){
        echo '<div class="checkout_flow_panel"><h3 class="checkout_flow_panel_title">آدرس‌های ذخیره‌شده</h3><div class="checkout_flow_table_wrap"><table class="cart">
                <thead>
                    <tr>
                        <th>استان</th>
                        <th>شهر</th>
                        <th>آدرس</th>
                        <th>کد پستی</th>
                        <th>نام گیرنده</th>
                        <th>تلفن گیرنده</th>
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody id="cart_tbody">
                    ';
    
        foreach($addresses as $address){
            $data_a = $address->data();
            ?>
            <form action="<?php echo $URL; ?>" method="post">
            <tr>
                <td>
                    <?php echo $data_a["county"]; ?>
                    <input type="text" required name="county" value="<?php echo $data_a["county"] ?>" class="hide" >
                </td>
                <td>
                    <?php echo $data_a["city"]; ?>
                    <input type="text" required name="city" value="<?php echo $data_a["city"] ?>" class="hide" >
                </td>
                <td>
                    <?php echo $data_a["address"]; ?>
                    <input type="text" required name="address" value="<?php echo $data_a["address"] ?>" class="hide" >
                </td>
                <td>
                    <?php echo $data_a["post_code"]; ?>
                    <input type="text"  name="post_code" value="<?php echo $data_a["post_code"] ?>" class="hide" >
                </td>
                <td>
                    <?php echo $data_a["rec_name"]; ?>
                    <input type="text"  name="post_code" value="<?php echo $data_a["rec_name"] ?>" class="hide" >
                </td>
                <td>
                    <?php echo $data_a["rec_tel"]; ?>
                    <input type="text"  name="post_code" value="<?php echo $data_a["rec_tel"] ?>" class="hide" >
                </td>
                <td>
                    <input type="submit" class="btn middle" name="submit" value="انتخاب">
                </td>
            </tr>
            </form>
        <?php
        }   
        echo '      
                </tbody>
            </table></div></div>';
        ?>
            
        <div class="checkout_flow_panel">
        <div class="cart_for_phone">
        <?php
            $i = 0;
            foreach($addresses as $address){
                $data_a = $address->data();
                $i++
        ?>
            <div class="cfp_item address">
            <form action="<?php echo $URL; ?>" method="post">
                
                    استان: <?php echo $data_a["county"]; ?>
                    <input type="text" required name="county" value="<?php echo $data_a["county"] ?>" class="hide" >
                     - شهر: <?php echo $data_a["city"]; ?>
                    <input type="text" required name="city" value="<?php echo $data_a["city"] ?>" class="hide" >
               
                <!--<div class="cb"></div>-->
                <input type="submit" class="btn fl" name="submit" value="انتخاب">
               <br>
                    آدرس: <?php echo $data_a["address"]; ?>
                    <input type="text" required name="address" value="<?php echo $data_a["address"] ?>" class="hide" >
                     - کد پستی: <?php echo $data_a["post_code"]; ?>
                    <input type="text"  name="post_code" value="<?php echo $data_a["post_code"] ?>" class="hide" >
               <br>
                    نام و تلفن گیرنده: <?php echo $data_a["rec_name"]; ?>
                    <input type="text" name="rec_name" value="<?php echo $data_a["rec_name"] ?>" class="hide" >
                     -  <?php echo $data_a["rec_tel"]; ?>
                    <input type="text"  name="rec_tel" value="<?php echo $data_a["rec_tel"] ?>" class="hide" >
            </form>
            </div>
        <?php
            }
        ?>
        </div>
        </div>
        
<?php
    }
?>
<div id="user_error" class="user_hint error <?php if(!isset($error))echo "hide"; ?>">
    مشتری گرامی، خواهشمند است ایرادات زیر را برطرف نمایید:
    <ol>
        <?php
            if(isset($error["address"][0]) && $error["address"][0] === "County not set"){
                echo "
            <li>
                استان را از فهرست انتخاب کنید.
            </li>";
            }
            if(isset($error["address"][1]) && $error["address"][1] == "City not set"){
                echo "
            <li>
                شهر را پس از استان از فهرست انتخاب کنید.
            </li>";
            }
            if(isset($error["address"][2]) && $error["address"][2] == "Address not set"){
                echo "
            <li>
                آدرس دقیق را به فارسی و صورت صحیح وارد نمایید.
            </li>";
            }
            if(isset($error["address"][3]) && $error["address"][3] == "Postal code not set"){
                echo "
            <li>
                کد پستی را به صورت صحیح وارد نمایید. (اختیاری)
            </li>";
            }
        ?>
    </ol>
</div>
            <div class="checkout_flow_panel">
            <form class="info" action="<?php echo $URL; ?>" method="post" onsubmit="return validateAddress(this)">
                <div class="form_holder">
                <?php
                    include $bu."modules/cart/address_form.php";
                ?>
                </div>
                <div class="cb"></div>
                <div class="checkout_nav_row">
                    <a class="btn middle" onclick="sub_show('clear','<?php echo cart_step_back_level(); ?>')">
                        <?php echo cart_step_back_text(); ?>
                    </a>
                    <input name="submit" type="submit" class="btn middle" name="submit" value="<?php echo cart_step_next_text(); ?>">
                </div>
            </form>
            </div>
</div>
</main>
<?php
    }else{
?>

<div class="user_hint">
    مشتری گرامی خواهشمند است نکات زیر را رعایت فرمایید:
    <ol>
        <li>
            در صورتی که گیرنده فردی غیر از شما است، بخش‌های نام و تلفن گیرنده را تکمیل نمایید.
        </li>
        <li>
            عزیزانی که برای استان‌هایی غیر از استان تهران خرید می‌نمایند، با وارد کردن کد پستی در ارسال بهتر و سریع‌تر مرسوله به ما کمک می‌کنند.
        </li>
    </ol>
</div>

<div id="user_error" class="user_hint error <?php if(!isset($error))echo "hide"; ?>">
    مشتری گرامی، خواهشمند است ایرادات زیر را برطرف نمایید:
    <ol>
        <?php
            if(isset($error["address"][0]) && $error["address"][0] === "County not set"){
                echo "
            <li>
                استان را از فهرست انتخاب کنید.
            </li>";
            }
            if(isset($error["address"][1]) && $error["address"][1] == "City not set"){
                echo "
            <li>
                شهر را پس از استان از فهرست انتخاب کنید.
            </li>";
            }
            if(isset($error["address"][2]) && $error["address"][2] == "Address not set"){
                echo "
            <li>
                آدرس دقیق را به فارسی و صورت صحیح وارد نمایید.
            </li>";
            }
            if(isset($error["address"][3]) && $error["address"][3] == "Postal code not set"){
                echo "
            <li>
                کد پستی را به صورت صحیح وارد نمایید. (اختیاری)
            </li>";
            }
        ?>
    </ol>
</div>
<?php
    if(isset($_SESSION["address"]))$data["address"] = $_SESSION["address"]->data();
?>

            
            
            <div class="checkout_flow_panel">
            <form class="info" action="<?php echo $URL; ?>" method="post" onsubmit="return validateAddress(this)">
                <div class="form_holder">
                    <?php
                        include $bu."modules/cart/address_form.php";
                    ?>
                </div>
                <div class="cb"></div>
                <div class="checkout_nav_row">
                    <a class="btn middle" onclick="sub_show('clear','<?php echo cart_step_back_level(); ?>')">
                        <?php echo cart_step_back_text(); ?>
                    </a>
                    <input name="submit" type="submit" class="btn middle" name="submit" value="<?php echo cart_step_next_text(); ?>">
                </div>
            </form>
            </div>
</div>
</main>


<?php
    }
?>
<script src="<?php echo asset_url('js/cities.js'); ?>"></script>
<script src="<?php echo asset_url('js/selection.js'); ?>"></script>
<script type="text/javascript">

function validateAddress(form){
    county = form.getElementsByTagName('input')[0];
    city = form.getElementsByTagName('input')[1];
    address = form.getElementsByTagName('input')[2];
    te = 0;
    error = [0,0,0];
    if(!county.value){
        error[0] = 1;
        te++;
    }
    if(!city.value){
        error[1] = 1;
        te++;
    }
    if(!address.value){
        error[2] = 1;
        te++;
    }
    if(te != 0){
        show_error_address(form,error);
        return false;
    }
}
function show_error_address(form,error){
    user_error = document.getElementById("user_error");
    SH_E(user_error);
    ol = user_error.getElementsByTagName("ol")[0];
    ol.innerHTML = "";
    if(error[0] == 1){
        li = "<li>استان را از فهرست انتخاب کنید.</li>";
        ol.innerHTML = ol.innerHTML + li;
    }
    if(error[1] == 1){
        li = "<li>شهر را پس از استان از فهرست انتخاب کنید.</li>";
        ol.innerHTML = ol.innerHTML + li;
    }
    if(error[2] == 1){
        li = "<li>آدرس دقیق را به فارسی و صورت صحیح وارد نمایید.</li>";
        ol.innerHTML = ol.innerHTML + li;
    }
    
}
</script>
<?php
        }
    }
?>

