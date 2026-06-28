<div id="sect_snappay_pending" class="sect" style="margin-top: 10px;">

    <div class="middle container">

        <h4 style="margin: 0 0 10px 0; font-size: 16px; text-align: right;">درخواست‌های در انتظار اسنپ‌پی</h4>

        <?php

        $snappay_pending_table_exists = false;
        $snappay_pending_list = [];

        if(file_exists($bu."modules/snappay/snappay_request_handler.php")){
            require_once $bu."modules/snappay/snappay_request_handler.php";

            $check_table = $mysqli->query("SHOW TABLES LIKE 'snappay_requests'");

            if($check_table && $check_table->num_rows > 0 && function_exists('snappay_request_type_column')) {
                $snappay_pending_table_exists = true;
                $request_type_col = snappay_request_type_column();
                $request_date_col = function_exists('snappay_request_has_column') && snappay_request_has_column('request_date')
                    ? 'request_date'
                    : 'created_at';

                $u_query = "
                    SELECT
                        o.id AS order_id,
                        o.unified_id,
                        u.name AS user_name,
                        sr.$request_type_col AS request_type,
                        sr.$request_date_col AS created_at
                    FROM orders o
                    INNER JOIN snappay_requests sr ON o.id = sr.order_id
                    LEFT JOIN users u ON u.id = o.uid
                    WHERE o.del_flag = 0
                    AND sr.status = 'pending'
                    AND sr.$request_type_col IN ('cancel', 'update')
                    ORDER BY sr.$request_date_col DESC
                ";

                $u_stmt = $mysqli->prepare($u_query);
                if($u_stmt) {
                    $u_stmt->execute();
                    $res_pending = $u_stmt->get_result();
                    if($res_pending->num_rows > 0) {
                        $snappay_pending_list = $res_pending->fetch_all(MYSQLI_ASSOC);
                    }
                    $u_stmt->close();
                }
            }
        }

        ?>

        <?php if(!empty($snappay_pending_list)): ?>
        <div style="overflow-x: auto; overflow-y: auto; max-height: 205px; background: #fff; border: 1px solid #eee; position: relative;">

            <table class="tracking" style="width: 100%; border-collapse: collapse; font-size: 13px; min-width: 950px;">

                <thead style="background: #f9f9f9; height: 30px; position: sticky; top: 0; z-index: 10; width: 100%;">
                    <tr style="position: sticky; top: 0; z-index: 11;">
                        <th style="min-width: 40px; height: 30px; line-height: 30px; vertical-align: middle;">ردیف</th>
                        <th style="height: 30px; line-height: 30px; vertical-align: middle;">شناسه سفارش</th>
                        <th style="min-width: 140px; height: 30px; line-height: 30px; vertical-align: middle;">مشتری</th>
                        <th style="min-width: 90px; height: 30px; line-height: 30px; vertical-align: middle;">نوع درخواست</th>
                        <th style="min-width: 120px; height: 30px; line-height: 30px; vertical-align: middle;">زمان ثبت</th>
                        <th style="min-width: 80px; height: 30px; line-height: 30px; vertical-align: middle;">باز کردن</th>
                    </tr>
                </thead>

                <tbody>
                    <?php $k = 1; foreach($snappay_pending_list as $row):
                        $action = strtoupper((string)$row['request_type']);
                        $type_label = ($action === 'CANCEL') ? 'لغو' : 'بروزرسانی';
                        $open_key = htmlspecialchars($row['unified_id'] ? $row['unified_id'] : $row['order_id']);
                    ?>
                    <tr style="height: 30px; line-height: 30px;">
                        <td style="height: 30px; text-align: center; vertical-align: middle;"><?php echo $k; ?></td>
                        <td style="height: 30px; vertical-align: middle;"><?php echo $open_key; ?></td>
                        <td style="height: 30px; vertical-align: middle;"><?php echo htmlspecialchars((string)($row['user_name'] ?? '-'), ENT_QUOTES, 'UTF-8'); ?></td>
                        <td style="height: 30px; font-weight: bold; vertical-align: middle; color: #c00;"><?php echo $type_label; ?></td>
                        <td style="height: 30px; font-size: 12px; vertical-align: middle;"><?php echo correctDate($row['created_at']); ?></td>
                        <td style="height: 30px; text-align: center; vertical-align: middle;">
                            <a href="?oid=<?php echo (int)$row['order_id']; ?>" class="btn click" onclick="sub_show('show_find_order', '<?php echo $open_key; ?>'); return false;">باز کردن</a>
                        </td>
                    </tr>
                    <?php $k++; endforeach; ?>
                </tbody>
            </table>

        </div>
        <?php elseif($snappay_pending_table_exists): ?>
        <div style="padding: 10px; text-align: center; color: #777;">هیچ درخواست در انتظاری یافت نشد.</div>
        <?php else: ?>
        <div style="padding: 10px; text-align: center; color: #777;">تنظیمات اسنپ‌پی یافت نشد یا جدول درخواست‌ها موجود نیست.</div>
        <?php endif; ?>

    </div>

</div>

<div class="cut w100p"></div>
