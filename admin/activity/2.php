<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $fields[1] = ["name","insta_id","ad_current_followers"];
    $fields[21] = ["cost_total","cost_indirect"];
    $fields[22] = ["cost_direct","cost_oid"];
    $fields[3] = ["ad_view","follower_increase"];
    $labels = array(
        "name"=>"نام",
        "insta_id"=>"شناسه اینستاگرام",
        "ad_current_followers"=>"تعداد مخاطب فعلی",
        "ad_date"=>"تاریخ تبلیغ",
        "cost_total"=>"مجموع هزینه",
        "cost_direct"=>"هزینه نقدی",
        "cost_indirect"=>"هزینه غیر نقدی",
        "cost_oid"=>"شناسه سفارش مربوطه",
        "ad_view"=>"تعداد مشاهده تبلیغ",
        "follower_increase"=>"میزان جذب مخاطب",
    );
    $id = $_SESSION[$cf]->id;
    if($id == "new"){
        $row = array(
            "name"=>NULL,
            "insta_id"=>NULL,
            "ad_current_followers"=>NULL,
            "ad_date"=>CaSDate(time()),
            "cost_total"=>0,
            "cost_direct"=>0,
            "cost_indirect"=>0,
            "cost_oid"=>NULL,
            "ad_view"=>NULL,
            "follower_increase"=>NULL,
        );
    }else{
    $st = "SELECT * FROM $tb WHERE id = $id";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    $row["cost_indirect"] = getVarFromDB("orders","sale_total","id",$row["cost_oid"]);
    $row["cost_total"] = $row["cost_direct"]+$row["cost_indirect"];
    $row["ad_date"] = CaSDate(strtotime($row["ad_date"]));
    }
?>
    <form action="<?php echo $URL ?>" method="post">
        <div class="ssrv">
            <div class="ssrv_title">مجری تبلیغ</div>        
            <div class="ssrv_dtl">
                <div class="form fr">
<?php
    foreach($fields[1] as $f){
?>
        <div class="form_item">
            <label><?php echo $labels[$f]; ?>: </label>
            <input name="<?php echo $f; ?>" type="text" value="<?php echo $row[$f]; ?>">
        </div>
<?php
    }
?>
                </div>
            </div>
        </div>
        <div class="ssrv">
            <div class="ssrv_title">اطلاعات تبلیغ</div>        
            <div class="ssrv_dtl">
                <div class="form fr">
                
                    <div class="form_item date" id="date_box">
                        تاریخ تبلیغ
                        <label for="day">روز:</label> 
                        <select name="day">
                            <?php
                                for($i=1;$i<=31;$i++){
                                    $selected = "";
                                    if($row["ad_date"]["day"] == $i)$selected=" selected ";
                                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                }
                            ?>
                        </select>
                        <label for="month">ماه:</label> 
                        <select name="month">
                            <?php
                                for($i=1;$i<=12;$i++){
                                    $selected = "";
                                    if($row["ad_date"]["month"] == $i)$selected=" selected ";
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
                                    if($row["ad_date"]["year"] == $i)$selected=" selected ";
                                    echo '<option '.$selected.' value="'.$i.'">'.$i.'</option>';
                                }
                            ?>
                        </select>
                    </div>
<?php
    foreach($fields[21] as $f){
?>
        <div class="form_item">
            <label><?php echo $labels[$f]; ?>: </label>
            <input disabled type="text" value="<?php echo $row[$f]; ?>">
        </div>
<?php
    }
?>                    
<?php
    foreach($fields[22] as $f){
?>
        <div class="form_item">
            <label><?php echo $labels[$f]; ?>: </label>
            <input name="<?php echo $f; ?>" type="text" value="<?php echo $row[$f]; ?>">
        </div>
<?php
    }
?>
                </div>
            </div>
        </div>
        <div class="ssrv">
            <div class="ssrv_title">نتیجه تبلیغ</div>        
            <div class="ssrv_dtl">
                <div class="form fr">
<?php
    foreach($fields[3] as $f){
?>
        <div class="form_item">
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