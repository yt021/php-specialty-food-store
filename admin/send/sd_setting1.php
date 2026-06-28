<?php
    if(isset($indexed)){
        if($indexed == 1){?>
    <form action="<?php echo $URL; ?>" method="post">
        <div class="ssrv">
            <div class="ssrv_title">تنظیمات</div>        
            <div class="ssrv_dtl">
                <div class="form fr">
<?php
    $st = "SELECT * FROM $tb WHERE NOT flag = 'tp_id'";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row = $res->fetch_assoc())
    {
?>  
                    <div class="form_item">
                        <label><?php echo $row["name"]; ?>: </label>
                        <input name="<?php echo $row["flag"]; ?>" type="text" value="<?php echo round($row["value"],4); ?>">
                    </div>
<?php
    }
    $st = "SELECT * FROM $tb WHERE flag = 'tp_id'";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row = $res->fetch_assoc())
    {
?>
                </div>
            </div>
        </div>
        <div class="ssrv">
            <div class="ssrv_title">ارسال‌کننده</div>        
            <div class="ssrv_dtl">
                <div class="form fr">
                    <div class="form_item">
                        <label><?php echo $row["name"]; ?>: </label>
                        <select name="<?php echo $row["flag"]; ?>">
<?php
    $st = "SELECT id,name FROM transporters
    WHERE del_flag = 0 AND id > 3";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res2 = $st->get_result();
    while($row2 = $res2->fetch_assoc())
    {
        $name = $row2["name"];
        $id = $row2["id"];
        $selected = "";
        if($row["value"] == $id)$selected=" selected ";
?>
                            <option <?php echo $selected; ?> value="<?php echo $id; ?>"><?php echo $name; ?></option>
<?php
    }
?>
                        </select>
                    </div>
<?php
    }
?>                      
                </div>
            </div>
        </div>                
        <input class="btn" type="submit" name="edit" value="ثبت">
    </form>
<?php
        }
    }
?>