<?php
    if(isset($indexed)){
        if($indexed == 1){?>

<?php
    if(!function_exists('account_snappay_list_pay_price')){
        function account_snappay_list_pay_price($oid, $fallback_pay_price, $bu)
        {
            static $snappay_loaded = false;
            if(!$snappay_loaded){
                $handler_path = $bu . "modules/snappay/snappay_request_handler.php";
                if(file_exists($handler_path)){
                    require_once $handler_path;
                }
                $snappay_loaded = true;
            }

            if(function_exists('snappay_get_request_for_order')){
                $cancel_request = snappay_get_request_for_order((int)$oid, 'cancel', null);
                if(
                    is_array($cancel_request) &&
                    strtolower(trim((string)($cancel_request['status'] ?? ''))) === 'approved' &&
                    isset($cancel_request['request_snapshot_data']) &&
                    is_array($cancel_request['request_snapshot_data']) &&
                    isset($cancel_request['request_snapshot_data']['financial']) &&
                    is_array($cancel_request['request_snapshot_data']['financial']) &&
                    isset($cancel_request['request_snapshot_data']['financial']['pay_price'])
                ){
                    return (int)$cancel_request['request_snapshot_data']['financial']['pay_price'];
                }
            }

            return (int)$fallback_pay_price;
        }
    }
?>
<main>
    <style type="text/css">
        .account_dash_orders_split{
            display:grid;
            grid-template-columns:minmax(0,1fr) 280px;
            gap:16px;
            align-items:start;
        }
        .account_dash_orders_main,
        .account_dash_orders_side{
            min-width:0;
        }
        .account_dash_orders_side{
            position:sticky;
            top:16px;
        }
        .account_dash_back{
            display:flex;
            width:100%;
        }
        .account_dash_back .btn{
            width:100%;
            margin-right:0;
        }
        .account_dash_panel .user_hint{
            margin-right:auto;
            margin-left:0;
        }
        @media (max-width:900px){
            .account_dash_orders_split{
                grid-template-columns:minmax(0,1fr);
            }
            .account_dash_orders_side{
                position:static;
                top:auto;
            }
        }
    </style>
    <div class="content account_dash_page">
        <div class="account_dash_header">
            <h1 class="account_dash_kicker" onclick="sub_show('clear',0);">حساب من</h1>
            <p class="account_dash_title">تمام سفارش‌ها</p>
        </div>
        <div class="account_dash_orders_split">
            <div class="account_dash_orders_main">
            <div class="account_dash_panel">
                <h3 class="account_dash_panel_title">فهرست سفارش‌های ثبت‌شده</h3>
                <div class="account_dash_order_list_block">
                <div class="account_dash_table_wrap">
                    <table class="cart account">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th class="onc" '.' onclick="">
                        شناسه سفارش
                    </th>
                    <th class="onc" '.' onclick="">
                        تاریخ ثبت
                    </th>
                    <th class="onc" '.' onclick="">
                        مبلغ کل
                    </th>
                    <th class="onc" '.' onclick="">
                        وضعیت
                    </th>
                    <th>
                        جزئیات
                    </th>
                </tr>
            </thead>
            <tbody>
            <?php
                $tb = "orders";
                $order_cards = array();
                
                $st = "SELECT id,create_date,pay_price,state,send_date,recieve_date,recieve_shift,post_ref_id FROM $tb 
                WHERE uid = ? AND state >= 1 AND del_flag = 0 ORDER BY create_date DESC, id DESC";
                $st = $mysqli->prepare($st);
                $st->bind_param('s',$_SESSION["logged"]->uid);
                if(!$st->execute()){
                    echo "E";
                    exit;
                }
                
                $res = $st->get_result();
                $k = 0;
                
                while($row = $res->fetch_assoc())
                {
                    $k++;
                    $id = $row["id"];
                    $create_date = correctDate($row["create_date"]);
                    $total_price = price_sep(account_snappay_list_pay_price($id, $row["pay_price"], $bu));
                    $state = (int)$row["state"];
                    $send_date = correctDate($row["send_date"]);
                    $rec_date = correctDate($row["recieve_date"]);
                    $rec_shift = $row["recieve_shift"];
                    $post_ref_id = $row["post_ref_id"];
                    $td_rs = 1;
                    if($state == 4){
                        if($post_ref_id){
                            $td_rs = 2;
                        }
                    }else{
                        if($rec_shift){
                            $td_rs = 2;
                        }
                    }
                    $td_rs = "rowspan='$td_rs'";
                    
                    $state = getVarFromDB("admin_orders_state","name","id",$state+1);
                    $state = substr($state,0);
                    
                    $tr_class="";
                    if($k%2 == 1)$tr_class = ' class="rgba" ';
                    echo "<tr $tr_class>
                        <td $td_rs>
                            $k
                        </td>
                        <td $td_rs>
                            $id
                        </td>
                        <td>
                            $create_date
                        </td>
                        <td>
                            $total_price
                        </td>
                        <td>
                            $state
                        </td>
                        <td ".'onclick ="sub_show('."'oid','".$id."'".')" >
                            <span class="curpo icon-i"></span>'."
                        </td>
                    </tr>";

                    $delivery_status = "";
                    if($post_ref_id){
                        $delivery_status = correct_post_state_client($post_ref_id,$send_date,$rec_date,$rec_shift);
                        echo "<tr $tr_class>
                                <td colspan='4'>";
                        echo $delivery_status;
                        echo "  </td>
                            </tr>";
                    }else{
                        if($rec_shift){
                            $delivery_status = correct_rec_time_client($rec_date,$rec_shift);
                            echo "<tr $tr_class>
                                    <td colspan='4'>";
                            echo $delivery_status;
                            
                            echo "  </td>
                                </tr>";
                        }    
                    }
                    $order_cards[] = array(
                        "row_no" => $k,
                        "id" => $id,
                        "create_date" => $create_date,
                        "total_price" => $total_price,
                        "state" => $state,
                        "delivery_status" => $delivery_status
                    );
                }
            ?>
            </tbody>
                    </table>
                </div>
                <div class="account_dash_orders_cards">
                <?php foreach($order_cards as $order_card){ ?>
                    <div class="account_dash_order_card">
                        <div class="account_dash_order_card_head">
                            <h4 class="account_dash_order_card_title">&#1587;&#1601;&#1575;&#1585;&#1588; #<?php echo (int)$order_card["id"]; ?></h4>
                            <span class="account_dash_order_card_status"><?php echo htmlspecialchars((string)$order_card["state"], ENT_QUOTES, 'UTF-8'); ?></span>
                        </div>
                        <div class="account_dash_order_card_grid">
                            <div class="account_dash_order_card_item">
                                <span class="account_dash_order_card_label">&#1585;&#1583;&#1740;&#1601;</span>
                                <span class="account_dash_order_card_value"><?php echo (int)$order_card["row_no"]; ?></span>
                            </div>
                            <div class="account_dash_order_card_item">
                                <span class="account_dash_order_card_label">&#1588;&#1606;&#1575;&#1587;&#1607; &#1587;&#1601;&#1575;&#1585;&#1588;</span>
                                <span class="account_dash_order_card_value"><?php echo (int)$order_card["id"]; ?></span>
                            </div>
                            <div class="account_dash_order_card_item">
                                <span class="account_dash_order_card_label">&#1578;&#1575;&#1585;&#1740;&#1582; &#1579;&#1576;&#1578;</span>
                                <span class="account_dash_order_card_value"><?php echo htmlspecialchars((string)$order_card["create_date"], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <div class="account_dash_order_card_item">
                                <span class="account_dash_order_card_label">&#1605;&#1576;&#1604;&#1594; &#1705;&#1604;</span>
                                <span class="account_dash_order_card_value"><?php echo htmlspecialchars((string)$order_card["total_price"], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <?php if(trim((string)$order_card["delivery_status"]) !== ""){ ?>
                            <div class="account_dash_order_card_item is-wide">
                                <span class="account_dash_order_card_label">&#1608;&#1590;&#1593;&#1740;&#1578; &#1575;&#1585;&#1587;&#1575;&#1604;</span>
                                <span class="account_dash_order_card_value"><?php echo $order_card["delivery_status"]; ?></span>
                            </div>
                            <?php } ?>
                        </div>
                        <div class="account_dash_order_card_actions">
                            <a onclick="sub_show('oid','<?php echo (int)$order_card["id"]; ?>')" class="btn">&#1605;&#1588;&#1575;&#1607;&#1583;&#1607; &#1580;&#1586;&#1574;&#1740;&#1575;&#1578;</a>
                        </div>
                    </div>
                <?php } ?>
                </div>
                </div>
            </div>
            </div>
            <div class="account_dash_orders_side">
            <div class="account_dash_back">
                <a onclick="sub_show('clear',0);" class="btn">بازگشت</a>
            </div>
            </div>
        </div>
    </div>
</main>

    
<?php
        }
    }
?>
