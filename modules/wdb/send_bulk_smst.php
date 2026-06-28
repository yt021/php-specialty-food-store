<?php 
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    if(!EXTERNAL_INTEGRATIONS_ENABLED) die("SMS disabled in showcase mode");
    include $bu."modules/cart/session_start.php";   
    
    if(isset($_SESSION["a_logged"])){
        if(!isset($_SESSION["admin_sms_bulk"])){
            
            $template = "behdasht";
            
            
            $_SESSION["admin_sms_bulk"] = 1;
        {   
//            $st = "SELECT users.id,name,tel,county FROM users LEFT JOIN addresses ON users.id = addresses.uid WHERE NOT addresses.county = 'شهر تهران' AND users.id < 3305 AND users.id > 3243";


            $st = "SELECT name,tel FROM users WHERE id>6745 ORDER BY id ASC ";
            
            
            // $st = "SELECT name,tel FROM users_for_sms WHERE id>570";
        }
            
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "E";
                exit;
            }
            $res = $st->get_result();
            $k = 0;
            $ck = 0;
            $curly = array();
            $res_curl = array();
            
            
            // $url = 'https://api.ghasedak.io/v2/sms/send/simple';
            $url = getenv('SMS_API_URL') ?: 'https://example.invalid/disabled';
            
            
            $api_key = getenv('SMS_API_KEY') ?: "not-configured";
    
            
            
            
            while($row = $res->fetch_assoc())
            {
                if($k == 18){
                    $mh = curl_multi_init();
                    
                    foreach($curly[$ck] as $curl){
                        curl_setopt($curl["handle"],CURLOPT_URL,$url);
                        curl_setOpt($curl["handle"],CURLOPT_CUSTOMREQUEST, "POST");
                        curl_setOpt($curl["handle"],CURLOPT_HTTPHEADER,array("apikey: $api_key"));
                        curl_setOpt($curl["handle"],CURLOPT_RETURNTRANSFER,true);
                        curl_setOpt($curl["handle"],CURLOPT_POST,1);
                        curl_setOpt($curl["handle"],CURLOPT_POSTFIELDS,$curl["data"]);
                        curl_multi_add_handle($mh,$curl["handle"]);
                    }
                    
                    // $running = null;
                    // do{
                    //     curl_multi_exec($mh,$running);
                    // }while($running>0);
                    
                    // foreach($curly[$ck] as $i=>$ch){
                    //     $res_curl[$ck][$i] = curl_multi_getcontent($ch["handle"]);
                    //     curl_multi_remove_handle($mh,$ch["handle"]);
                    //     echo "$k:".$res_curl[$ck][$i]."<br>";
                    // }
                    // curl_multi_close($mh);
                    
                    // $k = 0;
                    // echo "<br>Multi: $ck <br><br>";
                    // $ck++;
                }
                

                
                $curly[$ck][$k]["data"] = [
                        'type' => "1", 
                        'Receptor' => $row["tel"],
                        'template' => "$template",
                        'param1'=>$row["name"]
                    ];
                $curly[$ck][$k]["handle"] = curl_init();
                
                $k++;
                
                echo $k;
            }
            
        }
    }
        die;

    
    
?>
