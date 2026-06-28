<table id="cart_table" class="cart">
    <thead>
        <tr>
            <th class="product-name">نام محصول</th>
            <th class="product-weight">وزن</th>
            <th class="product-type">دسته‌بندی</th>
            <th class="product-quantity">تعداد</th>
            <th class="product-price">مبلغ واحد</th>
            <th class="product-price-discount">قیمت با تخفیف</th>
            <th class="product-subtotal">مجموع</th>
            <th class="product-remove">&nbsp;</th>
        </tr>
    </thead>
    <tbody id="cart_tbody">

<?php
    $cart_base_total = 0;
    $cart_final_total = 0;
    for($i=0;$i<sizeof($_SESSION["cart"]->orders);$i++){
        $item = $_SESSION["cart"]->orders[$i];
        $table = "products_price";
        $weight = getVarFromDB('products_price',"weight","pid",$item->pid,'id DESC');
        $weight = get_str_index($weight,",")[1];
        $cate = getVarFromDB("products","category","id",$item->pid);
        $type = getVarFromDB("products","type","id",$item->pid);
        $w_hide = '';
        if($cate == 'پکیج ولنتاین'){
            $w_hide = '  ';
        }
        $final_unit_price = (int)$item->price();
        $base_unit_price = $final_unit_price;
        if(function_exists("cart_summary_item_base_unit_price")){
            $base_unit_price = (int)cart_summary_item_base_unit_price($item,date("Y-m-d H:i:s"));
        }
        if($base_unit_price <= 0 || $base_unit_price < $final_unit_price){
            $base_unit_price = $final_unit_price;
        }
        $discount_unit_price = ($final_unit_price < $base_unit_price) ? $final_unit_price : null;
        $line_total_price = $final_unit_price * (int)$item->no;
        $cart_base_total += $base_unit_price * (int)$item->no;
        $cart_final_total += $line_total_price;
?>
<tr <?php if($i%2 == 1){echo 'class="rgba"';} echo 'id="'.$item->pid.'"';echo 'name="'.$item->opbid().'"'; ?>>
    
    <td class="product-name" data-title="Product">
        <a href="">
            <?php
                echo $item->name();
            ?>
        </a>
    </td>
    
    <?php
        switch($type){
            case "pack":
        
    ?>
    <td class="product-weight weight_options" data-title="Weight">
        <ul class="product_weight weight_show <?php echo $w_hide; ?>">
            <li >
                <?php echo $item->weight(); ?> گرم
            </li>
            <?php if($cate != "پکیج ولنتاین"){ 
            ?>
            <li class="weight_select" onclick="open_product_weight(this)">
                تغییر وزن
            </li>
        </ul>
        <ul class="product_weight hide weight_options <?php echo $w_hide; ?>">
            <?php
                for($wi = 0;$wi<sizeof($weight);$wi++){
            ?>
            <li onclick="select_weight(this)"<?php if($weight[$wi] == $item->weight){?> class="sd cur_weight" <?php } ?>><?php echo $weight[$wi]; ?> گرم
            </li>
            <?php
                }
            ?><br>
            <li class="weight_select" onclick="change_weight_cart(this.parentNode.parentNode)">
                ثبت
            </li>
            <?php }else{
            ?>
            <ul class="hide weight_options <?php echo $w_hide; ?>">
                <li class="sd cur_weight"><?php echo $item->weight(); ?> گرم</li>
            </ul>
            <?php
            } ?>
        </ul>
    </td>
    <?php
                break;
            case "box":
    ?>
    <td class="product-weight weight_options" data-title="Weight">
        <ul class="product_weight weight_show">
            <li class="sd">
                <?php echo $item->weight(); ?> گرم
            </li>
        </ul>
    </td>        
    <?php
                break;
        }
    ?>
        
    <td class="product-name" data-title="Type">
        <a href="">
            <?php
                
                echo $cate;
            ?>
        </a>
    </td>
    

    <td class="product-quantity" data-title="Quantity">
        <span class="">
            <span class="s_but" onclick="inc_cart(this.parentNode.parentNode)">+</span>
            <?php echo $item->no; ?>
            <span class="s_but" onclick="dec_cart(this.parentNode.parentNode)">-</span>
        </span>
    </td>
    
    <td class="product-price" data-title="Price">
        <span class="">
            <?php echo price_sep($base_unit_price); ?>
        </span>
    </td>
    
    <td class="product-price-discount" data-title="Discount Price">
        <span class="">
            <?php echo ($discount_unit_price !== null) ? price_sep($discount_unit_price) : "-"; ?>
        </span>
    </td>
    
    <td class="product-subtotal" data-title="Total">
        <span class="">
            <?php echo price_sep($line_total_price); ?>
        </span>
    </td>
    <td class="product-remove" >
        <a class="remove" aria-label="Remove this item" data-product_id="63321" data-product_sku="" onclick="del_from_cart(this.parentNode)">×</a>
    </td>
    
</tr>
<?php
    if($type == "box"){
?>
<tr <?php if($i%2 == 1){echo 'class="rgba"';}?>>
    <td></td>
    <td colspan="7" style="text-align: right;">
        شامل: 
        <?php echo $item->content->list_items("عدد"); ?>
    </td>
</tr>
<?php
    }
?>
<?php
    }
?>
<tr <?php if($i%2 == 1){echo 'class="rgba"';} ?>>
    
    <td class="product-name" data-title="Product">
        <a href="">
            مجموع
        </a>
    </td>
    

    <td class="product-weight weight_options" data-title="Weight">
        <ul class="product_weight weight_show">
            <li>
                <?php echo $_SESSION["cart"]->weight(); ?> گرم
            </li>
        </ul>
    </td>
    <td class="product-name" data-title="Type">
    
    </td>
    <td class="product-quantity" data-title="Quantity">
        <span class="">
            <?php echo $_SESSION["cart"]->number(); ?>
        </span>
    </td>
    
    <td class="product-price" data-title="Price">
        <span class="">
            -
        </span>
    </td>
    <td class="product-price-discount" data-title="Discount Price">
        <span class="">
            <?php echo ($cart_final_total < $cart_base_total) ? price_sep($cart_final_total) : "-"; ?>
        </span>
    </td>
    
    <td class="product-subtotal" data-title="Total">
        <span class="">
            <?php echo price_sep($cart_final_total); ?>
        </span>
    </td>
    <td class="product-remove" >
    
    </td>
    
</tr>

    </tbody>
</table>

<div class="cart_for_phone">
    <div class="cfp_item">
        <h3>
            سبد خرید
        </h3>
        <div class="cfpi_weight">
            وزن کل: <?php echo $_SESSION["cart"]->weight(); ?> گرم
        </div>
        <div class="cfpi_price">
        </div>
        <div class="cb"></div>
        <div class="cfpi_number">
            تعداد کل: <?php echo $_SESSION["cart"]->number(); ?>
        </div>
        <div class="cfpi_total_price">
            قیمت کل: <?php echo price_sep($_SESSION["cart"]->price()); ?> تومان
        </div>
    </div>
</div>
<div class="cart_for_phone">
<?php
    for($i=0;$i<sizeof($_SESSION["cart"]->orders);$i++){
        $item = $_SESSION["cart"]->orders[$i];
        $table = "products_price";
        $weight = getVarFromDB('products_price',"weight","pid",$item->pid,'id DESC');
        $weight = get_str_index($weight,",")[1];
        $cate = getVarFromDB("products","category","id",$item->pid);
        $type = getVarFromDB("products","type","id",$item->pid);
        $final_unit_price_mobile = (int)$item->price();
        $base_unit_price_mobile = $final_unit_price_mobile;
        if(function_exists("cart_summary_item_base_unit_price")){
            $base_unit_price_mobile = (int)cart_summary_item_base_unit_price($item,date("Y-m-d H:i:s"));
        }
        if($base_unit_price_mobile <= 0 || $base_unit_price_mobile < $final_unit_price_mobile){
            $base_unit_price_mobile = $final_unit_price_mobile;
        }
        $discount_unit_price_mobile = ($final_unit_price_mobile < $base_unit_price_mobile) ? $final_unit_price_mobile : null;
        $line_total_price_mobile = $final_unit_price_mobile * (int)$item->no;
?>
    <div class="cfp_item" <?php echo 'id="'.$item->pid.'" ';echo ' name="'.$item->opbid().'"';if($type == 'box')echo 'style="height:auto;"' ?>>
            <h3>
                <?php
                    
                    echo $item->name();
                ?>
            </h3>
<?php
    switch($type){
        case "pack":
?>
            <div class="cfpi_weight product-weight weight_options">
                <ul class="product_weight weight_show">
                    <li>
                        <?php echo $item->weight(); ?> گرم
                    </li>
                    <?php if($cate != "پکیج ولنتاین"){ ?>
                    <li class="weight_select" onclick="open_product_weight(this)">
                        تغییر وزن
                    </li>
                </ul>
                <ul class="product_weight hide weight_options">
                    <?php
                        for($wi = 0;$wi<sizeof($weight);$wi++){
                    ?>
                    <li onclick="select_weight(this)"<?php if($weight[$wi] == $item->weight){?> class="sd cur_weight" <?php } ?>><?php echo $weight[$wi]; ?> گرم
                    </li>
                    <?php
                        }
                    ?>
                    <li class="weight_select" onclick="change_weight_cart(this.parentNode.parentNode)">
                        ثبت
                    </li>
                    <?php } ?>
                </ul>
            </div>
<?php
        break;
        case "box":
?>
            <div class="cfpi_weight product-weight weight_options">
                <ul class="product_weight weight_show">
                    <li class="sd">
                        <?php echo $item->weight(); ?> گرم
                    </li>
                </ul>
                
            </div>
<?php
    }
?>
            <div class="cfpi_price">
                قیمت واحد: &nbsp;
                <span class="">
                    <?php echo price_sep($base_unit_price_mobile); ?>
                </span>
                <br>
                قیمت با تخفیف: &nbsp;
                <span class="">
                    <?php echo ($discount_unit_price_mobile !== null) ? price_sep($discount_unit_price_mobile) : "-"; ?>
                </span>
            </div>
            <div class="cb"></div>
            <div class="cfpi_number">
                تعداد: &nbsp;
                <span>
                    <span class="s_but" onclick="inc_cart(this.parentNode.parentNode)">+</span>
                    <?php echo $item->no; ?>
                    <span class="s_but" onclick="dec_cart(this.parentNode.parentNode)">-</span>
                </span>
            </div>
            <div class="cfpi_total_price">
                قیمت کل: &nbsp;
                <span>
                    <?php echo price_sep($line_total_price_mobile); ?> تومان
                </span>
            </div>
            <div class="cfpi_remove" >
                <span class="icon-x" onclick="del_from_cart(this.parentNode)"></span>
            </div>
        </div>
<?php
    }
?>
</div>
