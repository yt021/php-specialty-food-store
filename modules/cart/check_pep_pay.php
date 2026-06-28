<?php
    error_reporting(0);
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/cart/session_start.php";
    
    include $bu.'modules/pep/pep_config.php';
    
    $tb = "orders";
    $st = "SELECT id,create_date,pay_price,p_send_date FROM $tb WHERE del_flag = 0 AND state = 0 AND create_date > NOW() - INTERVAL 30 MINUTE";

    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $k=0;
    while($row = $res->fetch_assoc())
    {
        $k++;
        $oid = $row["id"];
        $amount = 10*(int)$row["pay_price"];
        $fields["invoiceNumber"] = $oid;
        $fields["invoiceDate"] = $row["create_date"];
        $fields["merchantCode"] = $merchant_code;
        $fields["terminalCode"] = $terminal_code;
        $result = pep_post_req($fields,$ctr_url);
        $bcd = pep_xmlres_parser($result)["resultObj"];
        // evaluating pay status
        if($bcd["result"] == "True"){
            //evaluating correct request
            if($bcd["action"] == "1003"){
                // evaluating amount consistency
                if($bcd["amount"] == $amount){
                    // increasing send date no of orders (for check max limit)
                    $day_id=$row["p_send_date"];
                    if(var_exist($day_id,"send_date","date")){
                        $day_id=getVarFromDB("send_date","id","date",$day_id);
                    }else{$day_id=0;}
                    
                    $tref_id = $bcd['transactionReferenceID'];
                    $trace_no = $bcd['traceNumber'];
                    $ref_no = $bcd['referenceNumber'];
                    
                    $pep_verify_res = pep_verifyPayment($bcd);
                    if($pep_verify_res["result"] == "True"){
                        if(db_pay_order_pep($oid,$tref_id,$trace_no,$ref_no,$day_id)){
                            if(getVarFromDB("orders","recieve_shift","id",$oid) == 3){
                                $tel = getVarFromDB('users',"tel","id",getVarFromDB('orders',"uid","id",$oid));
                                send_sms_temp('09000000000','peykorder',$oid,$tel);
                                send_sms_temp('09000000000','peykorder',$oid,$tel);
                            }
                        }
                    }
                }
            }
        }        
    }
    $st = "INSERT INTO ppc_cronjob (status) Values ($k)";
    $st = $mysqli->prepare($st);
    $st->execute();
    exit();
?>
