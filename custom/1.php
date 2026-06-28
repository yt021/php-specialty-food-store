<?php

    if(isset($indexed)){

        if($indexed == 1){?>

<main>

    <div class="content">

        <h1 class="tac">جعبه انتخابی</h1>

        <p class="tac">در این بخش جعبه خودتونُ انتخاب کنید.</p>



        <ul class="products">



        <?php

            $tb = "products";

            $tb2 = $tb."_price";

            $st = "SELECT $tb.id,$tb.name,$tb.file_name,$tb.first_img_id,$tb2.weight,$tb2.price,$tb2.start_time,$tb.state,$tb.sale_state FROM $tb CROSS JOIN $tb2 WHERE $tb.category = 'پکیج ولنتاین' AND $tb.del_flag = 0 AND $tb.id = $tb2.pid AND $tb2.start_time = (SELECT MAX($tb2.start_time) FROM $tb2 WHERE $tb.type = 'box' AND $tb2.pid = $tb.id ) ORDER BY $tb.show_order DESC,$tb.id DESC";

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

        ?>

            <li class="product" id="<?php echo $id; ?>">



                    <div class="img">

                        <img src="<?php echo $s."content/$img_fn"; ?>" alt="<?php echo $img_alt; ?>"/>

                    </div>

                    <h3 class="tac"><?php echo $name; ?></h3>







                <ul class="product_weight weight_options <?php echo $weight_hide; ?>">

                    <?php

                        for($i = 0;$i<sizeof($weight);$i++){

                    ?>

                    <li <?php if($i == 0){?> class="sd" <?php } ?>><?php echo $weight[$i]; ?> پیمانه

                    </li>

                    <?php

                        }

                    ?>

                </ul>

                <span class="price <?php if($has_discount)echo " has_s "; ?>">

                    <?php for($i=0;$i<sizeof($price);$i++){ ?>

                    <span <?php if($i>0){echo ' class="hide" ';} ?>>

                        <?php

                            if($has_discount && isset($old_price[$i])){

                                echo "<s>&nbsp;&nbsp;".price_sep($old_price[$i])."&nbsp;&nbsp;</s><br>".price_sep($price[$i]);
                            }else{
                                echo price_sep($price[$i]);
                            }
                        ?>

                         تومان

                    </span>

                    <?php

                        }

                    ?>

                </span>

                <?php

                    if((int)$state === 0){

                ?>



                <form action="<?php echo $URL; ?>" method="post">

                    <input type="hidden" class="hide" name="box" id="box" value="<?php echo $id; ?>">

                <input type="submit" class="cart_button" name="submit" value="انتخاب">

                </form>

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







</div>

</main>

<style type="text/css">

ul.products li.product{

    border:2px solid white;

}

ul.products li.product.selected{

    border:2px solid maroon;

    border-radius:5px;

}
s{
    text-decoration:line-through;
    color:maroon;
}
span.price.has_s{
    line-height:20px;
}

</style>

<script type="text/javascript">



</script>

<?php

        }

    }

?>
