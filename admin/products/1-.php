<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <form action="<?php echo $URL; ?>" method="post">
        <div class="ssrv">
                <div class="ssrv_title">مشخصات محصول</div>        
                <div class="ssrv_dtl">
                    
                        <div class="form fr">
<?php
    
    include_once $bu."modules/cart/cart_funcs.php";
    
    ?>
<?php
    if($_SESSION[$cf]->id == "new"){
        
?>
                        <div class="form_item">
                            <label>نام:</label>
                            <input type="text" name="name">
                        </div>
                        <div class="form_item">
                            <label>نام فایل:</label>
                            <input type="text" name="file_name">
                        </div>
                        <div class="form_item">
                            <label>دسته‌بندی:</label>
                            <select name="category">
<?php
    $st = "SELECT name FROM categories WHERE section = 'products' ";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row = $res->fetch_assoc())
    {
        echo '
            <option value="'.$row["flag"].'">'.$row["name"].'</option>';
    }
    
?>
                            </select>
                        </div>                       
                    </div>
                
            </div>
        </div>
        <div class="ssrv">
                <div class="ssrv_title">وزن و قیمت</div>        
                <div class="ssrv_dtl">
                    <div class="form fr">
                        <div class="form_item">
                            <label>وزن‌ها:</label>
                            <input type="text" name="weight">
                        </div>
                        <div class="form_item">
                            <label>قیمت:</label>
                            <input type="text" name="price">
                        </div>
                        <div class="form_item">
                            <label>سود:</label>
                            <input type="text" name="profit">
                        </div>
                        
                    </div>
                
            </div>
        </div>
        <input type="submit" name="edit" class="btn" value="ثبت">
    </form>
<?php
    }else{
?>
<?php
    $st = "SELECT name,file_name,category FROM $tb WHERE id = ?";
    $st = $mysqli->prepare($st);
    $st->bind_param('s',$_SESSION[$cf]->id);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    $id = $_SESSION[$cf]->id;
    $name = $row["name"];
    $file_name = $row["file_name"];
    $category = $row["category"];
    $weight = getVarFromDB("products_price","weight","pid",$id,"start_time DESC");
    echo '              
                        <div class="form_item">
                            <label>نام:</label>
                            <input disabled type="text" name="name" value="'.$name.'">
                        </div>
                        <div class="form_item">
                            <label>نام فایل:</label>
                            <input id="file_name" type="text" name="file_name" value="'.$file_name.'" disabled>
                        </div>
                        <div class="form_item">
                            <label>دسته‌بندی:</label>
                            <select disabled name="category">';
                            

    $st = "SELECT name FROM categories WHERE section = 'products' ";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row = $res->fetch_assoc())
    {
        if($category == $row["name"]){
            $selected = " selected ";
        }else{$selected = "";}
        echo '
            <option value="'.$row["name"].'" '.$selected.'>'.$row["name"].'</option>';
    }
                                                        

    echo               '</select>
                        </div>
                        <div class="form_item">
                            <label>وزن‌ها:</label>
                            <input disabled type="text" name="weight" value="'.$weight.'">
                        </div>

                        <div class="form_item hide">
                            <label>قیمت:</label>
                            <input disabled type="text" name="price">
                        </div>
                        <div id="profit" class="form_item hide">
                            <label>سود:</label>
                            <input disabled type="text" name="profit">
                        </div>
                        
                    ';
?>
                        <a class="btn" onclick="open_edit(this)">به روز رسانی</a>
                        
                        </div>
                    
                </div>
            </div>
            <input id="submit_edit" type="submit" name="edit" class="hide btn" value="ثبت به روز رسانی">
            </form>
            <div class="cut w100p"></div>
        <h2 class="tac">وضعیت نمایش</h2>
        <div>
        <form action="<?php echo $URL; ?>" method="post">
            <div class="form_item">
                <label>ترتیب نمایش:</label>
                <input type="text" name="show_order" value="<?php echo getVarFromDB($tb,"show_order","id",$_SESSION[$cf]->id); ?>">
            </div>
            <div class="form_item">
                <label>وضعیت تخفیف:</label>
                <input type="checkbox" name="sale_state" value="1" <?php if(getVarFromDB($tb,"sale_state","id",$_SESSION[$cf]->id)==1)echo " checked "; ?>>
            </div>
            <input type="submit" name="edit" class="btn" value="ثبت">
        </form>
        </div>
        <div class="cb cut w100p" style="margin-top:50px;"></div>
        <h2 class="tac">قیمت‌ها</h2>
        
        <table class="tracking">
            <thead>
                <tr>
                    <th >
                        ردیف
                    </th>
                    <th onclick="sub_show('order','weight')">
                        وزن
                    </th>
                    <th onclick="sub_show('order','price')">
                        قیمت
                    </th>
                    <th onclick="sub_show('order','profit')">
                        سود
                    </th>
                    <th onclick="sub_show('order','start_time')">
                        تاریخ ثبت
                    </th>
                </tr>
            </thead>
            <tbody>
<?php    
    //$order_str = $_SESSION[$cf]->order_by->order_str('products_price');
    $st = "SELECT price,weight,start_time,profit FROM products_price WHERE pid = ? ORDER BY 3 DESC";
    $st = $mysqli->prepare($st);
    $st->bind_param('s',$_SESSION[$cf]->id);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $k = 0;
    while($row = $res->fetch_assoc())
    {
        $k++;
?>
                <tr>
                    <td>
                        <?php echo $k;?> 
                    </td>
                    <td>
                        <?php echo $row["weight"];?>
                    </td>
                    <td>
                        <?php echo $row["price"];?>
                    </td>
                    <td>
                        <?php echo $row["profit"];?>
                    </td>
                    <td>
                        <?php echo correctDate($row["start_time"]);?>
                    </td>
                </tr>
<?php
    }
?>
            </tbody>
        </table>
<script type="text/javascript">
function open_edit(item){
    pn = item.parentNode;
    hides = pn.getElementsByClassName("hide");
    
    for(i=0;i<hides.length;i++){
        hides[i].classList.remove("hide");
    }
    inputs = pn.getElementsByTagName("input");
    for(i=0;i<inputs.length;i++){
        inputs[i].disabled = false;
    }
    pn.getElementsByTagName("select")[0].disabled = false;
    item.classList.add("hide");
    document.getElementById("profit").classList.remove("hide");
    document.getElementById("file_name").disabled = true;
    document.getElementById("file_name").parentNode.classList.add("hide");
    document.getElementById("submit_edit").classList.remove("hide");
    return;
}
</script>


<!-- Images -->
<?php
    $first_img_id = getVarFromDB($tb,"first_img_id","id",$_SESSION[$cf]->id);
    $img_str = getVarFromDB($tb,"img_str","id",$_SESSION[$cf]->id);
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
<div class="fl images multi">
    <h3 class="tac">سایر تصاویر</h3>
<?php
    if($img_str){
        $images = get_str_index($img_str,",")[1];
        foreach($images as $image_id){
            if(getVarFromDB("content","type","id",$image_id) == "Image"){
                $image_fn = getVarFromDB("content","file_name","id",$image_id);
?>
    
    <div class="img">
        <img src="<?php echo $s."content/$image_fn"; ?>" />
        <div class="btn right" onclick="sub_show('img_right','<?php echo $image_id ?>')">
        <span class="icon icon-angl"></span></div>
        <div class="btn left" onclick="sub_show('img_left','<?php echo $image_id ?>')">
        <span class="icon icon-angl"></span></div>
        <div class="btn mid" onclick="sub_show('img_del','<?php echo $image_id ?>')">
        <span class="icon icon-x"></span></div>
    </div>
<?php
            }
        }
    }
?>
    <div class="plus img" onclick="sub_show('pic','multi')">
        <div class="cross">
            <span class="icon icon-x">
            </span>
        </div>
    </div>
</div>
</div>

<!-- Details -->

<div class="cut w100p"></div>
<h2 class="tac">توضیحات</h2><br>
<div class="details">
<?php
    $details = getVarFromDB($tb,"detail","id",$_SESSION[$cf]->id);
    if(!$details){$details = "توضیحاتی وجود ندارد";}
    echo $details;
    
?>
</div>
<a class="btn" onclick="sub_show('detail','')">به روز رسانی توضیحات</a>
<?php
    }
?>
<?php
        }
    }
?>