<?php
    if(isset($indexed)){
        if($indexed == 1){?>


<h3 class="tac">
    گزارش مختصر مالی
</h3><br>
<div class="table_rotate">
<?php



    $fields = ["recieved","sold","sale","pure_sold","profit","item","weight"];
    $total = array(
                "recieved"=>0,
                "sold"=>0,
                "sale"=>0,
                "pure_sold"=>0,
                "profit"=>0,
                "item"=>0,
                "client"=>new order_by(),
                "order"=>0,
                "weight"=>0
    );

    $st = "SELECT id,uid,create_date,cart_price,cart_pure,sale_total,pay_price FROM $tb WHERE 
    del_flag = 0 AND
    state >= 1
    $where
    ";
    
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    
    while($row = $res->fetch_assoc())
    {
        $order = array(
                    "recieved"=>$row["pay_price"],
                    "sold"=>$row["cart_price"],
                    "sale"=>$row["sale_total"],
                    "pure_sold"=>$row["cart_pure"],
                    "profit"=>0,
                    "item"=>0,
                    "weight"=>0
        );
        
        $oid = $row["id"];
        $st2 = "SELECT pid,weight,number FROM sub_$tb WHERE oid = $oid";
        $st2 = $mysqli->prepare($st2);
        if(!$st2->execute()){
            echo "E";
            exit;
        }
        $res2 = $st2->get_result();
        while($row2 = $res2->fetch_assoc()){
            $pf = product_finance($row2["pid"],$row2["weight"],$row["create_date"]);
            $order["item"]+=$row2["number"];
            $order["profit"] += $pf["profit"]*$row2["number"];
            $order["weight"]+=$pf["weight"]*$row2["number"];
        }
        
        foreach($fields as $f){
            $total[$f]+=$order[$f];
        }
        $total["order"]++;
        $total["client"]->add_item($row["uid"]);
    }
    
    $total["client"] = substr_count($total["client"]->order_str("users"),",");
    $total["profit"] = $total["profit"] - $total["sale"];
    
?>
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        مجموع دریافت
                    </th>
                    <th>
                        مجموع فروش
                    </th>
                    <th>
                        مجموع تخفیف
                    </th>
                    <th>
                        مجموع فروش خالص
                    </th>
                    <th>
                        مجموع سود
                    </th>
                    <th>
                        تعداد بسته
                    </th>
                    <th>
                        تعداد مشتریان
                    </th>
                    <th>
                        تعداد سفارش
                    </th>
                    <th>
                        مجموع وزن
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
<?php
    foreach($total as $td){
        echo "
            <td>
            $td
            </td>
        ";
    }
?>
                </tr>
            </tbody>
        </table>
</div>

<style>
    @media only screen and (max-width:600px){
        div.table_rotate{
            width:240px;
            display:block;
            height:800px;
            margin:auto;
        }
        div.table_rotate table{
            transform: rotate(90deg) translateX(800px);
            transform-origin:top right;
        }
        div.table_rotate table td,div.table_rotate table th{
            transform: rotate(-90deg);
        }
        div.table_rotate table tbody tr{
            height:100px;
        }
    }
    
</style>

<div class="cut w100p"></div>

<?php
    $st = "SELECT * FROM $atb";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
?>
<?php
    while($row = $res->fetch_assoc()){ ?>
        <a onclick="sub_show('state','<?php echo $row["flag"]; ?>')" class="btn half fr admin_dashboard" ><?php echo $row["name"]; ?></a>
<?php }
?>


<?php
        }
    }
?>