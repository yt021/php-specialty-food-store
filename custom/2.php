<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<main>
    <h1 class="tac">انتخاب محتوای جعبه</h1>
    <div class="content">
        <div class="user_hint">
        
        <p class="tac">
            در این بخش، با توجه به ظرفیت جعبه، باکس‌هایی که دوست دارید داخل جعبه باشد را انتخاب کنید.<br>
         با هر بار فشردن تصویر هر باکس، باکس مورد نظر به جعبه انتخابی شما اضافه می شود.<br>
        محدودیتی در تکرار انتخاب باکسی وجود ندارد.<br>
        برای حذف یک باکس انتخاب شده، بر روی تصویر آن در داخل جعبه بزنید.
        </p>
        </div>
<?php
    if(isset($_SESSION["code_error"])){
        $error = $_SESSION["code_error"];
        unset($_SESSION["code_error"]);
    }
?>
        <div id="user_error" class="user_hint error <?php if(!isset($error))echo "hide"; ?>">
            <?php if(isset($error))echo substr($error,1); ?>
        </div>
        
        </div>
        <div class="cut"></div>
        <div class="content">
        <?php
            include ($bu."modules/custom/custom_table_tbody.php"); 
        ?>
        <ul class="s_products">
        
        <?php
            $tb = "products";
            $tb2 = $tb."_price";
            $st = "SELECT $tb.id,$tb.name,$tb.file_name,$tb.first_img_id,$tb2.weight,$tb2.price,$tb2.start_time,$tb.state,$tb.sale_state FROM $tb CROSS JOIN $tb2 WHERE $tb.del_flag = 0 AND $tb.id = $tb2.pid AND $tb2.start_time = (SELECT MAX($tb2.start_time) FROM $tb2 WHERE $tb.category = 'پکیج ولنتاین' AND $tb.type = 'piece' AND $tb2.pid = $tb.id ) ORDER BY $tb.show_order DESC,$tb.id DESC";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "E";
                exit;
            }
            $res = $st->get_result();
            while($row = $res->fetch_assoc()){
                $id = $row["id"];
                $name = $row["name"];
                $file_name = $row["file_name"];
                $state = $row["state"];
                $img = $row["first_img_id"];
                $img_fn = getVarFromDB("content","file_name","id",$img);
                $img_alt = getVarFromDB("content","name","id",$img);
                $weight = $row["weight"];
//                $weight = get_str_index($weight,",")[1];
                $price = $row["price"];
//                $price = get_str_index($price,",")[1];
        ?>
            <li class="s_product" id="<?php echo $id; ?>" onclick="add_to_box(this.children[0])">
                    <div class="img">
                        <img src="<?php echo $s."content/$img_fn"; ?>" alt="<?php echo $img_alt; ?>"/>
                    </div>
                    <?php echo "$name<br>".price_sep($price)." تومان"; ?>
                    <div class="plus">
                    +
                    </div>
            </li>
            <?php
            }
            ?>
        </ul>

        <form action="<?php echo $URL; ?>" method="post">
            <input type="submit" class="btn middle w120p" name="submit" value="تأیید">
        </form>
</div>
</main>
<script src="<?php echo asset_url('js/custom.js'); ?>" type="text/javascript"></script>
<?php
        }
    }
?>


