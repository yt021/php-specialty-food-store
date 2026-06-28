<?php
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Cache-Control: max-age=0');

    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/admin/session_start.php";

    include_once($bu."modules/xlsxwriter.class.php");
    
?>
<?php
    $column_name = "orders.p_send_date";
    $where_and = 1;
    $sq_date["start"] = CaSDate(time());
    $sq_date["end"] = CaSDate(time()+25*3600);
    
    
    $date_name = "rec_date";
    if(isset($_POST[$date_name])){
        $_SESSION[$cf]->sql_where[$date_name]["day"] = $_POST["day"];
        $_SESSION[$cf]->sql_where[$date_name]["month"] = $_POST["month"];
        $_SESSION[$cf]->sql_where[$date_name]["year"] = $_POST["year"];
        $sq_date["start"] = $_SESSION[$cf]->sql_where[$date_name];
        $sq_date["end"] = CaSDate(strtotime(cSQLTimefS($sq_date["start"]))+25*3600);
    }
    $where2 = db_time_condition($column_name,$sq_date["start"],$sq_date["end"],$where_and);
    
    // Shift
    $where3 = "";
    $where3 = " AND recieve_shift = ".$_POST["shift"]." ";
    
?>
<?php
//    Read Static Rows of Excel (Top Rows)
    $sleid = last_id('sle_set');
    
    $st = "SELECT row_n,row_to_str FROM send_list_excels_static_rows WHERE sleid = $sleid ORDER BY row_n ASC";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $excel_rows = array();
    while($row2 = $res->fetch_assoc()){
        $excel_rows[$row2["row_n"]] = explode(",",$row2["row_to_str"]);
    }  

    $nosr = sizeof($excel_rows);    // Number of Static Rows
    
//    Read Column Type & Values
    $st = "SELECT column_name,type,value FROM send_list_excels_column_values WHERE sleid = $sleid ORDER BY column_name ASC";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $column_tv = array();
    $res = $st->get_result();
    while($row2 = $res->fetch_assoc()){
        $column_tv[(int)$row2["column_name"]] = [$row2["type"],$row2["value"]];
    }
//    Read Detail Column Value (if exists)
    $st = "SELECT value FROM send_list_excels_column_values WHERE sleid = $sleid AND type = 'detail' LIMIT 1";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $st->store_result();
    $st->bind_result($detail_column_value);
    $st->fetch();
//    echo "$detail_column_value";
    if($detail_column_value){
        $divide_point = stripos($detail_column_value,"|||");
        $detail_text = substr($detail_column_value,0,$divide_point);
        $dt_var_no = substr_count($detail_text,"«متغیر»");
        $detail_variables = substr($detail_column_value,$divide_point+3);
        $detail_variables = explode(",",$detail_variables);
    }
    
    
    $tb = "orders";
    $st = "SELECT 
        $tb.id,
        users.name,
        users.tel,
        addresses.county,
        addresses.city,
        addresses.address,
        addresses.post_code,
        addresses.rec_name,
        addresses.rec_tel,
        addresses.rec_tel_2,
        addresses.janitor,
        $tb.cart_price,
        $tb.p_send_date,
        $tb.recieve_date,
        $tb.recieve_shift
        FROM $tb 
        LEFT JOIN users ON $tb.uid = users.id
        LEFT JOIN addresses ON $tb.aid = addresses.id
        LEFT JOIN sd_shifts ON $tb.recieve_shift = sd_shifts.id
        WHERE 
        $tb.del_flag = 0 AND 
        $tb.state = 3 AND 
        $tb.recieve_shift IS NOT NULL AND 
        sd_shifts.transporter_id > 3 
        $where2 
        $where3
        ORDER BY $tb.recieve_date ASC,$tb.recieve_shift ASC,id ASC";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    
    $file_name = "تاریخ ".correctDate_ts(create_tsDate($sq_date["start"])).cor_sendShift($_POST['shift']);
    $k = 1;
    while($row = $res->fetch_assoc())
    {
        
        $id = $row["id"];        

        if($row["rec_name"])$row["name"] = $row["rec_name"];
        if($row["rec_tel"])$row["tel"] = $row["rec_tel"];
        
        
        
        if($row["janitor"] == "yes")$row["janitor"] = "در صورت عدم حضور تحویل نگهبان/سرایدار شود.";
        else $row["janitor"] = "";
        
        $row["row_number"] = $k;
        
        $row["send_date"] = correctDate_rqm($row["p_send_date"]);
        $row["rec_date"] = correctDate_rqm($row["recieve_date"]);
        $row["rec_date_day"] = CaSDate(strtotime($row["recieve_date"]))["day_name"];
        $row["rec_shift"] = cor_sendShift($row["recieve_shift"]);
//        Feed Detail Column Data
        $row["detail"] = "";
        if($detail_column_value){
            $needle = "«متغیر»";
            $ndll = strlen($needle);
            $row["detail"] = $detail_text;
            for($vp=0;$vp<$dt_var_no;$vp++){
                $last_var_pos = stripos($row["detail"],$needle);
                $row["detail"] = substr($row["detail"],0,$last_var_pos).$row[$detail_variables[$vp]].substr($row["detail"],$last_var_pos+$ndll+1);
            }
            
        }
        
        $new_excel_row = array();
        
        foreach($column_tv as $column=>$tv){
            switch($tv[0]){
                case 'empty':
                    $new_excel_row[$column] = "";
                    break;
                case 'constant':
                    $new_excel_row[$column] = $tv[1];
                    break;
                case 'variable':
                    $new_excel_row[$column] = $row[$tv[1]];
                    break;
                case 'detail':
                    $new_excel_row[$column] = $row["detail"];
                    break;
                
            }
        }
        
        $excel_rows[$k+$nosr] = $new_excel_row;
        $k++;
    }
    header('Content-Disposition: attachment;filename="لیست ارسال '.$file_name.'.xlsx"');
    $writer = new XLSXWriter();
    $writer->setRightToLeft(true);
    $writer->writeSheet($excel_rows);
    $writer->writeToStdOut();
?>
