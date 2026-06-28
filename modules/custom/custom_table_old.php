<?php
    if(isset($_SESSION["custom"]) && is_a($_SESSION["custom"],"order_box") && $_SESSION["custom"]->content->number()>0){
?>

<table id="cart_table" class="cart">
    <thead>
        <tr>
            <th class="product-name">نام</th>
            <th class="product-weight">وزن</th>
            <th class="product-quantity">تعداد</th>
            <th class="product-price">مبلغ واحد</th>
            <th class="product-subtotal">مجموع</th>
            <th class="product-remove">&nbsp;</th>
        </tr>
    </thead>
    <tbody id="cart_tbody">

<?php
    for($i=0;$i<sizeof($_SESSION["custom"]->content->orders);$i++){
        $item = $_SESSION["custom"]->content->orders[$i];
?>
<tr <?php if($i%2 == 1){echo 'class="rgba"';} echo 'id="'.$item->pid.'"'; ?>>
    
    <td class="product-name" data-title="Product">
        <a href="">
            <?php
                echo $item->name();
            ?>
        </a>
    </td>
    

    <td class="product-weight weight_options" data-title="Weight">
        <ul class="product_weight weight_show">
            <li>
                <?php echo $item->weight; ?> گرم
            </li>
        </ul>
    </td>
    

    <td class="product-quantity" data-title="Quantity">
        <span class="">
            <span class="s_but" onclick="inc_box(this.parentNode.parentNode)">+</span>
            <?php echo $item->no; ?>
            <span class="s_but" onclick="dec_box(this.parentNode.parentNode)">-</span>
        </span>
    </td>
    
    <td class="product-price" data-title="Price">
        <span class="">
            <?php echo price_sep($item->price()); ?>
        </span>
    </td>
    
    <td class="product-subtotal" data-title="Total">
        <span class="">
            <?php echo price_sep($item->price()*$item->no); ?>
        </span>
    </td>
    <td class="product-remove" >
        <a class="remove" aria-label="Remove this item" data-product_id="63321" data-product_sku="" onclick="del_from_box(this.parentNode)">×</a>
    </td>
    
</tr>
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
                <?php echo $_SESSION["custom"]->content->weight(); ?> گرم
            </li>
        </ul>
    </td>
    <td class="product-quantity" data-title="Quantity">
        <span class="">
            <?php echo $_SESSION["custom"]->content->number(); ?>
        </span>
    </td>
    
    <td class="product-price" data-title="Price">

    </td>
    
    <td class="product-subtotal" data-title="Total">
        <span class="">
            <?php echo price_sep($_SESSION["custom"]->content->price()); ?>
        </span>
    </td>
    <td class="product-remove" >
    
    </td>
    
</tr>

    </tbody>
</table>

<?php
    }else{
        echo "جعبه خالی است، محتوای آن را انتخاب کنید.";
    }
?>
