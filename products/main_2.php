<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
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
    if($first_img_id){$first_img_fn = getVarFromDB("content","file_name","id",$first_img_id);$first_img_alt = getVarFromDB("content","name","id",$first_img_id);}else $first_img_fn = false;
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
                    قیمت: 
                    <span class="price <?php if($has_discount) echo "has_s"; ?>">
                        <?php for($i=0;$i<sizeof($price);$i++){ ?>
                        <span <?php if($i>0){echo ' class="hide" ';} ?>>
                            <?php
                                if($has_discount && isset($old_price[$i])){
                                    echo "<s>&nbsp;&nbsp;".price_sep($old_price[$i])."&nbsp;&nbsp;</s><br>".price_sep($price[$i])." تومان";
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
                        if((int)$state === 0){
                    ?>        
                    
                    <a onclick="add_to_cart(this)" class="cart_button">
                        افزودن به سبد
                    </a>
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
                        <img alt="<?php if($first_img_alt)echo $first_img_alt; ?>" src="<?php if($first_img_fn)echo $s."content/$first_img_fn"; ?>"/>
                    </div>
                    <div id="pics_ul">
                        <ul>
                        <?php
                            if($img_str){
                                $images = get_str_index($img_str,",")[1];
                                foreach($images as $image_id){
                                    if(getVarFromDB("content","type","id",$image_id) == "Image"){
                                        $image_fn = getVarFromDB("content","file_name","id",$image_id);
                                        $image_title = getVarFromDB("content","name","id",$image_id);
                        ?>
                            <li class="vCent"  onclick="change_cur_img(this)">
                                <img alt="<?php echo $image_title; ?>" src="<?php echo $s."content/$image_fn"; ?>"/>
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
            $st = "SELECT id,name,file_name,category,first_img_id FROM $table WHERE del_flag = 0 AND state = 0 ORDER BY RAND() LIMIT 4";
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
        ?>
            <li class="product" id="<?php echo $id; ?>">
                <a href="<?php echo $s."products/$file_name.php"; ?>">
                    <div class="img">
                        <img src="<?php echo $s."content/$img_fn"; ?>" alt="<?php echo $img_alt; ?>"/>
                    </div>
                    <h3 class="tac"><?php echo $name; ?></h3>
                    
                </a>
                <ul class="product_weight weight_options">
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
                            }else{
                                echo price_sep($price[$i])." تومان";
                            }
                        ?>
                    </span>
                    <?php
                        }
                    ?>
                </span>
                <a onclick="add_to_cart(this)" class="cart_button">
                    افزودن به سبد
                </a>
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
    item = item.getElementsByTagName("img")[0];
    new_src = item.src;
    cur_img = document.getElementById("current_pic").getElementsByTagName("img")[0];
    cur_src = cur_img.src;
    cur_img.src = new_src;
    item.src = cur_src;
}
</script>
<?php
        }
    }
?>
