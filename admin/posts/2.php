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
    $category = getVarFromDB($tb,"category","id",$_SESSION[$cf]->id);
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
                            <label>عنوان مطلب:</label>
                            <input name="title" type="text" value="'.$title.'">
                        </div>
                        <div class="form_item">
                            <label>دسته‌بندی:</label>
                            <select name="category">';
                            $section = $_SESSION[$cf]->state;
                            $st = "SELECT name FROM categories WHERE del_flag = 0 AND section = '$section'";
                            $st = $mysqli->prepare($st);
                            if(!$st->execute()){
                                echo "E";
                                exit;
                            }
                            $res = $st->get_result();
                            while($row = $res->fetch_assoc())
                            {                                
                                echo '
                                    <option value="'.$row["name"].'">'.$row["name"].'</option>';
                            }    
echo                        '</select>
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
<?php
    $first_img_id = getVarFromDB($tb,"first_img_id","id",$_SESSION[$cf]->id);
    if($first_img_id)$first_img_fn = getVarFromDB("content","file_name","id",$first_img_id);else $first_img_fn = false;
?>
<div class="cut w100p"></div>

<div style="display:table;width:100%;">
    <h2 class="tac">تصاویر</h2><br>
    <div class="images fr" style="width:300px;">
        <h3 class="tac">تصویر اصلی</h3>
        <div class="fl img">
            <img src="<?php if($first_img_fn)echo $s."content/$first_img_fn"; ?>" />
            <div class="btn" onclick="sub_show('pic','single')">انتخاب / تغییر</div>
        </div>
    </div>
</div>

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
    if($_SESSION["a_logged"]->get_level() >= 2){
?>

</div>  
</div>          
<div class="cut w100p"></div>
    <div id="sect2" class="sect">
        <div class="middle container">
            <a class="btn submit red" onclick="if(confirm('آیا مطمئنید?'))sub_show('del','post');">حذف مطلب</a>
            
<?php
    }
?>
<?php
        }
    }
?>