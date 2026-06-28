<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $id = $_SESSION[$cf]->id;
    $st = "SELECT static_rows,n_columns FROM $tb WHERE id = $id";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
    }
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    $last_row = (int)$row["static_rows"]-1;
?>
<style type="text/css">
    th{
        background-color:#D63650;
    }
    td select,td input{
        width:90px !important;
    }
</style>
<form action="<?php echo $URL; ?>" method="post">
    <table class="tracking">
        <thead>
            <tr>
<?php
    $alphas = range('A', 'Z');
    for($i=0;$i<(int)$row["n_columns"];$i++){
        echo "<th>".$alphas[$i]."</th>";
    }
?>
                </tr>
            </thead>
            <tbody>
<?php
    $st = "SELECT row_n,row_to_str FROM send_list_excels_static_rows WHERE sleid = $id AND row_n = $last_row";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row2 = $res->fetch_assoc()){
        echo "<tr>";
        $row_items = explode(",",$row2["row_to_str"]);
        foreach($row_items as $item){
            echo "<td>$item</td>";
        }
        echo "</tr>";
    }
?>
<?php
    $types = array(
        "empty"=>"خالی",
        "constant"=>"مقدار ثابت",
        "variable"=>"مقدار متغیر",
        "detail"=>"توضیحات (مقدار ترکیبی)"
    );
    $variables = array(
        "row_number"=>"ردیف (شماره سطر)",
        "id"=>"کد سفارش",
        "name"=>"نام و نام خانوادگی گیرنده",
        "tel"=>"تلفن گیرنده",
        "rec_tel_2"=>"تلفن ثابت",
        "county"=>"استان",
        "city"=>"شهر",
        "address"=>"آدرس",
        "full_address"=>"آدرس کامل (استان،شهر،آدرس)",
        "post_code"=>"کد پستی",
        "janitor"=>"تحویل به نگهبان",
        "send_date"=>"تاریخ ارسال",
        "rec_date"=>"تاریخ تحویل",
        "rec_date_day"=>"نام روز تحویل (شنبه تا جمعه)",
        "rec_shift"=>"نوبت ارسال",
        "cart_price"=>"ارزش محموله"
        
    );
    
    $st = "SELECT column_name,type,value FROM send_list_excels_column_values WHERE sleid = $id";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $column = array();
    $res = $st->get_result();
    while($row2 = $res->fetch_assoc()){
        $column[(int)$row2["column_name"]] = [$row2["type"],$row2["value"]];
    }
    echo "<tr>";
    for($i=0;$i<(int)$row["n_columns"];$i++){
?>
                    <td>
                        <select name="type_select[<?php echo $i; ?>]" onchange="type_select(this);">
                            <?php
                                foreach($types as $type=>$t_name){
                                    if(isset($column[$i]) && $column[$i][0] == $type)$sel = " selected "; else $sel="";
                                    echo "<option $sel value='$type'>$t_name</option>";
                                }
                            ?>
                        </select><br><br>
                        <?php
                            if(isset($column[$i]) && $column[$i][0] == 'variable')$class="";else $class="hide";
                        ?>
                        <select class="<?php echo $class; ?>" name="value_select[<?php echo $i; ?>]">
                            <?php
                                foreach($variables as $variable=>$v_name){
                                    if(isset($column[$i]) && $column[$i][0] == 'variable' && $column[$i][1] == $variable)
                                    $sel = " selected "; else $sel="";
                                    echo "<option $sel value='$variable'>$v_name</option>";
                                }
                            ?>
                        </select>
                        <?php
                            if(isset($column[$i]) && $column[$i][0] == 'constant')
                                {$value = $column[$i][1];$class="";}else {$value="";$class="hide";}
                        ?>
                        <input type="text" class="<?php echo $class; ?>" name="value_input[<?php echo $i; ?>]" value="<?php echo $value; ?>">
                    </td>
<?php
    }
    echo "</tr>";
?>
            </tbody>
        </table>
        <div class="cut w100p"></div>
<style type="text/css">

div.detail_holder{
    width:inherit;
}
div.detail_text{
    display:table;
/*    padding:0 10px;*/
    margin-left:10px;
    float:right;
}
div.detail_variable_holder{
    float:right;
/*    padding:10px;*/
    margin-left:10px;
}
div.detail_variables select{
    float:none;
    width:150px;
}
</style>
<?php
//    Find Detail Value
    $detail_value = "";
    if(isset($detail_table_value) && $detail_table_value){
        $detail_value = $detail_table_value;
    }
    foreach($column as $column_tv){
        if($column_tv[0] == "detail")
        $detail_value = $column_tv[1];
    }
    $detail_text = "";
    if($detail_value){
        $divide_point = stripos($detail_value,"|||");
        $detail_text = substr($detail_value,0,$divide_point);
        $detail_variables = substr($detail_value,$divide_point+3);
        $detail_variables = explode(",",$detail_variables);
        
    }
?>
        <div class="detail_holder">
            <div class="detail_text">
                <label>توضیحات:</label><br>
                <textarea name="detail_text" style="float:none;"  onchange="detail_cfc(this)" onkeydown="detail_cfc(this)" placeholder=
"متن توضیحات را اینجا وارد نمایید.
برای قرار دادن متغیرها، از «متغیر» استفاده کنید.
متغیرها به ترتیب در متن جایگذاری خواهند شد.
"
                ><?php echo $detail_text; ?></textarea>
            </div>
            <div id="dv_div" class="detail_variables">
            <?php
                $i = 0;
                foreach($variables as $v){
                    if(isset($detail_variables[$i]))$class="";else $class="hide";
            ?>
                <div class="detail_variable_holder <?php echo $class; ?>">
                    <label>متغیر <?php echo $i+1; ?>:</label><br>      
                    <select class="" name="detail_value[<?php echo $i; ?>]">
                        <option value="">انتخاب</option>
                        <?php
                            foreach($variables as $variable=>$v_name){
                                if(isset($detail_variables[$i]) && $detail_variables[$i] == $variable)
                                $sel = " selected "; else $sel="";
                                echo "<option $sel value='$variable'>$v_name</option>";
                            }
                        ?>
                    </select>
                </div>
            <?php
                    $i++;
                }
            ?>
            </div>
        </div>
        <div class="cb"></div>
        <div class="cut w100p"></div>
        <input type="submit" name="edit" class="btn" value="ثبت">
<script type="text/javascript">
function type_select(item){
    t_value = item[item.selectedIndex].value;
    item.parentNode.getElementsByTagName('select')[1].classList.add('hide');
    item.parentNode.getElementsByTagName('input')[0].classList.add('hide');
    switch(t_value){
        case "empty":
        case "detail":
            break;
        case "constant":
            item.parentNode.getElementsByTagName('input')[0].classList.remove('hide');
            break;
        case "variable":
            item.parentNode.getElementsByTagName('select')[1].classList.remove('hide');
            break;
    }
}

function detail_cfc(item){
    no_of_params = (item.value.match(/«متغیر»/g) || []).length;
    dv_div = document.getElementById("dv_div");
    dvh_divs = dv_div.getElementsByClassName('detail_variable_holder');
    for(i=0;i<no_of_params;i++){
        dvh_divs[i].classList.remove('hide');
    }
    for(i=no_of_params;i<dvh_divs.length;i++){
        dvh_divs[i].classList.add('hide');
        dvh_divs[i].getElementsByTagName('select')[0].selectedIndex = 0;
    }
    
}
</script>
</form>
<?php
        }
    }
?>
