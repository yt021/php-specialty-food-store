<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(isset($box))
?>
<div class="print_page_holder">
<div class="print_page pp">
    <div class="right">
        <div class="logo">
            <img src="<?php echo $s; ?>img/logo_bw.png" />
        </div>
        <div class="sender_info">
            <h2><?php echo $box->name; ?></h2>
            <br>
            جعبه مخلوط انتخابی با ظرفیت <?php echo $box->capacity; ?> پیمانه
        </div>
        
        <div class="cb"></div>
        <div class="cut"></div>
        <div class="rec_info">
            <table class="cart account">
                <thead>
                    <tr>
                        
                        <th>ردیف</th>
                        <th>نام</th>
                        <th>وزن</th>
                        <th>تعداد</th>
                    </tr>
                </thead>
                <tbody id="cart_tbody">
                    <?php
                        for($i=0;$i<sizeof($box->content);$i++){
                            $item = $box->content[$i];
                    ?>
                    <tr <?php if($i%2 == 1){echo 'class="rgba"';} ?>>
                        <td>
                            
                                <?php
                                    echo $i+1;
                                ?>
                        </td>
                        <td>
                                <?php
                                    echo $item->name;
                                ?>
                        </td>
                        

                        <td>
                            
                                <?php echo $item->weight; ?> گرم
                        </td>
                        

                        <td>
                                <?php echo $item->no; ?>
                        </td>
                    </tr>
                    <?php
                        }
                    ?>
                    <tr <?php if($i%2 == 1){echo 'class="rgba"';} ?>>
                        <td></td>
                        <td>
                                مجموع
                        </td>
                        

                        <td>

                                    <?php echo $box->weight; ?> گرم

                        </td>
                        <td>
                                <?php echo $box->capacity; ?>

                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        
        <div class="social">
            <span class="icon-ig"></span> aban_fruit
            <br>
            <img src="<?php echo $s; ?>img/www.png" style="position:relative;left:-0.5mm;width:5mm;height:5mm;float:left;margin-right:0mm;" /> www.abanfruit.com
        </div>
    </div>
    <div class="left">
        کد سفارش: <?php echo $oid; ?>
        <br>
        تاریخ ثبت: <?php echo correctDate_rqm(getVarFromDB("orders","create_date","id",$oid)); ?>
        <br>
<?php

?>
<?php
//    if(getVarFromDB("orders","admin_state","id",$oid) == 1){
//        echo "شناسه شبکه اجتماعی: ".getVarFromDB("users","social","id",$uid);
//    }else{
//        echo "سفارش مستقیم از سایت";
//    }
?>
        
    </div>
</div>
</div>
<?php
        }
    }
?>