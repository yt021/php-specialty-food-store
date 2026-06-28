<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $fields[1] = ["hour","minute"];
    $labels = array(
        "hour"=>"ساعت",
        "minute"=>"دقیقه",
    );
    $traffic_labels = ["","ورود","خروج"];
    $id = $_SESSION[$cf]->id;
    if($id == "new"){
        $row = array(
            "date"=>CaSDate(time()),
            "hour"=>jdate("G",time(),"","","en"),
            "minute"=>jdate("i",time(),"","","en"),
            "traffic"=>1,
            "eid"=>1
        );
    }else{
    $st = "SELECT v1,v2,v3 FROM $tb WHERE id = $id";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    $row["time"] = $row["v1"];
    $row["hour"] = (int)($row["time"]/60);
    $row["minute"] = $row["time"]%60;
    $row["traffic"] = $row["v2"];
    $row["date"] = CaSDate(strtotime($row["v3"]));
    }
    
    $row["eid"] = $_SESSION[$cf]->sql_where["eid"];
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
<?php
    foreach($fields[1] as $f){
?>
        <div class="form_item ">
            <label><?php echo $labels[$f]; ?>: </label>
            <input name="<?php echo $f; ?>" type="text" value="<?php echo $row[$f]; ?>">
        </div>
<?php
    }
?>                    
                    <div class="form_item  ">
                        <label>تردد: </label>
                        <select name="traffic">
                            <?php
                                for($i=1;$i<=2;$i++){
                                    $selected = "";
                                    if($row["traffic"] == $i)$selected=" selected ";
                                    echo '<option '.$selected.' value="'.$i.'">'.$traffic_labels[$i].'</option>';
                                }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>        
        <input type="submit" name="edit" class="btn" value="ثبت">
    </form>

<?php
        }
    }
?>