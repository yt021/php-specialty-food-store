<?php
if(isset($_SESSION["logged"]) && function_exists('cart_restore_draft_for_logged_user')){
    cart_restore_draft_for_logged_user();
    cart_sync_draft_for_logged_user();
}
if(!isset($_SESSION["cart"]) || !is_object($_SESSION["cart"])){
    $_SESSION["cart"] = new cart();
}
$header_cart_no = $_SESSION["cart"]->number();
$header_cart_tot = price_sep($_SESSION["cart"]->price());
?>
<header class="page">
    <div class="top_header">
    <div class="hide" id="cover" onclick="close_rsm()"></div>
        <div class="top_banner">
            <div class="content">
                <div class="tb_logo vCent">
                    <a class="vCent" href="<?php echo $s;?>">
                        <picture>
                            <source type="image/webp" srcset="<?php echo $s;?>img/webp/logo-mob.webp 500w,<?php echo $s;?>img/webp/logo.webp">
                            <img src="<?php echo $s;?>img/logo-mob.png"  srcset="<?php echo $s;?>img/logo-mob.png 500w,<?php echo $s;?>img/logo.png"/>
                        </picture>
                    </a>
                </div>
                <div class="tb_motto vCent">
                    میوه خشک آبان<br>دستچینی از بهترین میوه‌ها
                </div>
                <div class="tb_location vCent jCL">
                    تهران، ایران<br>ارسال به تمام نقاط کشور
                </div>
            </div>
        </div>
        <div class="top_menu" id="menubar">
            <div class="content">
                <nav class="tm_nav">
                    <ul>
                        <li>
                            <a href="<?php echo $s; ?>">فروشگاه</a>
                        </li>
                        <li>
                            <a href="<?php echo $s."pages/aboutus.php"; ?>">درباره ما</a>
                        </li>
                        <li>
                            <a href="<?php echo $s."pages/contactus.php"; ?>">تماس با ما</a>
                        </li>
                        <li>
                            <a href="<?php echo $s."account/"; ?>">حساب کاربری</a>
                        </li>
                    </ul>
                    <div class="tm_social_holder vCent">
                        <a class="social" onclick="show_rsm()"><span class="icon-menu"></span></a>
                    </div>
                </nav>
                <div class="tm_cart_holder vCent">
                    <a class="cart" href="<?php echo $s?>cart/">
                        <i class="fa fa-shopping-basket" aria-hidden="true"></i>
                        <span id="h_cart_no">
                            <?php echo $header_cart_no; ?>
                        </span>
                         محصول -  
                        <span id="h_cart_tot">
                            <?php echo $header_cart_tot; ?>
                        </span>
                         تومان
                    </a>
                </div>
                <div class="tm_social_holder vCent">
                    <a class="social" href="<?php $instagram = getVarFromDB("contact_info","value","name","اینستاگرام");echo "http://www.instagram.com/$instagram"; ?>"><span class="icon-ig"></span></a>
                </div>
            </div>
        </div>
    </div>
    <div class="rsm hide"></div>
</header>


<script type="text/javascript">
window.onscroll = function() {stickScroll()};
    header = document.getElementById("menubar");
    sticky = header.offsetTop;
function stickScroll(){

    if (window.pageYOffset > sticky) {
        header.classList.add("sticky");
    } else {
        header.classList.remove("sticky");
    }
}

function show_rsm(){
    header.getElementsByTagName("ul")[0].classList.add("show");
    document.getElementById("cover").classList.remove("hide");
    return;
}
function close_rsm(){
    header.getElementsByTagName("ul")[0].classList.remove("show");
    document.getElementById("cover").classList.add("hide");
    return;
}

function refresh_header_cart_badge(){
    var xmlHR = new XMLHttpRequest();
    var url = "<?php echo $s; ?>modules/cart/cart_badge.php";
    xmlHR.open("GET", url, true);
    xmlHR.send();
    xmlHR.addEventListener('readystatechange', function(){
        if (xmlHR.readyState == 4 && xmlHR.status == 200) {
            var data = xmlHR.responseText || "";
            if(data.length > 0 && data[0] === "D"){
                var payload = data.substring(1);
                var sep = payload.indexOf("-");
                if(sep > -1){
                    var cart_no = payload.substring(0,sep);
                    var cart_total = payload.substring(sep+1);
                    var noEl = document.getElementById("h_cart_no");
                    var totEl = document.getElementById("h_cart_tot");
                    if(noEl){ noEl.innerHTML = cart_no; }
                    if(totEl){ totEl.innerHTML = cart_total; }
                }
            }
        }
    });
}

refresh_header_cart_badge();
</script>
