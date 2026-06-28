<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <div class="ssrv">
            <div class="ssrv_title">مشخصات مشتری</div>        
            <div class="ssrv_dtl">
                <form action="<?php echo $URL; ?>" method="post">
                    <div class="form fr">
<?php
    
    include_once $bu."modules/cart/cart_funcs.php";
    
    ?>  
    
    <?php
    $social = getVarFromDB($tb,"social","id",$_SESSION[$cf]->id);
    echo '              
                        <div class="form_item">
                            <label>نام:</label>
                            <input disabled name="name" type="text" value="'.$user->data()["name"].'">
                        </div>
                        <div class="form_item">
                            <label>تلفن همراه:</label>
                            <input disabled name="tel" type="text" value="'.$user->data()["tel"].'">
                        </div>
                        <div class="form_item">
                            <label>رایانامه:</label>
                            <input disabled name="email" type="text" value="'.$user->data()["email"].'">
                        </div>
                        <div class="form_item">
                            <label>شناسه شبکه اجتماعی:</label>
                            <input disabled name="social" type="text" value="'.$social.'">
                        </div>
                        <div class="form_item hide">
                            <label>گذرواژه:</label>
                            <input disabled name="password" type="text" >
                        </div>
                    ';
?>

                    </div>
                </div>
            </div>
<?php
    if($_SESSION["a_logged"]->get_level() >= 1){
?>
                        <a class="btn" onclick="open_edit(this)">تغییر</a>
                        <input type="submit" id="submit_btn" name="edit" class="hide btn" value="ثبت تغییر">
<?php  
    }
?>
            </form>
            <div class="cut w100p"></div>
        <h2 class="tac">آدرس‌ها</h2>
        <br>
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th>
                        استان
                    </th>
                    <th>
                        شهر
                    </th>
                    <th>
                        آدرس 
                    </th>
                    <th>
                        کد پستی
                    </th>
                    <th>
                        نام گیرنده
                    </th>
                    <th>
                        تلفن گیرنده
                    </th>
                    <th>
                        تحویل به نگهبان
                    </th>
                    <th>
                        مشاهده
                    </th>
                </tr>
            </thead>
            <tbody>
<?php    
    $addresses = db_get_all_address($uid);
    $i=0;
    foreach($addresses as $address){
        $data_a = $address->data();
        $i++;
?>
                <tr>
                    <td>
                        <?php echo $i;?> 
                    </td>
                    <td>
                        <?php echo $data_a["county"];?>
                    </td>
                    <td>
                        <?php echo $data_a["city"];?>
                    </td>
                    <td>
                        <?php echo $data_a["address"];?>
                    </td>
                    <td>
                        <?php echo $data_a["post_code"];?>
                    </td>
                    <td>
                        <?php echo $data_a["rec_name"];?>
                    </td>
                    <td>
                        <?php echo $data_a["rec_tel"];?>
                    </td>
                    <td>
<?php
    $class = "icon-chk";
    if($data_a["janitor"] == "yes")$class = "icon-chkfl";
?>
                        <span class="checkbox <?php echo $class; ?>" onclick="sub_show('t_janitor','<?php echo $data_a["aid"] ?>')"></span>
                    </td>
                    <td onclick ="sub_show('aid','<?php echo $data_a["aid"] ?>')" >
                            <span class="curpo icon-i"></span>
                    </td>
                </tr>
<?php
    }
?>
            </tbody>
        </table>
        <br>
        <a class="btn" onclick="sub_show('aid','new')">افزودن آدرس</a>
        <div class="cut w100p"></div>
        <h2 class="tac">سفارش‌ها</h2>
        <br>
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th>
                        شناسه سفارش
                    </th>
                    <th>
                        تاریخ ثبت
                    </th>
                    <th>
                        وضعیت 
                    </th>
                    <th>
                        جمع سبد خرید (با اعمال تخفیف)
                    </th>
                    <th>
                        مشاهده
                    </th>
                </tr>
            </thead>
        <tbody>
<?php
    $st = "SELECT id,create_date,state,cart_pure FROM orders WHERE uid = ? AND del_flag = 0 ORDER BY id DESC";
    $st = $mysqli->prepare($st);
    $st->bind_param('s',$uid);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $k = 0;
    while($row = $res->fetch_assoc()){
        $k++;
        $row["state"] = get_ms_name('orders',get_ms_flag('orders',$row["state"]));
?>
                <tr>
                    <td>
                        <?php echo $k;?> 
                    </td>
                    <td>
                        <?php echo $row["id"];?>
                    </td>
                    <td>
                        <?php echo correctDate($row["create_date"]);?>
                    </td>
                    <td>
                        <?php echo $row["state"]?>
                    </td>
                    <td>
                        <?php echo $row["cart_pure"];?>
                    </td>
                    <td onclick ="sub_show('oid','<?php echo $row["id"] ?>','orders/')" >
                            <span class="curpo icon-i"></span>
                    </td>
                </tr>
<?php
    }
?> 
            </tbody>
        </table>          
<?php
    if($_SESSION["a_logged"]->get_level() >= 1){
?>
<script type="text/javascript">
function open_edit(item){
    pn = item.parentNode;
    hides = pn.getElementsByClassName("hide");
    for(i=0;i<hides.length;i++){
        hides[i].classList.remove("hide");
    }
    document.getElementById("submit_btn").classList.remove("hide");
    item.classList.add("hide");
    inputs = pn.getElementsByTagName("input");
    for(i=0;i<inputs.length;i++){
        inputs[i].disabled = false;
    }
    return;
}
</script>
<?php
    }
?>
<?php
        }
    }
?>