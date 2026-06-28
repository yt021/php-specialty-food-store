<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(isset($user) && isset($address) && isset($cart) && isset($sales))
?>
<div class="print_page_holder">
<div class="print_page pp">
    <div class="right">
        <div class="logo">
            <img src="<?php echo $s; ?>img/logo_bw.png" />
        </div>
        <div class="sender_info">
            نام فرستنده:  میوه خشک آبان
            <br>
            نشانی فرستنده: <?php echo getVarFromDB("contact_info","value","id",5);?>
            <br>
            شماره تماس:  09000000000
        </div>
        
        <div class="cb"></div>
        <div class="cut"></div>
        <div class="rec_info">
            نام گیرنده: <?php if(isset($address->data()["rec_name"]) && $address->data()["rec_name"])echo $address->data()["rec_name"];else echo $user->data()["name"];?>
            <br>
            نشانی گیرنده: <?php echo $address->full_address(); ?>
            <br>
            کد پستی:  <?php echo $address->data()["post_code"]; ?>
            <br>
            تلفن گیرنده:  <?php if(isset($address->data()["rec_tel"])  && $address->data()["rec_tel"])echo $address->data()["rec_tel"];else echo $user->data()["tel"];?>
            <br>
            <?php
                if($address->data()["janitor"]){
            ?>
                در صورت عدم حضور تحویل نگهبان/سرایدار شود.
                <br>
            <?php
                }
            ?>
            <?php
                if($address->data()["rec_tel_2"]){
            ?>
                تلفن ثابت: <?php echo $address->data()["rec_tel_2"]; ?>
                <br>
            <?php
                }
            ?>
            <?php
                if($rec_shift){
                    echo correct_rec_time_client($rec_date,$rec_shift);
                }
            ?>
        </div>
<?php
    
    $class="";
    if(sizeof($cart->orders)>13){
        $class=" high_no ";
    }
?>
        <div class="left_info <?php echo $class; ?>">
            <h4 class="tac">
                کد سفارش: <?php echo ($unified_id && $unified_id !== '' && !ctype_digit($unified_id)) ? $unified_id : $oid; ?>
            </h4>
            <div class="table_holder">
                <table>
                    <caption class="tac">لیست محصولات ارسالی</caption>
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
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        
                        for($i=0;$i<sizeof($cart->orders);$i++){
                            $item = $cart->orders[$i];
                            $name = getVarFromDB("products","name","id",$item->pid);
                    ?>
                        <tr>
                            <td>
                                <?php echo $i+1; ?>
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
                        </tr>
                    <?php
                        }
                    ?>
                    <?php
                        foreach($sales->gifts as $sale){
                            $name = $sale->name;
                    ?>
                        <tr>
                            <td>
                                <?php echo $i+1; ?>
                            </td>
                            <td>
                                <?php echo $name; ?>
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                    <?php
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="social">
            <span class="icon-ig"></span> aban_fruit
            <br>
            <img src="<?php echo $s; ?>img/www.png"  /> www.abanfruit.com
        </div>
        
<?php
    // if($address->data()["county"] == "شهر تهران" ){
    $number = $oid;
    include($bu."admin/orders/send_detail_barcode.php");
    // }
?>
        
    </div>
    <div class="left">
        کد سفارش: <?php echo ($unified_id && $unified_id !== '' && !ctype_digit($unified_id)) ? $unified_id : $oid; ?>
        <br>
        تاریخ ثبت: <?php echo correctDate_rqm(getVarFromDB("orders","create_date","id",$oid)); ?>
        <br>
<?php
        if($rec_shift){
            echo correct_rec_time_client($rec_date_rqm,$rec_shift)."<br>";
            if($send_date){
                echo "زمان ارسال: $send_date<br>";
            }
        }
?>
<?php
//    if(getVarFromDB("orders","admin_state","id",$oid) == 1){
//        echo "شناسه شبکه اجتماعی: ".getVarFromDB("users","social","id",$uid);
//    }else{
//        echo "سفارش مستقیم از سایت";
//    }
?>
        <div class="order_detail">
            توضیحات:
            <br>
            <?php echo getVarFromDB("orders","order_detail","id",$oid); ?>
        </div>
        <div class="order_receipt">
            رسید تحویل سفارش (میوه خشک آبان):
            <br>
            نام گیرنده: <?php if(isset($address->data()["rec_name"]) && $address->data()["rec_name"])echo $address->data()["rec_name"];else echo $user->data()["name"];?>
            <br>
            کد سفارش: <?php echo ($unified_id && $unified_id !== '' && !ctype_digit($unified_id)) ? $unified_id : $oid; ?>
            <br>
            امضا:
            <div class="bottom_site">
                www.abanfruit.com
            </div>
        </div>
    </div>
</div>
</div>
<?php
        }
    }
?>
