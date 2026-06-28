<?php
    if(isset($indexed)){
        if($indexed == 1){?>
            <div class="ssrv">
                <div class="ssrv_title">تغییر گذرواژه</div>        
                <div class="ssrv_dtl">
                    <form action="<?php echo $URL; ?>" method="post">
                        <div class="form fr">
<?php
    echo '              
                        <div class="form_item">
                            <label>گذرواژه کنونی:</label>
                            <input name="old_pass" type="password" value="">
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
                            <input type="submit" name="submit_edit" class="btn" value="ثبت">
                        </div>
                    </form>
                </div>
            </div>

<?php
        }
    }
?>