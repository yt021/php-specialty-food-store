<?php
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/cart/session_start.php";
    
    $sq_date["start"] = CaSDate(time()-0*3600);
    $sq_date["end"] = CaSDate(time()+24*3600);
    $column_name = "orders.recieve_date";
    $where_and = 1;
    $where = db_time_condition($column_name,$sq_date["start"],$sq_date["end"],$where_and);
    
    $tb = "orders";
    $st = "SELECT $tb.id,users.name,users.tel,$tb.recieve_date,$tb.recieve_shift FROM $tb 
    LEFT JOIN users ON $tb.uid = users.id
    LEFT JOIN sd_shifts ON $tb.recieve_shift = sd_shifts.id
    WHERE 
    $tb.del_flag = 0 AND 
    $tb.state = 4 AND 
    $tb.recieve_shift IS NOT NULL AND 
    $tb.recieve_shift IS NOT NULL AND 
    sd_shifts.transporter_id > 3 AND
    $tb.ssmss IS NULL
    $where ";
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
        $tel = $row["tel"];
        $rec_date = correctDate($row["recieve_date"]);
        $rec_shift = $row["recieve_shift"];
        $rec_shift = cor_sendShift_client($rec_shift);
        // $sst = send_sms_temp($tel,'sendTehran',$name,$id,"$rec_date - $rec_shift");
        // updateInDB($tb,"ssmss",$sst[1],"id",$id);
    }
    
    $st = "INSERT INTO cronjob_test (test) VALUES ('400')";
    $st = $mysqli->prepare($st);
    $st->execute();
    
    die();
?>
