<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <form action="<?php echo $URL; ?>" method="post">
            <div class="ssrv">
                <div class="ssrv_title">مشخصات</div>        
                <div class="ssrv_dtl">
                    
                        <div class="form fr">
<?php
    if($_SESSION[$cf]->id == "new"){$name = "";$username="";$level=1;}else{
    $name = getVarFromDB($tb,"name","id",$_SESSION[$cf]->id);
    $username = getVarFromDB($tb,"username","id",$_SESSION[$cf]->id);
    $level = getVarFromDB($tb,"level","id",$_SESSION[$cf]->id);}
    echo '              
                        <div class="form_item">
                            <label>نام:</label>
                            <input name="name" type="text" value="'.$name.'">
                        </div>
                        <div class="form_item">
                            <label>نام کاربری:</label>
                            <input name="username" type="text" value="'.$username.'">
                        </div>
                        <div class="form_item">
                            <label>سطح مدیریت:</label>
                            <input name="level" type="text" value="'.$level.'">
                        </div>
                        <div class="form_item">
                            <label>گذرواژه جدید:</label>
                            <input name="new_pass" type="password">
                        </div>
                        <div class="form_item">
                            <label>تکرار گذرواژه:</label>
                            <input name="cnf_new_pass" type="password">
                        </div>
                        
                    ';
?>
                            
                        </div>
                    
                </div>
            </div>
            <input type="submit" name="edit" class="btn" value="ثبت">
        </form>
<?php
    if($_SESSION[$cf]->id != "new"){
?>               
        </div>  
    </div>
    <div class="cut w100p"></div>

    <div id="sect3" class="sect ">
        <div class="middle container">
            <a class="btn" onclick="sub_show('access','set')">تنظیم دسترسی</a>     
<?php
    }
?>
<?php
        }
    }
?>