<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <div class="ssrv">
                <div class="ssrv_title">متن اسلایدر</div>        
                <div class="ssrv_dtl">
                    <form action="<?php echo $URL ?>" method="post">
                        <div class="form fr">
<?php
    $id = $_SESSION[$cf]->id;
    if($id == "new"){
        $row["title"]="";
        $row["detail"]="";
        $row["link"]="";
    }else{
    $st = "SELECT * FROM $tb WHERE id = $id";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    }
    
    echo '              
                        <div class="form_item">
                            <label>عنوان:</label>
                            <input name="title" type="text" value="'.$row["title"].'">
                        </div>
                        <div class="form_item">
                            <label>لینک:</label>
                            <input name="link" type="text" value="'.$row["link"].'">
                        </div>
                        <div class="form_item">
                            <label>متن:</label>
                            <textarea name="detail">'.$row["detail"].'</textarea>
                        </div>
                    ';
?>
                        </div>
                </div>
            </div>
            
            <input type="submit" name="edit" class="btn" value="ثبت">
            </form>

<?php
if($id !="new"){
    $image_id = $row["image_id"];
    if($image_id)$image_fn = getVarFromDB("content","file_name","id",$image_id);else $image_fn = false;
?>            
            
            
<div class="cut w100p"></div>
<div style="display:table;width:100%;">
<!--<h2 class="tac">تصاویر</h2><br>-->
<div class="images fr" style="width:100%;">
    <h3 class="tac">تصویر</h3>
    <div class="fr img" style="width:100%;overflow:hidden;">
    <img src="<?php if($image_fn)echo $s."content/$image_fn"; ?>" />
    <div class="btn" onclick="sub_show('pic','single')">انتخاب / تغییر</div>
    </div>
</div>
<?php
}
?>
<?php
        }
    }
?>