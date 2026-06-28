<?php 
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php"); 
    include $bu."modules/cart/session_start.php";
?>
<!DOCTYPE html>
<html prefix="og: http://ogp.me/ns#" class="" lang="en">

<?php
    include $bu."modules/main/head.php";
?>
<body>
    <div class="column">
        <div class="background"></div>
        <div class="box">
            <div class="logo_holder">
                <a href="https://abanfruit.com">
                    <img src="<?php echo $s; ?>img/logo.png" />
                </a>
            </div>
            <a href="https://abanfruit.com">
            <h1 class="tac">
                
                میوه خشک آبان
            </h1>
            </a>
        </div>
        <div class="cut"></div>
        <div class="box">
            <h2>
                دسترسی:
            </h2>
            <ul class="access">
<?php
    $access_links = ["https://abanfruit.com","$s"."account","$s"."comment"];
    $access_titles = [
        "مشاهده محصولات و ثبت سفارش",
        "پیگیری وضعیت سفارش",
        "ثبت نظرات",
    ];
    $access = array_combine($access_titles,$access_links);
    foreach($access as $title=>$link){
        echo "<a href='$link'>
                <li>
                $title
                <span class='icon-a'></span>
                </li>
                </a>
        ";
    }
?>
            </ul>
        </div>
        <div class="cut"></div>
        <div class="box">
            <h2 class="tac">
                ارتباط سریع
            </h2>
            <ul class="contact">
<?php
    $contact_links = ["https://t.me/aban_fruit","https://wa.me/989376998956"];
    $contact_titles = [
        "تلگرام",
        "واتس‌آپ",
    ];
    $contact_icons = ["telegram","whatsapp"];
    $contact = array_combine($contact_titles,$contact_links);
    $contact_icons = array_combine($contact_titles,$contact_icons);
    foreach($contact as $title=>$link){
        echo "<a href='$link'>
                <li class='".$contact_icons[$title]."'>
                <span class='icon-".$contact_icons[$title]."'></span>
                $title
                </li>
                </a>
        ";
    }
?>
            </ul>
        </div>
        <div class="cut"></div>
        <div class="box">
            <div class="badges">
                <a style="display:block;float:left;" target="_blank" href="https://trustseal.enamad.ir/?id=132402&amp;Code=4salyCtIhTW94npoQaIN"><img src="https://Trustseal.eNamad.ir/logo.aspx?id=132402&amp;Code=4salyCtIhTW94npoQaIN" alt="" style="cursor:pointer" id="4salyCtIhTW94npoQaIN"></a>
            </div>
        </div>
    </div>

</body>
<style type="text/css">
*{
    margin:0;padding:0;
}
div.column{
    width:400px;
    margin:10px auto;
}
div.box{
    margin:5px 0;
    padding:5px;
}
div.box .logo_holder{
    width:100px;
    margin:auto;
}
div.box h1{
    font-size:24px;
}
div.box h2{
    font-size:18px;
}
ul.access li{
    background-color:#ffeccb;
    color:#d08501;
    border-radius:7px;
    padding:7px;
    margin:8px;
}
ul.access li:hover{
    background-color:#ffd894;
    color:#a66b03;
}
ul.access li span{
    float:left;
    transform:rotate(90deg);
    position:relative;
    top:5px;
    left:10px;
}
ul.access li:hover span{
    left:2px;
}
ul.contact{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
}
ul.contact li{
    background-color:red;
    color:white;
    border-radius:12px;
    padding:5px;
    margin:10px;
    width:160px;
    height:40px;
    float:right;
    text-align:center;
    overflow:hidden;
    line-height:20px;
    font-size:16px;
}
ul.contact li:hover{
    background-color:maroon;
    color:#a66b03;
}
ul.contact li span{
/*    display:inline-block;*/
/*    float:right;*/
    font-size:20px;
    margin-left:5px;
    position:relative;
    top:5px;
}
ul.contact li.telegram{
    background-color:#35ACE1;
    color:white;
}
ul.contact li.whatsapp{
    background-color:#00E676;
    color:white;
}
ul.contact li:hover{
    box-shadow:0px 0px 10px 0 rgba(0,0,0,0.5);
}

div.badges{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
}

@media only screen and (max-width: 399px){
div.column{
    width:320px;
}
ul.contact li{
    margin:10px;
    width:130px;
}
}

div.background{
    position:fixed;
    z-index:-1;
    top:0;
    right:0;
    width:100%;
    height:100vh;
    background-image: url('<?php echo $s; ?>content/Image6276892780.jpg');
    opacity:0.05;
}
</style>