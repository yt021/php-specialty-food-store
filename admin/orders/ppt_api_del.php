<?php 
    if(isset($indexed)){
        if($indexed == 1){

// Cancel Pickup
$url = 'https://example.invalid/disabled';
echo "<div style='direction:ltr'>";
unset($data);
$data = array();
$data['user'] = array(
    'username'=>'disabled',
    'password'=>'not-configured'
);
if(isset($_SESSION["a_logged"])){
    if(isset($_POST["ms_del"])){
        $ids = get_str_index($_POST["ms_del"],",")[1];
        foreach($ids as $oid){
            if(getVarFromDB('orders','ssmss','id',$oid) && getVarFromDB('orders','recieve_shift','id',$oid) > 3 && getVarFromDB('orders','state','id',$oid) == 3){
                

                $data['consignment_no'] = getVarFromDB('orders','ssmss','id',$oid);
                $data['reason'] = 'delete';
                
                $data_en = json_encode($data);

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
                $response = json_decode($response);

                if($response->result){
                    updateInDB('orders','ssmss',null,'id',$oid);
                }else{
                    if($response->message == 'بارنامه وجود ندارد'){
                        updateInDB('orders','ssmss',null,'id',$oid);
                    }
                }
            }
        }
    }
}
echo "</div>";

        }
    }

?>
