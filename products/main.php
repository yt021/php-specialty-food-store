<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $dir = $s."content";
    $tb = "products";
    $id = getVarFromDB($tb,"id","file_name",$file_name);
    $category = getVarFromDB($tb,"category","id",$id);
    $state = getVarFromDB($tb,"state","id",$id);
    $weight = getVarFromDB("products_price","weight","pid",$id,'id DESC');
    $weight = get_str_index($weight,",")[1];
    $price_base = getVarFromDB("products_price","price","pid",$id,'id DESC');
    $price_base = get_str_index($price_base,",")[1];
    $price = $price_base;
    $old_price = array();
    $has_discount = false;
    $discount_row = product_discount_get_active($id);
    if($discount_row !== false){
        $discount_prices = product_discount_prepare_price_lists($price_base,$discount_row,$weight);
        $price = $discount_prices["price"];
        $old_price = $discount_prices["old_price"];
        $has_discount = (bool)$discount_prices["has_discount"];
    }
    $first_img_id = getVarFromDB($tb,"first_img_id","id",$id);
    if($first_img_id){
        $first_img_fn = getVarFromDB("content","file_name","id",$first_img_id);
        $first_img_alt = getVarFromDB("content","name","id",$first_img_id);
        
        $ff = pathinfo($first_img_fn, PATHINFO_EXTENSION);
        $fn = pathinfo($first_img_fn, PATHINFO_FILENAME);
                
        $webp_path = "$dir/webp/$fn.webp";
        $webp_m_path = "$dir/webp/$fn.webp";
        $img_path = "$dir/$first_img_fn";
        $img_m_path = "$dir/$fn.$ff";
        
    }else $first_img_fn = false;
    $img_str = getVarFromDB($tb,"img_str","id",$id);
    
    
?>
<main>
    <div class="content">
            <div class="product_item">
                <div id="<?php echo $id; ?>" class="product_data" >
                    <div class="title">
                         <?php $name = getVarFromDB($tb,"name","id",$id);echo $name; ?> 
                    </div>
                    <?php if($category)echo "دسته بندی: $category<br><br>"; ?> 
                    <?php if($category != "پکیج ولنتاین"){ ?>
                    وزن‌ها:
                    <ul class="product_weight weight_options" style="">
                        <?php
                            for($i = 0;$i<sizeof($weight);$i++){
                        ?>
                        <li onclick="select_weight(this)"<?php if($i == 0){?> class="sd" <?php } ?>><?php echo $weight[$i]; ?> گرم
                        </li>
                        <?php
                            }
                        ?>
                    </ul>
                    <br><br>
                    <?php } ?>
                    <?php
                        if((int)$state === 0){
                    ?> 
                    
                    قیمت: 
                    <span class="price <?php if($has_discount) echo "has_s"; ?>">
                        <?php for($i=0;$i<sizeof($price);$i++){ ?>
                        <span <?php if($i>0){echo ' class="hide" ';} ?>>
                            <?php
                             if($has_discount && isset($old_price[$i])){
                                    echo "<s>&nbsp;&nbsp;".price_sep($old_price[$i])."&nbsp;&nbsp;</s><br>".price_sep($price[$i])." تومان";
                                }else if($id == 73){
                                    echo '<span style="font-size:10.5px;line-height:16px;">'.'قیمت براساس باکس‌های انتخابی'.'</span>';
                                }else{
                                    echo price_sep($price[$i])." تومان";
                                }
                            ?>
                        </span>
                        <?php
                            }
                        ?>
                        
                    </span><br>
                           
                    
                   <?php
                        if($id == 73){
                    ?>        
                    <a href='<?php echo $s; ?>custom/?it=valen' class="cart_button">
                        انتخاب محتوا
                    </a>
                    <?php        
                        }else{
                    ?>
                    <a onclick="add_to_cart(this)" class="cart_button">
                        افزودن به سبد
                    </a>
                    <?php } ?>
                    <?php
                        }else{
                    ?>
                    <a class="cart_button red">
                        ناموجود
                    </a>
                    <?php
                        }
                    ?>
                    <div class="cb"></div>
                    <div class="detail big">
                        <?php echo getVarFromDB($tb,"content","id",$id); ?> 
                    </div>
                    
                </div>
                <div class=""></div>
                <div id="photo_box">
                    <div id="current_pic" class="vCent">
                        <?php if(isset($first_img_fn)){ ?>
                        <picture loading="lazy">
                            <source type="image/webp" srcset="<?php  echo $webp_m_path; ?> 500w,<?php  echo $webp_path; ?>">
                            <img src="<?php echo $img_m_path; ?>" srcset="<?php echo $img_m_path; ?> 500w,<?php echo $img_path; ?> " 
                            alt="<?php if($first_img_alt)echo $first_img_alt; ?>" loading="lazy">
                        </picture>
                        <?php }else{ ?>
                        <picture>
                            <img alt="<?php if($first_img_alt)echo $first_img_alt; ?>" src="<?php if($first_img_fn)echo $s."content/$first_img_fn"; ?>"/>
                        </picture>
                        <?php } ?>
                    </div>
                    <div id="pics_ul">
                        <ul>
                        <?php
                            if($img_str){
                                $images = get_str_index($img_str,",")[1];
                                foreach($images as $image_id){
                                    if(getVarFromDB("content","type","id",$image_id) == "Image"){
                                        $image_fn = getVarFromDB("content","file_name","id",$image_id);
                                        
                                        $ff = pathinfo($image_fn, PATHINFO_EXTENSION);
                                        $fn = pathinfo($image_fn, PATHINFO_FILENAME);
                                                
                                        $webp_path = "$dir/webp/$fn.webp";
                                        $webp_m_path = "$dir/webp/$fn.webp";
                                        $img_path = "$dir/$image_fn";
                                        $img_m_path = "$dir/$fn.$ff";
                                        
                                        $image_title = getVarFromDB("content","name","id",$image_id);
                        ?>
                            <li class="vCent"  onclick="change_cur_img(this)">
                                <picture loading="lazy">
                                    <source type="image/webp" srcset="<?php  echo $webp_m_path; ?> 500w,<?php  echo $webp_path; ?>">
                                    <img src="<?php echo $img_m_path; ?>" srcset="<?php echo $img_m_path; ?> 500w,<?php echo $img_path; ?> " 
                                    alt="<?php if($image_title)echo $image_title; ?>" loading="lazy">
                                </picture>
                                <span class="icon-eye"></span>
                            </li>
                        <?php
                                    }}}
                        ?>
                        </ul>
                    </div>
                </div>
                <div class="detail small">
                        <?php echo getVarFromDB($tb,"content","id",$id); ?> 
                </div>
                
            </div>
    </div>
    <div style="width:100%;background-color:#ccbeba;height:80px;margin-top:20px;color:white;text-align:center;font-size:28px;line-height:80px;">
        دستچینی از بهترین میوه‌ها
    </div>
    <!--<img src="<?php echo $s."img/product_banner.png"; ?>" />-->
    <div class="content">
        <h4 class="tac">سایر محصولات</h4>
        <ul class="products">
        <?php
            $table = "products";
//            $st = "SELECT count(*) FROM $table WHERE del_flag = 0";
//            $st = $mysqli->prepare($st);
//            if(!$st->execute()){
//                echo "E";
//                exit;
//            }
//            if(!$st->execute()){
//            echo "E";
//            exit;
//            }
//            $st->store_result();
//            $st->bind_result($count);
//            $st->fetch();
//            
            $st = "SELECT id,name,file_name,category,first_img_id FROM $table WHERE del_flag = 0 AND state = 0 AND category <> 'غیر قابل فروش' AND type = 'pack' ORDER BY RAND() LIMIT 4";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "E";
                exit;
            }
            $res = $st->get_result();
            while($row = $res->fetch_assoc()){
                $id = $row["id"];
                $name = $row["name"];
                $file_name = $row["file_name"];
                $cate = $row["category"];
                $img = $row["first_img_id"];
                $img_fn = getVarFromDB("content","file_name","id",$img);
                
                $ff = pathinfo($img_fn, PATHINFO_EXTENSION);
                $fn = pathinfo($img_fn, PATHINFO_FILENAME);
                
                $webp_path = "$dir/webp/$fn.webp";
                $webp_m_path = "$dir/webp/$fn.webp";
                $img_path = "$dir/$img_fn";
                $img_m_path = "$dir/$fn.$ff";
                
                $img_alt = getVarFromDB("content","name","id",$img);
                $weight = getVarFromDB("products_price","weight","pid",$id,"start_time DESC");
                $weight = get_str_index($weight,",")[1];
                $price_base = getVarFromDB("products_price","price","pid",$id,"start_time DESC");
                $price_base = get_str_index($price_base,",")[1];
                $price = $price_base;
                $old_price = array();
                $has_discount = false;
                $discount_row = product_discount_get_active($id);
                if($discount_row !== false){
                    $discount_prices = product_discount_prepare_price_lists($price_base,$discount_row,$weight);
                    $price = $discount_prices["price"];
                    $old_price = $discount_prices["old_price"];
                    $has_discount = (bool)$discount_prices["has_discount"];
                }
                
                $weight_hide = "";
                if($cate == "پکیج ولنتاین"){
                    $weight_hide = " hide ";
                }
        ?>
            <li class="product" id="<?php echo $id; ?>">
                <a href="<?php echo $s."products/$file_name.php"; ?>">
                    <div class="img">
                        <picture loading="lazy">
                            <source type="image/webp" srcset="<?php  echo $webp_m_path; ?> 500w,<?php  echo $webp_path; ?>">
                            <img src="<?php echo $img_m_path; ?>" srcset="<?php echo $img_m_path; ?> 500w,<?php echo $img_path; ?> " 
                            alt="<?php echo $img_alt; ?>" loading="lazy">
                        </picture>
                    </div>
                    <h3 class="tac"><?php echo $name; ?></h3>
                    
                </a>
                <ul class="product_weight weight_options <?php echo $weight_hide; ?>">
                    <?php
                        for($i = 0;$i<sizeof($weight);$i++){
                    ?>
                    <li onclick="select_weight(this)"<?php if($i == 0){?> class="sd" <?php } ?>><?php echo $weight[$i]; ?> گرم
                    </li>
                    <?php
                        }
                    ?>
                </ul>
                <span class="price <?php if($has_discount) echo "has_s"; ?>">
                    <?php for($i=0;$i<sizeof($price);$i++){ ?>
                    <span <?php if($i>0){echo ' class="hide" ';} ?>>
                        <?php
                        if($has_discount && isset($old_price[$i])){
                                    echo "<s>&nbsp;&nbsp;".price_sep($old_price[$i])."&nbsp;&nbsp;</s><br>".price_sep($price[$i])." تومان";
                                }else if($id == 73){
                                    echo '<span style="font-size:10.5px;line-height:16px;">'.'قیمت براساس باکس‌های انتخابی'.'</span>';
                                }else{
                                    echo price_sep($price[$i])." تومان";
                                }
                        ?>
                    </span>
                    <?php
                        }
                    ?>
                </span>
                  <?php
                        if($id == 73){
                    ?>        
                    <a href='<?php echo $s; ?>custom/?it=valen' class="cart_button">
                        انتخاب محتوا
                    </a>
                    <?php        
                        }else{
                    ?>
                    <a onclick="add_to_cart(this)" class="cart_button">
                        افزودن به سبد
                    </a>
                    <?php } ?>
            </li>
            <?php
            }
            ?>
        </ul>
    </div>
</main>
<style type="text/css">
s{
    text-decoration:line-through;
    color:maroon;
}
span.price.has_s{
    line-height:20px;
}
</style>
<script type="text/javascript">
    base_url = "<?php echo $s; ?>";
</script>
<script src="<?php echo asset_url('js/main.js'); ?>"></script>
<script src="<?php echo asset_url('js/cart.js'); ?>"></script>
<script type="text/javascript">
function change_cur_img(item){
    item = item.getElementsByTagName("picture")[0];
    new_outer = item.outerHTML;
    cur_img = document.getElementById("current_pic").getElementsByTagName("picture")[0];
    cur_outer = cur_img.outerHTML;
    cur_img.outerHTML = new_outer;
    item.outerHTML = cur_outer;
}
</script>
<?php
        }
    }
?>
