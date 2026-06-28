<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
if(!function_exists("cart_discount_code_normalize")){
    include_once $GLOBALS['bu']."modules/cart/cart_funcs.php";
}
?>
        <a class="btn" onclick="sub_show('new','new')">جشنواره تخفیف جدید</a>
        </div>
    </div>
    <div class="cut w100p"></div>

    <div id="sect3" class="sect ">
        <div class="middle container">
<?php

if(isset($_POST["id_del"]) && var_exist($_POST["id_del"],$tb,"id")){
    $del_flag = (int)getVarFromDB($tb,"del_flag","id",$_POST["id_del"]);
    $del_flag = 1 - $del_flag;
    updateInDB($tb,"del_flag",$del_flag,"id",$_POST["id_del"]);
}

if(isset($_POST["del_flag"])){
    $_SESSION[$cf]->del_flag = 1-$_SESSION[$cf]->del_flag;
}
if(!isset($_SESSION[$cf]->filter_status)) $_SESSION[$cf]->filter_status = "all";
if(!isset($_SESSION[$cf]->filter_code)) $_SESSION[$cf]->filter_code = "all";
if(isset($_POST["filter_status"])){
    $status_filter = trim((string)$_POST["filter_status"]);
    if(in_array($status_filter,array("all","active","upcoming","expired"),true)){
        $_SESSION[$cf]->filter_status = $status_filter;
    }
}
if(isset($_POST["filter_code"])){
    $code_filter = trim((string)$_POST["filter_code"]);
    if(in_array($code_filter,array("all","with_code","without_code"),true)){
        $_SESSION[$cf]->filter_code = $code_filter;
    }
}

if($_SESSION[$cf]->del_flag == 1) echo "<h3>حذف شده ها</h3>";

function correctType($type){
    switch($type){
        case "percentage":
        case "percent":
            return "درصد از جمع سبد خرید";
        case "fixed_amount":
        case "amount":
            return "مبلغ از سبد خرید";
        case "whole_price":
        case "final_price":
        case "fixed_price":
            return "قیمت نهایی سبد خرید";
        case "free_shipping":
        case "free_send":
        case "send":
            return "ارسال رایگان";
        case "gift":
            return "هدیه";
    }
    return $type;
}

function sales2_status_text($status){
    switch($status){
        case "active": return "فعال";
        case "upcoming": return "آینده";
        case "expired": return "منقضی";
    }
    return "-";
}

$active_filter = (string)$_SESSION[$cf]->filter_status;
$code_filter = (string)$_SESSION[$cf]->filter_code;
?>
<style type="text/css">
    .sales_filters{
        display:flex;
        gap:8px;
        flex-wrap:wrap;
        margin:0 0 10px 0;
        justify-content:flex-start;
    }
    .sales_filters .btn{
        width:130px !important;
        min-width:130px !important;
        box-sizing:border-box;
        display:inline-flex !important;
        align-items:center;
        justify-content:center;
        text-align:center;
        white-space:nowrap;
        padding:6px 10px;
    }
    .sales2_table_wrap{
        width:100%;
        overflow-x:auto;
    }
    table.tracking.sales2_summary_table{
        width:100%;
    }
    table.tracking.sales2_summary_table td.sale_code_cell{
        font-size:inherit !important;
    }
    table.tracking.sales2_summary_table td.sale_code_cell span[dir='ltr']{
        font-size:inherit !important;
        line-height:inherit;
        font-family:inherit;
        font-weight:inherit;
        letter-spacing:normal;
        white-space:normal;
        word-break:break-word;
    }
    @media (max-width:760px){
        .sales_filters{
            justify-content:center;
        }
        .sales_filters .btn{
            width:124px !important;
            min-width:124px !important;
        }
        table.tracking.sales2_summary_table thead{
            display:none;
        }
        table.tracking.sales2_summary_table tbody,
        table.tracking.sales2_summary_table tr,
        table.tracking.sales2_summary_table td{
            display:block;
            width:100%;
        }
        table.tracking.sales2_summary_table tr{
            border:2px solid #2e2e2e;
            margin-bottom:10px;
        }
        table.tracking.sales2_summary_table td{
            border-left:0 !important;
            border-top:1px solid #2e2e2e;
            padding:8px !important;
            text-align:right;
            font-size:13px;
            line-height:1.5;
        }
        table.tracking.sales2_summary_table td:first-child{
            border-top:0;
        }
        table.tracking.sales2_summary_table td::before{
            content:attr(data-label);
            float:left;
            margin-right:10px;
            color:#bfbfbf;
            font-weight:700;
        }
        table.tracking.sales2_summary_table td .curpo{
            font-size:16px;
        }
    }
</style>
<div class="sales_filters" style="display:flex;gap:8px;flex-wrap:wrap;margin:0 0 10px 0;">
    <a class="btn" onclick="sub_show('filter_status','all')" style="padding:6px 10px;<?php if($active_filter==='all') echo 'background:#333;'; ?>">همه وضعیت‌ها</a>
    <a class="btn" onclick="sub_show('filter_status','active')" style="padding:6px 10px;<?php if($active_filter==='active') echo 'background:#333;'; ?>">فعال</a>
    <a class="btn" onclick="sub_show('filter_status','upcoming')" style="padding:6px 10px;<?php if($active_filter==='upcoming') echo 'background:#333;'; ?>">آینده</a>
    <a class="btn" onclick="sub_show('filter_status','expired')" style="padding:6px 10px;<?php if($active_filter==='expired') echo 'background:#333;'; ?>">منقضی</a>
    <a class="btn" onclick="sub_show('filter_code','all')" style="padding:6px 10px;<?php if($code_filter==='all') echo 'background:#333;'; ?>">همه کدها</a>
    <a class="btn" onclick="sub_show('filter_code','with_code')" style="padding:6px 10px;<?php if($code_filter==='with_code') echo 'background:#333;'; ?>">دارای کد</a>
    <a class="btn" onclick="sub_show('filter_code','without_code')" style="padding:6px 10px;<?php if($code_filter==='without_code') echo 'background:#333;'; ?>">بدون کد</a>
</div>
<?php

echo '<div class="sales2_table_wrap">
        <table class="tracking sales2_summary_table">
            <thead>
                <tr>
                    <th>ردیف</th>
                    <th class="onc" onclick="sub_show(&#39;order&#39;,&#39;1&#39;)">شناسه</th>
                    <th class="onc" onclick="sub_show(&#39;order&#39;,&#39;2&#39;)">نام</th>
                    <th class="onc" onclick="sub_show(&#39;order&#39;,&#39;3&#39;)">نوع</th>
                    <th class="onc" onclick="sub_show(&#39;order&#39;,&#39;4&#39;)">مقدار</th>
                    <th>وضعیت</th>
                    <th class="onc" onclick="sub_show(&#39;order&#39;,&#39;5&#39;)">تاریخ شروع</th>
                    <th class="onc" onclick="sub_show(&#39;order&#39;,&#39;6&#39;)">تاریخ پایان</th>
                    <th>تعداد سفارش</th>
                    <th>مبلغ تخفیف</th>
                    <th class="onc" onclick="sub_show(&#39;order&#39;,&#39;7&#39;)">تاریخ ثبت</th>
                    <th class="onc" onclick="sub_show(&#39;order&#39;,&#39;8&#39;)">کد تخفیف</th>
                    <th>مشاهده جزئیات</th>
                    <th>مشاهده سفارش‌ها</th>
                    <th class="onc" onclick="sub_show(&#39;del_flag&#39;,&#39;-&#39;)">حذف</th>
                </tr>
            </thead>
            <tbody>';

$order_str = $_SESSION[$cf]->order_by->order_str($tb);
$del_flag = $_SESSION[$cf]->del_flag;
$tb2 = "orders_sale";
$st = "SELECT $tb.id,$tb.name,$tb.type,$tb.amount,$tb.start_date,$tb.end_date,$tb.create_date,$tb.lksr FROM $tb WHERE $tb.del_flag = $del_flag";
$st = $mysqli->prepare($st);
if(!$st->execute()){
    echo "E";
    exit;
}

$res = $st->get_result();
$k = 0;
$now_ts = db_time_now() + 2.5*3600;
while($row = $res->fetch_assoc())
{
    $id = (int)$row["id"];
    $raw_lksr = trim((string)$row["lksr"]);
    $lksr = cart_discount_code_normalize($raw_lksr);

    if($code_filter === "with_code" && $lksr === "") continue;
    if($code_filter === "without_code" && $lksr !== "") continue;

    $start_ts = strtotime((string)$row["start_date"]);
    $end_ts = strtotime((string)$row["end_date"]);
    $status = "active";
    if($start_ts !== false && $start_ts > $now_ts){
        $status = "upcoming";
    }else if($end_ts !== false && $end_ts <= $now_ts){
        $status = "expired";
    }
    if($active_filter !== "all" && $status !== $active_filter) continue;

    $k++;
    $name = $row["name"];
    $type = correctType($row["type"]);
    $amount = $row["amount"];
    if($row["type"] == 'gift'){
        $amount = getVarFromDB('products','name','id',$amount);
    }
    $start_date = correctDate($row["start_date"]);
    $end_date = correctDate($row["end_date"]);
    $create_date = correctDate($row["create_date"]);

    $st2 = "SELECT $tb2.amount FROM $tb2 CROSS JOIN orders WHERE $tb2.sid = $id AND $tb2.oid = orders.id AND orders.state > 0 AND orders.del_flag = 0";
    $st2 = $mysqli->prepare($st2);
    if(!$st2->execute()){
        echo "E";
        exit;
    }
    $res2 = $st2->get_result();
    $c_sale = 0;
    $s_amount = 0;
    while($row2 = $res2->fetch_assoc())
    {
        $c_sale++;
        $s_amount = $s_amount + $row2["amount"];
    }

    if($row["type"] == 'gift'){
        $weight = getVarFromDB('products_price','weight','pid',$row['amount'],'start_time DESC');
        $weight = explode(',',$weight)[0];
        $pf = product_finance($row['amount'],$weight,$row['end_date']);
        $s_amount = $c_sale * ($pf['price'] - $pf['profit']);
    }

    echo "<tr>
            <td data-label='Row'>$k</td>
            <td data-label='ID'>$id</td>
            <td data-label='Name'>$name</td>
            <td data-label='Type'>$type</td>
            <td data-label='Amount'>$amount</td>
            <td data-label='Status'>".sales2_status_text($status)."</td>
            <td data-label='Start Date'>$start_date</td>
            <td data-label='End Date'>$end_date</td>
            <td data-label='Orders Count'>$c_sale</td>
            <td data-label='Discount Amount'>$s_amount</td>
            <td data-label='Created At'>$create_date</td>
            <td class='sale_code_cell' data-label='Discount Code'><span dir='ltr'>".($lksr !== "" ? htmlspecialchars($lksr,ENT_QUOTES,'UTF-8') : "-")."</span></td>
            <td data-label='Details' onclick=\"sub_show('id','$id')\"><span class='curpo icon-i'></span></td>
            <td data-label='Orders' onclick=\"sub_show('show_orders_id','$id')\"><span class='curpo icon-basket'></span></td>
            <td data-label='Delete' onclick=\"sub_show('id_del','$id')\"><span class='curpo icon-x'></span></td>
        </tr>";
}

echo '</tbody>
    </table>
</div>';
?>

<?php
        }
    }
?>
