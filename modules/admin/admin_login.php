<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<div id="main" class="middle">
    <div id="sect1" class="sect">
        <div class="middle container">
            <div class="title tac">
                ورود مدیریت
                <div class="cut"></div>
            </div>
            <div class="admin login_form">
                <form id="admin_login" action="<?php echo $URL; ?>" method="post">
                    <label>نام کاربری:</label>
                    <input type="text" name="user" id="user" autofocus="autofocus">
                    <label>گذرواژه:</label>
                    <input type="password" name="pass" id="pass">
                    <input type="submit" name="login" class="btn" value="ورود">
                </form>    
            </div>
        </div>
    </div>
<!--    <div class="cut"></div>-->
</div>


<script type="text/javascript">
var base_url = "<?php echo $s; ?>";

//function form_submit(){
//    document.getElementById('admin_login').submit();
//}
//
//document.body.addEventListener('keypress',function (e) {
//    var key = e.which || e.keyCode;
//    if (key === 13) { // 13 is enter
//      form_submit();
//    }
//});

</script>
<?php
        }
    }
?>