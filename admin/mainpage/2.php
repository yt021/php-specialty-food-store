<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <div class="ssrv">
                <div class="ssrv_title">مشخصات خریدار</div>        
                <div class="ssrv_dtl">
                    <form>
                        <div class="form fr">
<?php
    $oid = $_SESSION[$cf]->id;
    include_once $bu."modules/cart/cart_funcs.php";
    $uid = getVarFromDB($tb,"uid","id",$oid);
    $aid = getVarFromDB($tb,"aid","id",$oid);
    $user = db_get_user($uid,$aid);
    ?>  
    
    <?php

    echo '              
                        <div class="form_item">
                            <label>نام:</label>
                            <input disabled type="text" value="'.$user->data()["name"].'">
                        </div>
                        <div class="form_item">
                            <label>تلفن همراه:</label>
                            <input disabled type="text" value="'.$user->data()["tel"].'">
                        </div>
                        <div class="form_item">
                            <label>رایانامه:</label>
                            <input disabled type="text" value="'.$user->data()["email"].'">
                        </div>
                    ';
?>
                        <a class="btn" onclick="sub_show('uid','<?php echo $_SESSION["$cf"]->sub_id;?>','clients/')">مشاهده حساب</a> 
                        </div>
                    </form>
                </div>
            </div>
            <div class="ssrv">
                <div class="ssrv_title">آدرس خریدار</div>        
                <div class="ssrv_dtl">
                    <form>
                        <div class="form fr">
<?php
    echo '              
                        <div class="form_item">
                            <label>استان:</label>
                            <input disabled type="text" value="'.$user->data()["address"]["county"].'">
                        </div>
                        <div class="form_item">
                            <label>شهر:</label>
                            <input disabled type="text" value="'.$user->data()["address"]["city"].'">
                        </div>
                        <div class="form_item">
                            <label>آدرس:</label>
                            <input disabled type="text" value="'.$user->data()["address"]["address"].'">
                        </div>
                        <div class="form_item">
                            <label>کد پستی:</label>
                            <input disabled type="text" value="'.$user->data()["address"]["post_code"].'">
                        </div>
                    ';
?>
                        </div>
                    </form>
                </div>
            </div><br><br>
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
    $cart = db_get_cart($oid);
    for($i=0;$i<sizeof($cart->orders);$i++){
        $item = $cart->orders[$i];
        $name = getVarFromDB("products","name","id",$item->pid);
?>
                <tr>
                    <td>
                        <?php echo ($i+1);?> 
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
                        <?php echo price_sep($item->price());?>
                    </td>
                    <td>
                        <?php echo price_sep($item->price()*$item->no); ?>
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
if($submit_text = getVarFromDB($atb,"submit_text","flag",$_SESSION[$cf]->state)){
?>
    </div>  
</div>          
<div class="cut w100p"></div>
    <div id="sect2" class="sect">
        <div class="middle container">
            <a class="btn submit" onclick="sub_show('sub','state')"><?php echo $submit_text; ?></a>
<?php
 }      
?>

<?php
        }
    }
?>
