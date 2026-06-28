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
                            <label>نوع محصول:</label>
                            <select name="type">
<?php
    $st = "SELECT name,flag FROM admin_orders_type";
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
            <option value="'.$row["name"].'">'.$row["name"].'</option>';
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
                            <label>وزن‌ها \ پیمانه:</label>
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
    $st = "SELECT name,file_name,category,keywords,description FROM $tb WHERE id = ?";
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
    $keywords = $row["keywords"];
    $description = $row["description"];
    $weight = getVarFromDB("products_price","weight","pid",$id,"start_time DESC");
    echo '              
                        <div class="form_item">
                            <label>نام:</label>
                            <input type="text" name="name" value="'.$name.'">
                        </div>
                        <div class="form_item">
                            <label>نام فایل:</label>
                            <input type="text" value="'.$file_name.'" disabled>
                        </div>
                        <div class="form_item">
                            <label>دسته‌بندی:</label>
                            <select name="category">';
                            

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
                            <label>کلمات کلیدی:</label>
                            <input type="text" name="keywords" value="'.$keywords.'">
                        </div>
                        <div class="form_item">
                            <label>توضیحات:</label>
                            <input type="text" name="description" value="'.$description.'">
                        </div>
                   </div>
               </div>
           </div>
           <div class="ssrv">
               <div class="ssrv_title">مشخصات فروش</div>
               <div class="ssrv_dtl">
                   <div class="form fr">     
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
                        <a class="btn" onclick="open_edit(this)">به روز رسانی اطلاعات فروش</a>
                        
                        </div>
                    
                </div>
            </div>
            <input id="submit_edit" type="submit" name="edit" class="btn" value="ثبت به روز رسانی">
            </form>
            <div class="cut w100p"></div>
<?php
    $discount_table_available = false;
    $discount_weight_price_ready = false;
    include_once $GLOBALS['bu']."modules/jdf.php";
    $discount_weight_ref = trim((string)$weight);
    $discount_data = array(
        "discount_weight" => $discount_weight_ref,
        "discount_price" => "",
        "start_at" => "",
        "end_at" => "",
        "is_active" => 0
    );

    $st_discount = $mysqli->prepare("SELECT * FROM product_discounts WHERE pid = ? ORDER BY id DESC LIMIT 1");
    if($st_discount){
        $discount_table_available = true;
        $discount_weight_price_ready = (product_discount_has_column("discount_weight") && product_discount_has_column("discount_price"));
        $pid_sql = (string)$_SESSION[$cf]->id;
        $st_discount->bind_param('s',$pid_sql);
        if($st_discount->execute()){
            $res_discount = $st_discount->get_result();
            $row_discount = $res_discount ? $res_discount->fetch_assoc() : null;
            if($row_discount){
                if(isset($row_discount["discount_weight"]) && trim((string)$row_discount["discount_weight"]) !== ""){
                    $discount_data["discount_weight"] = (string)$row_discount["discount_weight"];
                }
                if(isset($row_discount["discount_price"])){
                    $discount_data["discount_price"] = (string)$row_discount["discount_price"];
                }
                if(isset($row_discount["start_at"])){
                    $discount_data["start_at"] = (string)$row_discount["start_at"];
                }
                if(isset($row_discount["end_at"])){
                    $discount_data["end_at"] = (string)$row_discount["end_at"];
                }
                if(isset($row_discount["is_active"])){
                    $discount_data["is_active"] = (int)$row_discount["is_active"];
                }
            }
        }
    }

    $s_day = 0;
    $s_month = 0;
    $s_year = 0;
    $e_day = 0;
    $e_month = 0;
    $e_year = 0;
    if($discount_data["start_at"]){
        $s_time = strtotime($discount_data["start_at"]);
        $s_day = (int)jdate("j",(int)$s_time,"","","en");
        $s_month = (int)jdate("n",(int)$s_time,"","","en");
        $s_year = (int)jdate("o",(int)$s_time,"","","en");
    }else{
        $s_day = (int)jdate("j","","","","en");
        $s_month = (int)jdate("n","","","","en");
        $s_year = (int)jdate("o","","","","en");
    }
    if($discount_data["end_at"]){
        $e_time = strtotime($discount_data["end_at"]);
        $e_day = (int)jdate("j",(int)$e_time,"","","en");
        $e_month = (int)jdate("n",(int)$e_time,"","","en");
        $e_year = (int)jdate("o",(int)$e_time,"","","en");
    }else{
        $e_day = $s_day;
        if($s_month != 12){
            $e_month = $s_month + 1;
            $e_year = $s_year;
        }else{
            $e_month = 1;
            $e_year = $s_year + 1;
        }
    }
    $s_l_year = min((int)jdate("Y","","","","en"),$s_year)-1;
    $e_l_year = max((int)jdate("Y","","","","en"),$e_year)+1;
?>
        <h2 class="tac">وضعیت نمایش</h2>
        <div>
        <form action="<?php echo $URL; ?>" method="post">
            <div class="form_item">
                <label>ترتیب نمایش:</label>
                <input type="text" name="show_order" value="<?php echo getVarFromDB($tb,"show_order","id",$_SESSION[$cf]->id); ?>">
            </div>
            <?php if($discount_table_available && $discount_weight_price_ready){ ?>
            <div class="form_item">
                <label>وزن‌های تخفیف:</label>
                <input type="text" value="<?php echo htmlspecialchars((string)$discount_data["discount_weight"], ENT_QUOTES, 'UTF-8'); ?>" disabled>
                <input type="hidden" name="discount_weight" value="<?php echo htmlspecialchars((string)$discount_data["discount_weight"], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
            <div class="form_item">
                <label>قیمت‌های تخفیف‌دار:</label>
                <input type="text" name="discount_price" value="<?php echo htmlspecialchars((string)$discount_data["discount_price"], ENT_QUOTES, 'UTF-8'); ?>">
                <div class="user_hint">لیست را با ویرگول جدا کنید. بین ویرگول‌ها خالی بگذارید تا آن وزن بدون تخفیف باشد.</div>
            </div>
            <div class="form_item">
                <label>تاریخ شروع تخفیف:</label>
                <div class="date_holder">
                    <select name="discount_s_day" required>
                        <?php
                            for($i=1;$i<=31;$i++){
                                $selected = ($s_day == $i) ? " selected " : "";
                                echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                            }
                        ?>
                    </select>
                    <select name="discount_s_month" required>
                        <?php
                            for($i=1;$i<=12;$i++){
                                $selected = ($s_month == $i) ? " selected " : "";
                                $timetext = jmktime(1,0,0,$i,1,1397);
                                $month = jdate("F",(int)$timetext,"","","en");
                                echo '<option '.$selected.' value="'.$i.'">'.$month.'</option>';
                            }
                        ?>
                    </select>
                    <select name="discount_s_year" required>
                        <?php
                            for($i=$s_l_year;$i<=$e_l_year;$i++){
                                $selected = ($s_year == $i) ? " selected " : "";
                                echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form_item">
                <label>تاریخ پایان تخفیف:</label>
                <div class="date_holder">
                    <select name="discount_e_day" required>
                        <?php
                            for($i=1;$i<=31;$i++){
                                $selected = ($e_day == $i) ? " selected " : "";
                                echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                            }
                        ?>
                    </select>
                    <select name="discount_e_month" required>
                        <?php
                            for($i=1;$i<=12;$i++){
                                $selected = ($e_month == $i) ? " selected " : "";
                                $timetext = jmktime(1,0,0,$i,1,1397);
                                $month = jdate("F",(int)$timetext,"","","en");
                                echo '<option '.$selected.' value="'.$i.'">'.$month.'</option>';
                            }
                        ?>
                    </select>
                    <select name="discount_e_year" required>
                        <?php
                            for($i=$s_l_year;$i<=$e_l_year;$i++){
                                $selected = ($e_year == $i) ? " selected " : "";
                                echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form_item">
                <label for="discount_active">فعال بودن تخفیف:</label>
                <input id="discount_active" type="checkbox" name="discount_active" value="1" <?php if((int)$discount_data["is_active"] === 1) echo " checked "; ?>>
            </div>
            <?php }else if($discount_table_available){ ?>
            <div class="user_hint error">ستون‌های <span dir="ltr">discount_weight</span> و <span dir="ltr">discount_price</span> در جدول <span dir="ltr">product_discounts</span> وجود ندارند. ابتدا مایگریشن را اجرا کنید.</div>
            <?php }else{ ?>
            <div class="user_hint error">جدول <span dir="ltr">product_discounts</span> وجود ندارد. ابتدا مایگریشن را اجرا کنید.</div>
            <?php } ?>
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
//    pn.getElementsByTagName("select")[0].disabled = false;
    item.classList.add("hide");
    document.getElementById("profit").classList.remove("hide");
//    document.getElementById("file_name").disabled = true;
//    document.getElementById("file_name").parentNode.classList.add("hide");
//    document.getElementById("submit_edit").classList.remove("hide");
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
    $details = getVarFromDB($tb,"content","id",$_SESSION[$cf]->id);
    if(!$details){$details = "توضیحاتی وجود ندارد";}
    echo $details;
    
?>
</div>
<a class="btn" onclick="sub_show('detail','')">به روز رسانی توضیحات</a>
<div class="cut w100p"></div>
<h2 class="tac">مشتریان پر طرفدار</h2><br>
<div class="sect">
    <table class="tracking">
            <thead>
                <tr>
                    <th >
                        ردیف
                    </th>
                    <th>
                        وزن خریداری شده (گرم)
                    </th>
                    <th>
                        نام
                    </th>
                    <th>
                        تلفن
                    </th>
                    <th>
                        تاریخ آخرین خرید
                    </th>
                    <th>
                        مشاهده حساب
                    </th>
                </tr>
            </thead>
            <tbody>
<?php
    $id = $_SESSION[$cf]->id;
    $st = "SELECT sum(weight) as suw,users.id,users.name,users.tel,max(orders.create_date) as cd FROM `sub_orders` LEFT JOIN orders ON sub_orders.oid = orders.id LEFT JOIN users ON users.id = orders.uid WHERE orders.state > 0 AND sub_orders.pid = $id GROUP BY orders.uid ORDER BY suw DESC,orders.create_date DESC LIMIT 100";
    $res = return_sel_sql($st);
    $k = 0;
    while($row = $res->fetch_assoc()){
        $k++;
        $row['cd'] = correctDate($row['cd']);
        echo "
            <tr>
                <td>$k</td>
                <td>${row['suw']}</td>
                <td>${row['name']}</td>
                <td>${row['tel']}</td>
                <td>${row['cd']}</td>
                <td onclick =\"sub_show('uid','${row['id']}','clients/')\" >
                    <span class=\"curpo icon-i\"></span>
                </td>
            </tr>
        ";
    }
?>
        </tbody>
    </table>
</div>
<?php
    }
?>
<?php
        }
    }
?>
