<?php

if(isset($indexed)){

    if($indexed == 1){

        include_once $bu."modules/cart/cart_funcs.php";
        require_once $bu."modules/snappay/snappay_db.php";

        $oid = isset($_SESSION[$cf]->id) ? (int)$_SESSION[$cf]->id : 0;
        if($oid < 1 || !var_exist($oid, "orders", "id")){
            ?>
            <div class="user_hint error">Order is invalid.</div>
            <?php
            return;
        }

        if (!isset($_SESSION['order_items_csrf']) || !is_string($_SESSION['order_items_csrf']) || strlen($_SESSION['order_items_csrf']) < 32) {
            if (function_exists('random_bytes')) {
                $_SESSION['order_items_csrf'] = bin2hex(random_bytes(16));
            } else {
                $_SESSION['order_items_csrf'] = sha1(uniqid('order_items_csrf_', true));
            }
        }
        $order_items_csrf = (string)$_SESSION['order_items_csrf'];

        $snappay_tx = snappay_tx_get_latest_for_order($oid);
        $snappay_csrf = '';
        if ($snappay_tx) {
            if (!isset($_SESSION['snappay_csrf']) || !is_string($_SESSION['snappay_csrf']) || strlen($_SESSION['snappay_csrf']) < 32) {
                if (function_exists('random_bytes')) {
                    $_SESSION['snappay_csrf'] = bin2hex(random_bytes(16));
                } else {
                    $_SESSION['snappay_csrf'] = sha1(uniqid('snappay_csrf_', true));
                }
            }
            $snappay_csrf = (string)$_SESSION['snappay_csrf'];
        }

        $order_row = getRow($mysqli, 'orders', 'id = ?', [$oid], 'i');
        $st = $mysqli->prepare("SELECT id,pid,weight,number FROM sub_orders WHERE oid = ? AND del_flag = 0 AND (type = 'pack' OR type = 'box') ORDER BY id ASC");
        $st->bind_param('i', $oid);
        $st->execute();
        $res = $st->get_result();
        $items = [];
        while($row = $res->fetch_assoc()){
            $row['pid'] = (int)$row['pid'];
            $row['weight'] = (int)$row['weight'];
            $row['number'] = (int)$row['number'];
            $row['name'] = (string)getVarFromDB('products', 'name', 'id', $row['pid']);
            $items[] = $row;
        }
        $st->close();

        $msg = null;
        if(isset($_SESSION["order_items_msg"])){
            $msg = (string)$_SESSION["order_items_msg"];
            unset($_SESSION["order_items_msg"]);
        }
?>

<style type="text/css">
div.order_products_box input.qty_input{
    width:90px;
    text-align:center;
}
div.order_products_box .actions_row{
    margin-top:12px;
    display:flex;
    flex-wrap:wrap;
    gap:8px;
}
</style>

<div class="ssrv order_products_box">
    <div class="ssrv_title">ویرایش محصولات سفارش</div>
    <div class="ssrv_dtl">
        <?php if($msg !== null){ ?>
            <div class="user_hint <?php if(strlen($msg) > 0 && $msg[0] === 'E') echo 'error'; ?>">
                <?php echo htmlspecialchars(substr($msg,1), ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php } ?>

        <div class="user_hint">برای حذف آیتم، تعداد را 0 بگذارید.</div>

        <form action="<?php echo $URL; ?>" method="post">
            <input type="hidden" name="order_items_csrf" value="<?php echo htmlspecialchars($order_items_csrf, ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="snappay_confirm" value="yes">
            <input type="hidden" name="snappay_csrf" value="<?php echo htmlspecialchars($snappay_csrf, ENT_QUOTES, 'UTF-8'); ?>">

            <table class="tracking">
                <thead>
                    <tr>
                        <th>ردیف</th>
                        <th>شناسه</th>
                        <th>نام</th>
                        <th>وزن</th>
                        <th>تعداد</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(count($items) === 0){ ?>
                    <tr>
                        <td colspan="5">آیتم فعالی برای این سفارش وجود ندارد.</td>
                    </tr>
                <?php } ?>
                <?php foreach($items as $idx => $item){ ?>
                    <tr>
                        <td><?php echo $idx + 1; ?></td>
                        <td><?php echo (int)$item['pid']; ?></td>
                        <td><?php echo htmlspecialchars((string)$item['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo (int)$item['weight']; ?></td>
                        <td>
                            <input
                                class="qty_input"
                                type="number"
                                min="0"
                                step="1"
                                name="item_qty[<?php echo (int)$item['id']; ?>]"
                                value="<?php echo (int)$item['number']; ?>"
                            >
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>

            <div class="actions_row">
                <input type="submit" class="btn" name="edit_products_save" value="ثبت تغییرات">
                <input
                    type="submit"
                    class="btn"
                    name="edit_products_save_send"
                    value="ثبت و به‌روزرسانی اسنپ‌پی"
                    onclick="return confirm('ابتدا آیتم‌ها ذخیره می‌شوند و سپس درخواست به‌روزرسانی اسنپ‌پی ارسال می‌شود. ادامه می‌دهید؟');"
                >
                <a class="btn" onclick="sub_show('back_to_order','1')">بازگشت به سفارش</a>
            </div>
        </form>

        <?php if($order_row){ ?>
            <div class="user_hint" style="margin-top:10px;">
                مقادیر فعلی:
                cart_price=<?php echo (int)$order_row['cart_price']; ?>،
                cart_pure=<?php echo (int)$order_row['cart_pure']; ?>،
                sale_total=<?php echo (int)$order_row['sale_total']; ?>،
                pay_price=<?php echo (int)$order_row['pay_price']; ?>
            </div>
        <?php } ?>
    </div>
</div>

<?php

    }
}

?>
