<?php
    if(isset($indexed)){
        if($indexed == 1){?>
            <div class="ssrv">
                <div class="ssrv_title">مشخصات</div>        
                <div class="ssrv_dtl">
                    <form action="<?php echo $URL; ?>" method="post">
                        <div class="form fr">
<?php
    $username = getVarFromDB($tb,"username","id",$_SESSION["a_logged"]->uid);
    echo '              
                        <div class="form_item">
                            <label>نام:</label>
                            <input disabled name="name" type="text" value="'.$_SESSION["a_logged"]->name.'">
                        </div>
                        <div class="form_item">
                            <label>نام کاربری:</label>
                            <input disabled name="username" type="text" value="'.$username.'">
                        </div>
                        
                    ';
?>
                            <a class="btn" onclick="sub_show('edit','pass')">تغییر گذرواژه</a>
                        </div>
                    </form>
                </div>
            </div>

<?php
        }
    }
?>