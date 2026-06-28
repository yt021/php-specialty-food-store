<?php 
require_once __DIR__ . '/../pinket/helpers.php';
require_once __DIR__ . '/../pinket/status-webhook.php';
global $mysqli;
    if(isset($indexed)){
        if($indexed == 1){

// Bulk Import
$url = 'https://example.invalid/disabled';
echo "<div style='direction:ltr'>";
unset($data);
$data = array();
$data['user'] = array(
    'username'=>'disabled',
    'password'=>'not-configured'
);

if(isset($_SESSION["a_logged"])){
    if(isset($_POST["ms_submit"])){
        $ids = get_str_index($_POST["ms_submit"],",")[1];
        foreach($ids as $oid){
            if(getVarFromDB('orders','ssmss','id',$oid) == null && getVarFromDB('orders','recieve_shift','id',$oid) > 3 && getVarFromDB('orders','state','id',$oid) == 3){
                
                $aid = getVarFromDB('orders','aid','id',$oid);
                $uid = getVarFromDB('orders','uid','id',$oid);
                
                $cn = array(
                    'reference'=>$oid,
                    'date'=> date("Y-m-d",strtotime(getVarFromDB('orders','p_send_date','id',$oid))),
                    'assinged_pieces'=>'1',
                    'service'=>'1',
                    'value'=>'0',
                    'inv_value'=>'0',
                    'payment_term'=>"0",
                    'weight'=>'1',
                    'content'=>'میوه خشک',
                    'note'=>getVarFromDB('addresses','janitor','id',$aid) == 'yes'?'در صورت عدم حضور به سرایدار/نگهبان تحویل شود':''
                ); 
                $rec = array(
                    'person'=>getVarFromDB('addresses','rec_name','id',$aid)?getVarFromDB('addresses','rec_name','id',$aid):getVarFromDB('users','name','id',$uid),
                    'company'=>'',
                    'city_no'=>"10866",
                    'telephone'=> "",
                    'mobile'=>getVarFromDB('addresses','rec_tel','id',$aid)?getVarFromDB('addresses','rec_tel','id',$aid):getVarFromDB('users','tel','id',$uid),
                    'email'=>"",
                    'address'=>getVarFromDB('addresses','address','id',$aid)
                    // 'post_code'=>"10866"
                );
                $sender = array(
                    'person'=>"میوه خشک آبان",
                    'company'=>"",
                    'city_no'=>'10866',
                    'telephone'=>"",
                    'mobile'=>'989000000000',
                    'email'=>"",
                    'address'=>"میوه خشک آبان"
                    // 'post_code'=>"10866"
                );
                
                
                if(isset($data['bulk']))
                unset($data['bulk']);
                $data['bulk'] = array(
                    'cn'=>$cn,
                    'sender'=>$sender,
                    'receiver'=>$rec
                );
                
                // echo json_encode($data);
                // echo "<br><br><br>";
                
                
                
                ///////////////////////////////history read
                // $url = 'https://api-ppt.chabok.app/bulk_history_report';
                // $url = 'https://api-ppt.chabok.app/report?';
                // $url = 'https://api-ppt.chabok.app/tracking?';
                // $url = 'https://api-ppt.chabok.app/cancel_pickup?';
                
                // unset($data['bulk']);
                // $data['bulk'] = array("$oid");
                // $data['date'] = array(
                    // 'from'=>'2022-07-29',
                    // 'to'=>'2022-08-02'
                    // );
                // $data['order'] = array('reference'=>'44054');
                // $data['consignment_no'] = '910824856';
                // $data['reason'] = 'test_api';
                
                $data_en = json_encode($data);
                $data_en = str_replace('"bulk":{','"bulk":[{',$data_en);
                $data_en = str_replace('}}}','}}]}',$data_en);
                // echo $data."<br><br><br>";
                $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => array('input'=>$data_en),
                CURLOPT_HTTPHEADER => array(
                    "APP-AUTH:aW9zX2N1c3RvbWVyX2FwcDpUUFhAMjAxNg==",
                    "content-type:  multipart/form-data;charset=utf-8"
                    )
                )
                );
                $response = curl_exec($curl);
                $err = curl_error($curl);
                $response = utf8_encode($response);
                $err = utf8_encode($err);
                
                // var_dump(json_decode($response));
                
                $response = json_decode($response);
                // echo "<pre style='direction:ltr;'>";
                    // print_r($response);
                // echo "</pre>";
                // echo "<br><br><br>";
                // var_dump($response);
                // echo $response->objects->result[0]->tracking."<br><br>3";
                if($response->result){
                    updateInDB('orders','ssmss',$response->objects->result[0]->tracking,'id',$oid);
                    // echo "سفارش $oid:".$response->message;
                    // echo "<br>";
                    // echo "<pre style='direction:ltr;'>";
                    // print_r($response);
                    // echo "</pre>";
                }else{
                    // if con_no is not set in db
                    if(isset($response->objects->result[0]->reference,$response->objects->result[0]->consignment_no) && $response->objects->result[0]->reference == $oid){
                        updateInDB('orders','ssmss',$response->objects->result[0]->consignment_no,'id',$oid);
                    }
                    // echo "سفارش $oid:".$response->message;
                }
                
                curl_close($curl);
                
                // echo "<br><br><br>";
                // var_dump($err);

                // After PPT API call and updating order, notify Pinket if this is a Pinket order
                $order = getRow($mysqli, "orders", "id = ?", [$oid], "i");
                if ($order && !empty($order['unified_id']) && !ctype_digit($order['unified_id'])) {
                    file_put_contents(__DIR__.'/pinket_debug.log', "Trying Pinket for OID: $oid\n", FILE_APPEND);
                    updateOrderStatusInPinket($oid);
                }
            }
        }
        // Ensure Pinket webhook is called for all Pinket orders in the batch
        foreach($ids as $oid){
            $order = getRow($mysqli, "orders", "id = ?", [$oid], "i");
            if ($order && !empty($order['unified_id']) && !ctype_digit($order['unified_id'])) {
                file_put_contents(__DIR__.'/pinket_debug.log', "Trying Pinket for OID: $oid\n", FILE_APPEND);
                updateOrderStatusInPinket($oid);
            }
        }
    }
}
echo "</div>";

        }
    }

?>
