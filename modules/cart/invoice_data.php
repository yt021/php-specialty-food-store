<?php

    if(isset($indexed)){

        if($indexed == 1){?>



<?php

    $cart = new cart_read($oid);

    $uid = getVarFromDB("orders","uid","id",$oid);

    $aid = getVarFromDB("orders","aid","id",$oid);

    $user = db_get_user($uid);

    $address = db_get_address($aid);

    

    $sales = new sales("read",$oid);

    

    

    $order_date = correctDate($cart->create_date);

    $unified_id = getVarFromDB("orders","unified_id","id",$oid);

//    Finance Data

{

    $fields[1] = ["pay_price","sale_total","cart_price","cart_pure"];

    $fields[2] = ["cart_sale","send_sale"];

    $labels = array(

        "full_price"=>"مجموع فاکتور",

        "pay_price"=>"مبلغ قابل پرداخت",

        "sale_total"=>"مجموع تخفیف",

        "cart_price"=>"مجموع سبد خرید",

        "cart_pure"=>"مجموع سبد خرید(خالص)",

        "cart_sale"=>"تخفیف سبد خرید",

        "send_cost"=>"هزینه ارسال",

        "send_sale"=>"تخفیف ارسال"

    );

    foreach($fields[1] as $f){

        $invoice_value[$f] = getVarFromDB("orders",$f,"id",$oid);

    }

    $invoice_value["send_cost"] = 

    ($invoice_value["pay_price"] - $invoice_value["cart_pure"]) + 

    ($invoice_value["sale_total"] - ($invoice_value["cart_price"] - $invoice_value["cart_pure"]));

    $invoice_value["full_price"] = 

    $invoice_value["cart_price"] + 

    $invoice_value["send_cost"];

    

    $invoice_value["cart_sale"] = $invoice_value["cart_price"] - $invoice_value["cart_pure"];

    $invoice_value["send_sale"] = $invoice_value["sale_total"] - $invoice_value["cart_sale"];

}

    

    $clear_tr = '<tr><td> &nbsp;</td><td> &nbsp;</td><td> &nbsp;</td><td> &nbsp;</td><td>&nbsp;</td><td> &nbsp;</td></tr>';

?>

<meta http-equiv="content-type" content="text/html; charset=UTF-8">

<meta charset="UTF-8">

<link rel="stylesheet" href="<?php echo asset_url('css/invoice.css'); ?>" type="text/css" media="all">

<link rel="stylesheet" href="<?php echo asset_url('css/font_icon.css'); ?>" type="text/css" media="all">



<div class="print_page pp">

    <div class="right">

        <div class="logo">

            <img src="<?php echo $s; ?>img/logo.png" />

        </div>

        <div class="sender_info">

            <!--<h3>صورتحساب فروش کالا و خدمات</h3>-->

            <div>

            خریدار: <?php echo $user->data()["name"];?>

            &nbsp;&nbsp;&nbsp;&nbsp;

            تلفن:  <?php echo $user->data()["tel"];?>

            <br>

            نشانی: <?php echo $address->full_address(); ?> &nbsp;&nbsp;&nbsp;&nbsp;

            کد پستی:  <?php echo $address->data()["post_code"]; ?>



            </div>

        </div>

        <div class="order_detail">

            تاریخ: <?php echo $order_date; ?>

            <br>

            کد سفارش: <?php echo ($unified_id && $unified_id !== '' && $unified_id !== '0') ? $unified_id : $oid; ?>

        </div>

        

        

        <div class="cb"></div>

        <div class="cut"></div>

        <div class="left_info">

            <div class="table_holder">

                <table>

                    <!--<caption class="tac">مشخصات کالای مورد معامله</caption>-->

                    <thead>

                        <tr>

                            <th>

                                ردیف

                            </th>

                            <th>

                                نام محصول

                            </th>

                            <th>

                                وزن (گرم)

                            </th>

                            <th>

                                تعداد

                            </th>

                            <th>

                                قیمت واحد

                            </th>

                            <th>

                                قیمت کل

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                        $i = 0;

                        foreach($cart->orders as $item){

                            $i++;

                            $name = $item->name;

                    ?>

                        <tr>

                            <td>

                                <?php echo $i; ?>

                            </td>

                            <td>

                                <?php echo $name; ?>

                            </td>

                            <td>

                                <?php echo $item->weight;?>

                            </td>

                            <td>

                                <?php echo $item->no;?>

                            </td>

                            <td>

                                <?php echo price_sep($item->price);?>

                            </td>

                            <td>

                                <?php echo price_sep($item->no*$item->price);?>

                            </td>

                        </tr>

                    <?php

                        if(is_a($item,'order_box_read')){

                    ?>

                        <tr>

                            <td></td>

                            <td colspan="4" style="text-align: right;padding-right:5px;">

                                <?php

                                    echo $item->c_list();

                                ?>

                            </td>

                            <td></td>

                            

                        </tr>

                    <?php

                        }

                    ?>

                    <?php

                        }

                    ?>

                        <?php echo $clear_tr; ?>

                        <tr class="bb">

                            <td colspan="2">

                                مجموع سبد خرید

                            </td>

                            <td></td>

                            <td></td>

                            <td></td>

                            <td>

                                <?php

                                    echo price_sep($invoice_value["cart_price"]);

                                ?>

                            </td>

                        </tr>

                        <tr class="bb">

                            <td colspan="2">

                                هزینه ارسال

                            </td>

                            <td></td>

                            <td></td>

                            <td></td>

                            <td>

                                <?php

                                    echo price_sep($invoice_value["send_cost"]);

                                ?>

                            </td>

                        </tr>

                        <?php echo $clear_tr; ?>

                        <tr class="bb">

                            <td colspan="2">

                                <b>جمع کل</b>

                            </td>

                            <td>

                                <?php echo $cart->weight(); ?>

                            </td>

                            <td>

                                <?php echo $cart->number(); ?>

                            </td>

                            <td>

                            </td>

                            <td>

                                <?php echo price_sep($invoice_value["full_price"]); ?>

                            </td>

                        </tr>

                        <?php echo $clear_tr; ?>

<?php

    if($invoice_value["cart_sale"]>0){

?>

                        <tr class="bb">

                            <td colspan="2">

                                تخفیف سبد خرید

                            </td>

                            <td></td>

                            <td></td>

                            <td></td>

                            <td>

                                <?php echo price_sep($invoice_value["cart_sale"]); ?>

                            </td>

                        </tr>

<?php

    }

        foreach($sales->gifts as $sale){

        $name = $sale->name;

?>

                        <tr class="bb">

                            <td>

                                هدیه 

                            </td>

                            <td><?php echo $sale->name; ?></td>

                            <td></td>

                            <td></td>

                            <td></td>

                            <td>

                                هدیه

                            </td>

                        </tr>

<?php

        }

        echo $clear_tr;

    if($invoice_value["send_sale"]>0){

?>

                        <tr class="bb">

                            <td colspan="2">

                                تخفیف ارسال

                            </td>

                            <td></td>

                            <td></td>

                            <td></td>

                            <td>

                                <?php echo price_sep($invoice_value["send_sale"]); ?>

                            </td>

                        </tr>

<?php

    }

    if($invoice_value["sale_total"]>0){

?>

                        <?php echo $clear_tr; ?>

                        <tr class="bb">

                            <td colspan="2">

                                <b>مجموع تخفیف</b>

                            </td>

                            <td></td>

                            <td></td>

                            <td></td>

                            <td>

                                <?php echo price_sep($invoice_value["sale_total"]); ?>

                            </td>

                        </tr>

                        <?php echo $clear_tr; ?>

<?php

    }

?>                  

                        <tr>

                            <td colspan="2">

                                <b>مبلغ قابل پرداخت</b>

                            </td>

                            <td></td>

                            <td></td>

                            <td></td>

                            <td>

                                <?php echo price_sep($invoice_value["pay_price"]); ?>

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

        

        <div class="social">

            <span class="icon-ig"></span> aban_fruit

            <br>

            <img src="<?php echo $s; ?>img/www.png" style="position:relative;left:-0.5mm;width:5mm;height:5mm;float:left;margin-right:0mm;" /> www.abanfruit.com

        </div>

    </div>

</div>

<?php

        }

    }

?>

