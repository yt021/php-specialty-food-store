<?php

    if(isset($indexed)){

        if($indexed == 1){?>

        <table class="tracking">

            <thead>

                <tr>

                    <th>

                        وضعیت

                    </th>

                    <th>

                        تعداد

                    </th>

                    <th>

                        قدیمی‌ترین سفارش

                    </th>

                    <th>

                        اولین ارسال

                    </th>

                </tr>

            </thead>

            <tbody>

<?php

    $state_names = explode(",",getVarFromDB("admin_modules","state_names","flag",$cf));

    $st = "SELECT state,count(id) as noo,MIN(create_date) as mcd,MIN(p_send_date) as mpd FROM orders WHERE state < 4 AND state > 0 AND del_flag = 0 GROUP BY state ORDER BY state";

    $st = $mysqli->prepare($st);

    $st->execute();

    $res = $st->get_result();

    while($row = $res->fetch_assoc()){
        $state = (int)$row["state"];

        $name = $state_names[$state];

        $NoO = $row["noo"];

        $mcd = correctDate($row["mcd"]);

        $mpd = correctDate($row["mpd"]);

        echo "<tr>";

        echo "

                <td>

                    $name

                </td>

                <td>

                    $NoO

                </td>

                <td>

                    $mcd

                </td>

                <td>

                    $mpd

                </td>

        ";

        echo "</tr>";

    }

?>

            </tbody>

        </table>

    </div>

</div>

<div class="cut w100p"></div>

<?php include $bu."admin/orders/snappay_pending_dashboard.php"; ?>

<div class="cut w100p"></div>

<div id="sect2" class="sect">

    <div class="middle container">

<?php

    $state_flags = explode(",",getVarFromDB("admin_modules","state_flags","flag",$cf));

    foreach($state_flags as $key=>$flag){

?>

        <a onclick="sub_show('state','<?php echo $flag; ?>')" class="btn half fr admin_dashboard"><?php echo $state_names[$key]; ?></a>

<?php }

?>

    </div>

</div>

<div class="cut w100p"></div>

<div id="sect2" class="sect">

    <div class="middle container">

        جستجو بر اساس کد سفارش:

        <form class="date" action="<?php echo $URL; ?>" method="post">

            <label for="id">کد سفارش:</label>

            <input name="id" type="text" value="">

            <input type="submit" name="find_order" value="تایید">

        </form>

<style type="text/css">

form.date input[type="text"] {

    border: 1px solid gray;

    width: 300px;

    float: none;

}

</style>

    </div>

</div>

<div class="cut w100p"></div>

<?php

    if(isset($_SESSION[$cf]->id)){

        if(var_exist($_SESSION[$cf]->id,$tb,"id")){

?>

        <div class="cut w100p"></div>

        <div class="">

        <table class="tracking">

            <thead>

                <tr>

                    <th>

                        شناسه سفارش

                    </th>

                    <th>

                        وضعیت

                    </th>

                    <th>

                        نام مشتری

                    </th>

                    <th>

                        استان مشتری

                    </th>

                    <th>

                        تاریخ ثبت

                    </th>

                    <th>

                        تاریخ پرداخت

                    </th>

                    <th>

                        جمع سبد خرید (با اعمال تخفیف)

                    </th>

                    <th>

                        مبلغ قابل پرداخت

                    </th>

                    <th>

                        روش پرداخت

                    </th>

                    <th>

                        ارسال

                    </th>

                    <th>

                        مشاهده جزئیات

                    </th>

                </tr>

            </thead>

            <tbody>

                <tr>

<?php

    $id = $_SESSION[$cf]->id;

    $raw_unified_id = getVarFromDB($tb, "unified_id", "id", $id);

    $unified_id = $raw_unified_id;

    if (!$unified_id) $unified_id = $id;

    $name = getVarFromDB("users","name","id",getVarFromDB($tb,"uid","id",$id));

    $state = get_ms_name($cf,get_ms_flag($cf,getVarFromDB($tb,"state","id",$id)));

    $county = getVarFromDB("addresses","county","id",getVarFromDB($tb,"aid","id",$id));

    $create_date = correctDate(getVarFromDB($tb,"create_date","id",$id));

    $payment_date = correctDate(getVarFromDB($tb,"payment_date","id",$id));

    $cart_pure = price_sep(getVarFromDB($tb,"cart_pure","id",$id));

    $pay_price = price_sep(getVarFromDB($tb,"pay_price","id",$id));

    $send_date = correctDate(getVarFromDB($tb,"p_send_date","id",$id));

    $admin_state = (int)getVarFromDB($tb,"admin_state","id",$id);

    $is_manual_pay = ($admin_state === 1);

    $pay_method = "درگاه پرداخت";

    if ($raw_unified_id && !ctype_digit((string)$raw_unified_id)) {

        $pay_method = "Pinket";

    } else if (file_exists($bu."modules/snappay/snappay_db.php")) {

        require_once $bu."modules/snappay/snappay_db.php";

        if (function_exists('snappay_tx_oids_set')) {

            $set = snappay_tx_oids_set([(int)$id]);

            if (isset($set[(int)$id])) $pay_method = "اسنپ‌پی";

        }

    }

    if ($pay_method === "درگاه پرداخت" && $is_manual_pay) {

        $pay_method = "توسط ادمین";

    }

    echo "          <td class=\"id_span\">$unified_id</td>\n<td>$state</td>\n<td>$name</td>\n<td>$county</td>\n<td>$create_date</td>\n<td>$payment_date</td>\n<td>$cart_pure</td>\n<td>$pay_price</td>\n<td>$pay_method</td>\n<td>$send_date</td>\n<td onclick=\"sub_show('show_find_order','$unified_id')\"><span class=\"curpo icon-i\"></span>";

?>

                </tr>

            </tbody>

        </table></div>

<?php

    if(getVarFromDB($tb,"del_flag","id",$id) == 1){

        echo "<br>این سفارش حذف شده است.<br>";

    }

?>

<?php

        }else{

            echo "<br>سفارشی با شناسه مورد نظر وجود ندارد.<br>";

        }

    }

?>

<?php

        }

    }
?>
