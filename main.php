<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php  include $bu."modules/main/slider.php"; ?>
<main>
    <div class="content">
        <h1 class="tac">میوه خشک</h1>
        <div class="cat_filter">
            <ul class="product_weight weight_options">
                <li onclick="show_cat(0,this)" class="sd">همه محصولات</li>
<?php
    $tb_cat = "categories";
    $st_cat = "SELECT id,name FROM $tb_cat WHERE section = 'products' AND name <> 'غیر قابل فروش' ORDER BY show_order ASC;";
    $st_cat = $mysqli->prepare($st_cat);
    if(!$st_cat->execute()){
        echo "E";
        exit;
    }
    $res_cat = $st_cat->get_result();
    $tb = "products";
    $tb2 = $tb."_price";
    $dir = $s."content";
    while($row_cat = $res_cat->fetch_assoc()){
        $category = $row_cat["name"];
        $st = "SELECT id FROM $tb WHERE category = '$category' and del_flag = 0 LIMIT 2";
        $st = $mysqli->prepare($st);
        $st->execute();
        $st->store_result();
        if($st->num_rows != 0){
?>            
                <li onclick="show_cat(<?php echo $row_cat["id"] ?>,this)"><?php echo $row_cat["name"] ?></li>
<?php
        }
    }
    
?>                
                
            </ul>
        </div>
<style>
    .cat_filter ul.product_weight li{
        padding:5px;
        width:auto;
    }
</style>
<script>
    function show_cat(catid,item){
        item.parentNode.getElementsByClassName('sd')[0].classList.remove('sd');
        item.classList.add('sd');
        prd_uls = document.getElementsByClassName('products');
        
        for(var i = 0;i<prd_uls.length;i++){
            if(catid > 0){
                prd_uls[i].classList.add('hide');
            }else{
                prd_uls[i].classList.remove('hide');
            }
        }
        if(catid > 0){
            prd_ul = document.getElementById('cat_'+catid);
            if(prd_ul){
                prd_ul.classList.remove('hide');
            }
        }
    }
</script>
        <p class="tac"></p>
<?php 
    $tb_cat = "categories";
    $st_cat = "SELECT id,name FROM $tb_cat WHERE section = 'products' AND name <> 'غیر قابل فروش' ORDER BY show_order ASC;";
    $st_cat = $mysqli->prepare($st_cat);
    if(!$st_cat->execute()){
        echo "E";
        exit;
    }
    $res_cat = $st_cat->get_result();
    while($row_cat = $res_cat->fetch_assoc()){
        $category = $row_cat["name"];
        $st = "SELECT id FROM $tb WHERE category = '$category' and del_flag = 0 LIMIT 2";
        $st = $mysqli->prepare($st);
        $st->execute();
        $st->store_result();
        if($st->num_rows != 0){
            $weight_hide = "";
            if($category == "پکیج ولنتاین"){
                $weight_hide = " hide ";
            }
        
?>
        <ul class="products" id="cat_<?php echo $row_cat["id"]; ?>">
        <?php
            $tb = "products";
            $tb2 = $tb."_price";
            $st = "SELECT $tb.id,$tb.name,$tb.file_name,$tb.first_img_id,$tb2.weight,$tb2.price,$tb2.start_time,$tb.state,$tb.sale_state FROM $tb CROSS JOIN $tb2 WHERE $tb.del_flag = 0 AND $tb.type = 'pack' AND $tb.id = $tb2.pid AND $tb2.start_time = (SELECT MAX($tb2.start_time) FROM $tb2 WHERE $tb.category = '$category' AND $tb2.pid = $tb.id ) ORDER BY $tb.show_order DESC,$tb.id DESC";
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
                $state = $row["state"];
                $img = $row["first_img_id"];
                $img_fn = getVarFromDB("content","file_name","id",$img);
                $ff = pathinfo($img_fn, PATHINFO_EXTENSION);
                $fn = pathinfo($img_fn, PATHINFO_FILENAME);
                
                $webp_path = "$dir/webp/$fn.webp";
                $webp_m_path = "$dir/webp/$fn.webp";
                $img_path = "$dir/$img_fn";
                $img_m_path = "$dir/$fn.$ff";
                
                $img_alt = getVarFromDB("content","name","id",$img);
                $weight = $row["weight"];
                $weight = get_str_index($weight,",")[1];
                $price_base = get_str_index($row["price"],",")[1];
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
                
                if($id == 73){
                    // $price='قیمت براساس باکس‌های انتخاب شده';
                }
        ?>
            <li class="product" id="<?php echo $id; ?>" itemscope itemtype="http://schema.org/Product">
                <a href="<?php echo $s."products/$file_name.php"; ?>" itemprop="url">
                    <div class="img">
                        <picture loading="lazy" >
                            <source type="image/webp" srcset="<?php  echo $webp_m_path; ?> 500w,<?php  echo $webp_path; ?>">
                            <img itemprop="image" src="<?php echo $img_m_path; ?>" srcset="<?php echo $img_m_path; ?> 500w,<?php echo $img_path; ?> " 
                            alt="<?php echo $img_alt; ?>" loading="lazy">
                        </picture>
                    </div>
                    <h3 class="tac" itemprop="name"><?php echo $name; ?></h3>
                    
                </a>

                <ul class="product_weight weight_options <?php echo $weight_hide; ?>">
                    <?php
                        for($i = 0;$i<sizeof($weight);$i++){
                    ?>
                    <li onclick="select_weight(this)"<?php if($i == 0){?> class="sd" <?php } ?>><?php echo $weight[$i]; ?> گرم</li>
                    <?php
                        }
                    ?>
                </ul>
                <?php
                    if((int)$state === 0){
                ?>  
                <span class="price <?php if($has_discount)echo " has_s "; ?>"  itemprop="offers" itemscope itemtype="http://schema.org/Offer" <?php if($id == 73){echo 'style="line-height:14px;"';}  ?> >
                    <?php for($i=0;$i<sizeof($price);$i++){ ?>
                    <span <?php if($i>0){echo ' class="hide" ';} ?> itemprop="price">
                        <?php
                            if($has_discount && isset($old_price[$i])){
                                echo "<s>&nbsp;&nbsp;".price_sep($old_price[$i])."&nbsp;&nbsp;</s><br>".price_sep($price[$i])." تومان";
                            }else{
                                if($id == 73){
                                    echo '<span style="font-size:10.5px;line-height:16px;">'.'قیمت براساس باکس‌های انتخابی'.'</span>';
                                }else{
                                    echo price_sep($price[$i])." تومان";
                                }
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
                <?php
                    }else{
                ?>
                <a class="cart_button red">
                    ناموجود
                </a>
                <?php
                    }
                ?>
            </li>
            <?php
            }
            ?>
        </ul>
<?php
        }
    }
?>
    </div>
<style type="text/css">
s{
    text-decoration: line-through;
    color:maroon;
}
span.price.has_s{
    line-height:20px;
}
</style>
    <div class="cut"></div>
    <div class="content">
        <div style="display:flex;flex-wrap:wrap;justify-content:center;">
            <div id="insta_holder" class="insta_holder fr" style="min-height:200px;">

            </div>

            <div class="comments fr">
                <h2 class="tac">نظرات</h2>
                <ul id="comment_box">
                <?php
                    $table = "comments";
                    $st = "SELECT name,comment,create_date,reply,reply_date FROM $table WHERE show_flag = 1 ORDER BY id DESC LIMIT 5";
                    $st = $mysqli->prepare($st);
                    if(!$st->execute()){
                        echo "E";
                        exit;
                    }
                    $res = $st->get_result();
                    while($row = $res->fetch_assoc()){
                        
                        $name = $row["name"];
                        $cm = $row["comment"];
                        $date = correctDate($row["create_date"]);
                        $reply = $row["reply"];
                        $reply_date = correctDate($row["reply_date"]);
                ?>
                    <li>
                        <b><?php echo $name; ?></b>
                        <?php echo $cm; ?>
                        <span class="person"><?php echo $date; ?></span>
                        <?php
                            if($reply){
                                ?>
                                <div class="cb"></div>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <picture class="admin_logo">
                                    <source type="image/webp" srcset="<?php echo $s;?>img/webp/logo-mob.webp 500w,<?php echo $s;?>img/webp/logo.webp">
                                    <img class="admin_logo" src="<?php echo $s;?>img/logo-mob.png"  srcset="<?php echo $s;?>img/logo-mob.png 500w,<?php echo $s;?>img/logo.png"/>
                                </picture>
                                <?php echo $reply;?>
                                <?php 
                            }
                        ?>
                    </li>
                <?php
                    }
                ?>
                </ul>
                <a class="btn" href="<?php echo $s; ?>comment/">شما هم نظر خود را بگویید</a>
            </div>
        </div>
    </div>
</main>
<script type="text/javascript">
    base_url = "<?php echo $s; ?>";
</script>
<script src="<?php echo asset_url('js/main.js'); ?>"></script>
<script src="<?php echo asset_url('js/cart.js'); ?>"></script>

<?php
    if($slides_no > 1){
?>
<style>
div.slider{
    animation-name:none;
    animation-duration: <?php echo $slide_time*$slides_no; ?>s;
    animation-fill-mode: both;
    animation-iteration-count: infinite;
    transform:translateX(-100%);
    transition:all 0s !important;
    animation-timing-function:linear;
}
div#slider.active div.slider{
    animation-name: example3;
}    

@keyframes example3 {
  0%   {transform:translateX(-100%);z-index:3;}
  <?php echo $p[1]; ?>%   {transform:translateX(0);z-index:3;}
  <?php echo $p[2]; ?>%  {transform:translateX(0);z-index:2;}
  <?php echo $p[3]; ?>%  {transform:translateX(100%);z-index:2;}
  68%  {z-index:0;display:none;}
  70%  {transform:translateX(-100%);}
  72%  {z-index:2;display:block;}
  100%  {z-index:2;}
}
</style>

<script src="<?php echo asset_url('js/slider.js'); ?>" type="text/javascript" async></script>
<?php
    }
?>

<script type="text/javascript">
base_url = '<?php echo $s; ?>';
</script>
<script src="<?php echo asset_url('js/lazy_load.js'); ?>" type="text/javascript" async></script>

<?php
        }
    }
?>
