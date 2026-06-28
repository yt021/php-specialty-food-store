<?php
    if(isset($indexed)){
        if($indexed == 1){?>
            <div class="ssrv">
                <div class="ssrv_title">مشخصات</div>        
                <div class="ssrv_dtl">
                    <form action="<?php echo $URL; ?>" method="post">
                        <div class="form fr">
<?php
    if($_SESSION[$cf]->id == "new"){$name = "";$title="";$keywords="";$description="";$content="";}else{
    $name = getVarFromDB($tb,"name","id",$_SESSION[$cf]->id);
    $title = getVarFromDB($tb,"title","id",$_SESSION[$cf]->id);
    $keywords = getVarFromDB($tb,"keywords","id",$_SESSION[$cf]->id);
    $description = getVarFromDB($tb,"description","id",$_SESSION[$cf]->id);
    $content = getVarFromDB($tb,"content","id",$_SESSION[$cf]->id);
    }
    echo '              
                        <div class="form_item">
                            <label>نام:</label>
                            <input name="name" type="text" value="'.$name.'">
                        </div>
                        <div class="form_item">
                            <label>عنوان صفحه:</label>
                            <input name="title" type="text" value="'.$title.'">
                        </div>
                        <div class="form_item">
                            <label>کلمات کلیدی:</label>
                            <input name="keywords" type="text" value="'.$keywords.'">
                        </div>
                        <div class="form_item">
                            <label>توضیحات:</label>
                            <textarea name="description">'.$description.'</textarea>
                        </div>
                        
                        
                    ';
?>

                            
                        </div>
                    
                </div>
            </div>
            <input type="submit" name="edit" class="btn" value="ثبت">
</form>     
<?php   if($_SESSION[$cf]->id != "new"){ ?>
<div class="cut w100p"></div>
<h2 class="tac">محتوا</h2><br>
<div class="details">
<?php
    if(!$content){$content = "محتوایی وجود ندارد";}
    echo $content;
?>
</div>
<a class="btn" onclick="sub_show('content','')">به روز رسانی محتوا</a>
<?php
        }
?>
<?php
        }
    }
?>