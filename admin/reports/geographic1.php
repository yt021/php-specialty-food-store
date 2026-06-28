<?php
    if(isset($indexed)){
        if($indexed == 1){?>

<h3 class="tac">
   جدول بررسی جغرافیایی
</h3><br>
<h4 class="tac"> فروش استان تهران</h4><br>
<?php
    echo '
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        محدوده
                    </th>
                    <th>
                       تعداد مشتری
                    </th>
                    <th>
                        تعداد سفارش
                    </th>
                    <th>
                        مجموع مبلغ خرید
                    </th>
                </tr>
            </thead>
            <tbody>';
    
    $places = getVarFromDB("cities","cities_str","id",1);
    $places = get_str_index($places,",")[1];
    array_push($places,"حومه");
    for($index = 0;$index<sizeof($places);$index++){
        $places_table[$places[$index]]["name"] = $places[$index];
        $places_table[$places[$index]]["clients"] = new order_by();
        $places_table[$places[$index]]["orders"] = 0;
        $places_table[$places[$index]]["total_revenue"] = 0;
    }
    $tb2 = "addresses";
    $st = "SELECT id,uid,aid,cart_price,create_date FROM $tb WHERE $tb.state >= 1 $where;";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    
    while($row = $res->fetch_assoc())
    {
        if(getVarFromDB("addresses","county","id",$row["aid"]) == "شهر تهران"){
        $city = getVarFromDB("addresses","city","id",$row["aid"]);
        // echo $row["uid"]."-".$row["aid"]."<br>";
        $places_table[$city]["clients"]->add_item($row["uid"]);
        $places_table[$city]["orders"]++;
        $places_table[$city]["total_revenue"] += $row["cart_price"];
        }
        if(getVarFromDB("addresses","county","id",$row["aid"]) == "استان تهران"){
        $city = "حومه";
        $places_table[$city]["clients"]->add_item($row["uid"]);
        $places_table[$city]["orders"]++;
        $places_table[$city]["total_revenue"] += $row["cart_price"];
        }
        
    }
    
    foreach($places as $place){
        $places_table[$place]["clients"] = substr_count($places_table[$place]["clients"]->order_str("users"),",");
        
        echo "
        <tr>
            <td>
                ".$places_table[$place]["name"]."
            </td>
            <td>
                ".$places_table[$place]["clients"]."
            </td>
            <td>
                ".$places_table[$place]["orders"]."
            </td>
            <td>
                ".$places_table[$place]["total_revenue"]."
            </td>
        </tr>
        ";
    }
    echo '</tbody>
            </table>';
?><br>
<h4 class="tac"> فروش سایر استان ها</h4><br>
<?php
    
    
    $st = "SELECT county,map_svg_path FROM cities WHERE id > 1 AND del_flag = 0;";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $k = 0;
    $places_table = array();
    while($row = $res->fetch_assoc())
    {
        $places[$k] = $row["county"];
        
        $places_table[$places[$k]]["name"] = $places[$k];
        $places_table[$places[$k]]["clients"] = new order_by();
        $places_table[$places[$k]]["orders"] = 0;
        $places_table[$places[$k]]["total_revenue"] = 0;
        $places_table[$places[$k]]["svg_path"] = $row["map_svg_path"];
        $k++;
    }

    
    $st = "SELECT id,uid,aid,cart_price,create_date FROM $tb WHERE $tb.state >= 1 $where;";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    
    while($row = $res->fetch_assoc())
    {
        $county = getVarFromDB("addresses","county","id",$row["aid"]);
        if($county != "شهر تهران" && $county != "استان تهران"){
            // echo $row["uid"]."-".$row["aid"]."<br>";
        $places_table[$county]["clients"]->add_item($row["uid"]);
        $places_table[$county]["orders"]++;
        $places_table[$county]["total_revenue"] += $row["cart_price"];
        }
        // else{
        //     $county = "استان تهران";
        //     $places_table[$county]["clients"]->add_item($row["uid"]);
        //     $places_table[$county]["orders"]++;
        //     $places_table[$county]["total_revenue"] += $row["cart_price"];
        // }
    }
    $max = 0;
    foreach($places as $place){
        $places_table[$place]["clients"] = substr_count($places_table[$place]["clients"]->order_str("users"),",");
        $max = max($max,$places_table[$place]["orders"]);
    }
    
    ?>

<style type="text/css">
div.map_holder{
    max-width:700px;
    margin:auto;
}
div.detail_holder{
    width:100%;
    height:100px;
    border-bottom:1px solid #D63650;
}
svg{
    display:relative;
    margin:10px 0 30px 0;
}
svg path{
    fill:#D63650 !important;
    stroke:black !important;
    stroke-width: 1px;
}
svg path:hover{
    fill:gold !important;
    opacity:0.5 !important;
}

</style>
<script type="text/javascript">

function show_county_detail(item){
    name = item.getAttribute('title');
    noc = item.getAttribute('alt');
    noo = item.getAttribute('id');
    troo = item.getAttribute('class');
    tr = "<td>"+name+"</td><td>"+noc+"</td><td>"+noo+"</td><td>"+troo+"</td>";
    document.getElementById('county_detail_tr').innerHTML = tr;
}

</script>

<div class="map_holder">
    <div class="detail_holder">
        <table class="tracking">
            <thead>
                <tr>
                    <th style="width:110px;">
                        استان
                    </th>
                    <th>
                       تعداد مشتری
                    </th>
                    <th>
                        تعداد سفارش
                    </th>
                    <th>
                        مجموع مبلغ خرید
                    </th>
                </tr>
            </thead>
            <tbody >
                <tr id="county_detail_tr"></tr>
            </tbody>
        </table>
    </div>
    <svg  preserveAspectRatio="xMidYMid meet" viewBox="0 0 654.51147 593.71021">
<?php
    foreach($places as $place){
        if($max==0){$opacity=0.1;}
        else{$opacity = 0.1+$places_table[$place]["orders"]/$max;}
        echo "
            <path onmouseover='show_county_detail(this)' d='".$places_table[$place]["svg_path"]."' title='".$places_table[$place]["name"]."' alt='".$places_table[$place]["clients"]."' id='".$places_table[$place]["orders"]."' class='".$places_table[$place]["total_revenue"]."'  style='opacity:$opacity'>
            </path>
        ";
    }            
?>        
    </svg>
</div>

<?php
    echo '
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        استان
                    </th>
                    <th>
                       تعداد مشتری
                    </th>
                    <th>
                        تعداد سفارش
                    </th>
                    <th>
                        مجموع مبلغ خرید
                    </th>
                </tr>
            </thead>
            <tbody>';
    
    foreach($places as $place){
        
        echo "
        <tr>
            <td>
                ".$places_table[$place]["name"]."
            </td>
            <td>
                ".$places_table[$place]["clients"]."
            </td>
            <td>
                ".$places_table[$place]["orders"]."
            </td>
            <td>
                ".$places_table[$place]["total_revenue"]."
            </td>
        </tr>
        ";
    }
    echo '</tbody>
            </table>';
?>

<?php
        }
    }
?>