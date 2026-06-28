<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if($_SESSION[$cf]->state == "order"){
?>
        <h2 class="tac">سبد خرید</h2><br><br>
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th>
                        شناسه محصول
                    </th>
                    <th>
                        نام محصول
                    </th>
                    <th>
                        وزن 
                    </th>
                    <th>
                        تعداد
                    </th>
                    <th>
                        مبلغ واحد
                    </th>
                    <th>
                        مجموع
                    </th>
                </tr>
            </thead>
            <tbody>
<?php
    $cart = new cart_read($_SESSION[$cf]->sub_id);
    $i = 0;
    foreach($cart->orders as $item){
        $i++;
        $name = getVarFromDB("products","name","id",$item->pid);
?>
                <tr>
                    <td>
                        <?php echo $i;?> 
                    </td>
                    <td>
                        <?php echo $item->pid;?>
                    </td>
                    <td>
                        <?php echo $name;?>
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
                        <?php echo price_sep($item->price*$item->no); ?>
                    </td>
                </tr>
<?php
    }
?> 
                <!--<tr>
                    <td colspan="7"> &nbsp;</td>
                </tr>-->
                <tr>
                    <td colspan="3">
                        
                    مجموع
                        
                    </td>
                    <td>
                        <?php echo $cart->weight();?>
                    </td>
                    <td>
                        <?php echo $cart->number()?>
                    </td>
                    <td>
                        
                    </td>
                    <td>
                        <?php echo price_sep($cart->price());?>
                    </td>
                </tr>
            </tbody>
        </table>
<?php
    } 
?>
<?php
        }
    }
?>
