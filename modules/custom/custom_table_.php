<?php
    if(isset($_SESSION["custom"]) && is_a($_SESSION["custom"],"order_box") && $_SESSION["custom"]->content->number()>0){
?>

<table class="cart account">
    <thead>
        <tr>
            <th class="product-name">نام</th>
            <th class="product-weight">وزن</th>
            <th class="product-quantity">تعداد</th>
            <th class="product-price">مبلغ واحد</th>
            <th class="product-subtotal">مجموع</th>
        </tr>
    </thead>
    <tbody id="cart_tbody">
<tr class="rgba">
    
    <td class="product-name" data-title="Product">
        <a href="">
            <?php
                echo $_SESSION["custom"]->name();
            ?>
        </a>
    </td>
    

    <td colspan="3" class="product-weight weight_options" data-title="Weight">
        <span class="">
                ظرفیت: <?php echo $_SESSION["custom"]->capacity; ?> پیمانه
        </span>
    </td>
    
    <td class="product-subtotal" data-title="Total">
        <span class="">
            <?php echo price_sep($_SESSION["custom"]->price(true)); ?>
        </span>
    </td>
</tr>
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
            <?php echo $item->no; ?>
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
            <?php echo price_sep($_SESSION["custom"]->price()); ?>
        </span>
    </td>
</tr>

    </tbody>
</table>

<?php
    }else{
        echo "جعبه خالی است، محتوای آن را انتخاب کنید.";
    }
?>
