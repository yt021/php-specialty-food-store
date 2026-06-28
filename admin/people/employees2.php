<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

    $id = $_SESSION[$cf]->id;
    if($id == "new"){
        $row = array(
            "hire_date"=>CaSDate(time()),
            "fire_date"=>NULL,
            "name"=>"",
            
        );
    }else{
    $st = "SELECT name,hire_date,fire_date FROM $tb WHERE id = $id";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    $row["hire_date"] = CaSDate(strtotime($row["hire_date"]));
    $row["fire_date"] = CaSDate(strtotime($row["fire_date"]));
    }
?>
    <form action="<?php echo $URL ?>" method="post">
        <div class="ssrv">
            <div class="ssrv_title">مشخصات</div>        
            <div class="ssrv_dtl">
                <div class="form fr">
                    <div class="form_item ">
                        <label>نام: </label>
                        <input name="name" type="text" value="<?php echo $row["name"]; ?>">
                    </div>
                    <div class="form_item date" id="date_box">
                        تاریخ شروع به فعالیت
                        <label for="day">روز:</label> 
                        <select name="hire_date[day]">
                            <?php
                                for($i=1;$i<=31;$i++){
                                    $selected = "";
                                    if($row["hire_date"]["day"] == $i)$selected=" selected ";
                                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                }
                            ?>
                        </select>
                        <label for="month">ماه:</label> 
                        <select name="hire_date[month]">
                            <?php
                                for($i=1;$i<=12;$i++){
                                    $selected = "";
                                    if($row["hire_date"]["month"] == $i)$selected=" selected ";
                                    include_once $GLOBALS['bu']."modules/jdf.php";
                                    $timetext = jmktime(1,0,0,$i,1,1397);
                                    $month = jdate("F",(int)$timetext,"","","en");
                                    echo '<option '.$selected.' value="'.$i.'">'.$month.'</option>';
                                }
                            ?>
                        </select>
                        <label for="year">سال:</label> 
                        <select name="hire_date[year]">
                            <?php
                                include_once $GLOBALS['bu']."modules/jdf.php";
                                $year = (int)jdate("Y","","","","en");
                                for($i=1397;$i<=$year;$i++){
                                    $selected = "";
                                    if($row["hire_date"]["year"] == $i)$selected=" selected ";
                                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form_item date" id="date_box">
                        تاریخ اتمام همکاری
                        <label for="day">روز:</label> 
                        <select name="fire_date[day]">
                            <?php
                                for($i=1;$i<=31;$i++){
                                    $selected = "";
                                    if($row["fire_date"]["day"] == $i)$selected=" selected ";
                                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                }
                            ?>
                        </select>
                        <label for="month">ماه:</label> 
                        <select name="fire_date[month]">
                            <?php
                                for($i=1;$i<=12;$i++){
                                    $selected = "";
                                    if($row["fire_date"]["month"] == $i)$selected=" selected ";
                                    include_once $GLOBALS['bu']."modules/jdf.php";
                                    $timetext = jmktime(1,0,0,$i,1,1397);
                                    $month = jdate("F",(int)$timetext,"","","en");
                                    echo '<option '.$selected.' value="'.$i.'">'.$month.'</option>';
                                }
                            ?>
                        </select>
                        <label for="year">سال:</label> 
                        <select name="fire_date[year]">
                            <?php
                                include_once $GLOBALS['bu']."modules/jdf.php";
                                $year = (int)jdate("Y","","","","en");
                                for($i=1397;$i<=$year;$i++){
                                    $selected = "";
                                    if($row["fire_date"]["year"] == $i)$selected=" selected ";
                                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
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