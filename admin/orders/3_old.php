<?php
    if(isset($indexed)){
        if($indexed == 1){?>

<form action="<?php echo $URL; ?>" method="post">

<?php
    echo '
        <table class="tracking">
            <thead>
                <tr>
                    <th>
                        ردیف
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','1'".')">
                        شناسه سفارش
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','2'".')">
                        نام مشتری
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','3'".')">
                        استان مشتری
                    </th>
                    <th class="onc" '.' onclick="sub_show('."'order','4'".')">
                        تاریخ ثبت
                    </th>
                    <th>
                        شماره مرسوله پستی 
                    </th>
                    <th>
                        تی‌نکست
                    </th>
                    <th>
                        پیک (خارج از نوبت)
                    </th>
                    <th>
                        تیپاکس 
                    </th>';
                    
            echo        '
                </tr>
            </thead>
            <tbody>';
            
        {
            $order_str = $_SESSION[$cf]->order_by->order_str($tb);
            $st = "SELECT $tb.id,users.name,addresses.rec_name,addresses.county,$tb.create_date FROM $tb 
            LEFT JOIN users ON $tb.uid = users.id
            LEFT JOIN addresses ON $tb.aid = addresses.id
            WHERE $tb.del_flag = 0 AND $tb.state = 3 AND $tb.create_date > '2019-10-10' AND $tb.post_ref_id is NULL $order_str";
            $st = $mysqli->prepare($st);
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
                $name = $row["name"];
                if($rec_name = $row["rec_name"])$name = $rec_name;
                $county = $row["county"];
                $submit_date = correctDate($row["create_date"]);
                
                
                echo "<tr>
                        <td>
                            $k
                        </td>
                        <td ".'class="id_span"'.">
                            $id
                        </td>
                        <td>
                            $name
                        </td>
                        <td>
                            $county
                        </td>
                        <td>
                            $submit_date
                        </td>
                        <td>
                            <input name='post[$id]' type='text'>
                        </td>
                        <td>
                            <input class='hide' name='tinext[$id]' type='checkbox'>
                            <span class='curpo chkholder icon-chk' onclick = 'box_select(this);'></span>
                        </td>
                        <td>
                            <input class='hide' name='courier[$id]' type='checkbox'>
                            <span class='curpo chkholder icon-chk' onclick = 'box_select(this);'></span>
                        </td>
                        <td>
                            <input class='hide' name='tipax[$id]' type='checkbox'>
                            <span class='curpo chkholder icon-chk' onclick = 'box_select(this);'></span>
                        </td>";
                echo    "</tr>";
            }
        }
        echo '</tbody>
            </table>';
?>
<div class="cut"></div>
<input type="submit" name="post_ref" class="btn" value="ثبت">
</form>
<script type="text/javascript">
function box_select(item){
    if(item.classList.contains("icon-chk")){
        item.classList.add("icon-chkfl");
        item.classList.remove("icon-chk");
        item.parentNode.getElementsByTagName('input')[0].checked = true;
    }else if(item.classList.contains("icon-chkfl")){
        item.classList.remove("icon-chkfl");
        item.classList.add("icon-chk");
        item.parentNode.getElementsByTagName('input')[0].checked = false;
    }
}
</script>
<?php
        }
    }
?>