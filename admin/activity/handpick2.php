<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $fields[1] = ["t_weight","w_weight"];
    
    $labels = array(
        "t_weight"=>"وزن اولیه",
        "w_weight"=>"وزن ضایعات",
    );
    $traffic_labels = ["","ورود","خروج"];
    $id = $_SESSION[$cf]->id;
    if($id == "new"){
        $row = array(
            "date"=>CaSDate(time()),
            "eid"=>1,
            "pid"=>1,
            "t_weight"=>0,
            "w_weight"=>0,
            "sid"=>1
        );
    }else{
    $st = "SELECT eid,v1,v2,v3,v4,v5 FROM $tb WHERE id = $id";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    $row["pid"] = $row["v1"];
    $row["t_weight"] = $row["v2"];
    $row["w_weight"] = $row["v4"];
    $row["sid"] = $row["v5"];
    $row["date"] = CaSDate(strtotime($row["v3"]));
    }
?>
    <form action="<?php echo $URL ?>" method="post">
        <div class="ssrv">
            <div class="ssrv_title">تردد</div>        
            <div class="ssrv_dtl">
                <div class="form fr">
                    <div class="form_item  ">
                        <label>شخص: </label>
                        <select name="eid">
<?php
    $st = "SELECT id,name FROM employees
    WHERE del_flag = 0";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row2 = $res->fetch_assoc())
    {
        $name = $row2["name"];
        $id = $row2["id"];
        $selected = "";
        if($row["eid"] == $id)$selected=" selected ";
?>
        <option <?php echo $selected; ?> value="<?php echo $id; ?>"><?php echo $name; ?></option>
<?php
    }
?>
                        </select>
                    </div>
                    <div class="form_item date" id="date_box">
                        تاریخ
                        <label for="day">روز:</label> 
                        <select name="day">
                            <?php
                                for($i=1;$i<=31;$i++){
                                    $selected = "";
                                    if($row["date"]["day"] == $i)$selected=" selected ";
                                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                }
                            ?>
                        </select>
                        <label for="month">ماه:</label> 
                        <select name="month">
                            <?php
                                for($i=1;$i<=12;$i++){
                                    $selected = "";
                                    if($row["date"]["month"] == $i)$selected=" selected ";
                                    include_once $GLOBALS['bu']."modules/jdf.php";
                                    $timetext = jmktime(1,0,0,$i,1,1397);
                                    $month = jdate("F",(int)$timetext,"","","en");
                                    echo '<option '.$selected.' value="'.$i.'">'.$month.'</option>';
                                }
                            ?>
                        </select>
                        <label for="year">سال:</label> 
                        <select name="year">
                            <?php
                                include_once $GLOBALS['bu']."modules/jdf.php";
                                $year = (int)jdate("Y","","","","en");
                                for($i=1397;$i<=$year;$i++){
                                    $selected = "";
                                    if($row["date"]["year"] == $i)$selected=" selected ";
                                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form_item date ">
                        <label>محصول: </label>
                        <select name="pid">
<?php
    $st = "SELECT id,name FROM products
    WHERE del_flag = 0";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row2 = $res->fetch_assoc())
    {
        $name = $row2["name"];
        $id = $row2["id"];
        $selected = "";
        if($row["pid"] == $id)$selected=" selected ";
?>
        <option <?php echo $selected; ?> value="<?php echo $id; ?>"><?php echo $name; ?></option>
<?php
    }
?>
                        </select>

                        <label>تامین‌کننده: </label>
                        <select name="sid">
<?php
    $st = "SELECT id,name FROM suppliers
    WHERE del_flag = 0";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row2 = $res->fetch_assoc())
    {
        $name = $row2["name"];
        $id = $row2["id"];
        $selected = "";
        if($row["sid"] == $id)$selected=" selected ";
?>
        <option <?php echo $selected; ?> value="<?php echo $id; ?>"><?php echo $name; ?></option>
<?php
    }
?>
                        </select>
                    </div>
<?php
    foreach($fields[1] as $f){
?>
        <div class="form_item multi">
            <label><?php echo $labels[$f]; ?>: </label>
            <input name="<?php echo $f; ?>" type="text" value="<?php echo $row[$f]; ?>">
        </div>
<?php
    }
?>                    
                    
                </div>
            </div>
        </div>        
        <input type="submit" name="edit" class="btn" value="ثبت">
    </form>

<?php
        }
    }
?>