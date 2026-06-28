<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <form action="<?php echo $URL; ?>" method="post">
            <div class="ssrv">
                <div class="ssrv_title">مشخصات</div>        
                <div class="ssrv_dtl">
                    <div class="form fr">
<?php
    $fields = ["county","cities_str","cost"];
    
    if($_SESSION[$cf]->id == "new"){
        foreach($fields as $f){
            $data[$f] = "";
        }
    }
    else{
        foreach($fields as $f){
            $data[$f] = getVarFromDB($tb,$f,"id",$_SESSION[$cf]->id);
        }
    }
    echo '              
                        <div class="form_item">
                            <label>استان:</label>
                            <input name="county" type="text" value="'.$data["county"].'">
                        </div>
                        <div class="form_item">
                            <label>بخش‌ها:</label>
                            <input name="cities_str" type="text" value="'.$data["cities_str"].'">
                        </div>
                        <div class="form_item">
                            <label>هزینه ارسال:</label>
                            <input name="cost" type="text" value="'.$data["cost"].'">
                        </div>
                    ';
?>
                    </div>
                </div>
            </div>
        
            <input type="submit" name="edit" class="btn" value="ثبت">
</form>     

<?php
        }
    }
?>