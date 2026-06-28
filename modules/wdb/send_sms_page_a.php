<?php 
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    if(!EXTERNAL_INTEGRATIONS_ENABLED) die("SMS disabled in showcase mode");
    include $bu."modules/cart/session_start.php";   
    
    $sms_pass = 'not-configured';
    
    $limit = 100;
    
    
    $url = "https://example.invalid/disabled";
    
    
    if(isset($_POST['sms_pass'],$_POST['message'],$_POST['start'])){
        if($_POST['sms_pass'] == $sms_pass){
            
            $start = $_POST['start']*$limit;
            // $start = 0;
            $st = "INSERT INTO sms_check (sms_pass,start) VALUES (?,?) ";
            $st = $mysqli->prepare($st);
            $st->bind_param('ss',$sms_pass,$start);
            $st->execute();
        
            $end = $start+$limit;
        
            $st = "SELECT id,name,tel FROM users WHERE id>? AND id<=?";
            $st = $mysqli->prepare($st);
            $st->bind_param('ss',$start,$end);
            $st->execute();
            $res = $st->get_result();
            $k = 1;
            $ck = 0;
            $curly = array();
            $res_curl = array();
            
            
    
            while($row = $res->fetch_assoc())
            {
                // echo $row['id'].'<br>';
                if($k == 20){
                    $mh = curl_multi_init();
                    
                    foreach($curly[$ck] as $curl){
                        curl_setopt($curl["handle"],CURLOPT_URL,$url);
                        curl_setOpt($curl["handle"],CURLOPT_CUSTOMREQUEST, "POST");
//                        curl_setOpt($curl["handle"],CURLOPT_HTTPHEADER,array("apikey: $api_key"));
                        curl_setOpt($curl["handle"],CURLOPT_RETURNTRANSFER,true);
//                        curl_setOpt($curl["handle"],CURLOPT_POST,1);
                        curl_setOpt($curl["handle"],CURLOPT_POSTFIELDS,$curl["data"]);
                        curl_multi_add_handle($mh,$curl["handle"]);
                    }
                    
                    
//                      $running = null;
//                      do{
//                          curl_multi_exec($mh,$running);
//                      }while($running>0);
// //                    
//                      foreach($curly[$ck] as $i=>$ch){
//                          $res_curl[$ck][$i] = curl_multi_getcontent($ch["handle"]);
//                          curl_multi_remove_handle($mh,$ch["handle"]);
//                          // echo "$k:".$res_curl[$ck][$i]."<br>";
//                      }
//                      curl_multi_close($mh);
// //                    
//                      $k = 1;
//                      echo "<br>Multi: $ck <br><br>";
// //                    
//                      $ck++;
                }
                
                $message = str_replace('/param/',$row['name'],$_POST['message']);
                
                $rcpt_nm = array($row["tel"]);
                $param = array
                            (
                                'uname'=>'09000000000',
                                'pass'=>'faraz0013410857',
                                'from'=>'98club',
                                'message'=>$message,
                                'to'=>json_encode($rcpt_nm),
                                'op'=>'send'
                            );
                
                
                $curly[$ck][$k]["data"] = $param;
                
                $curly[$ck][$k]["handle"] = curl_init();
                
                $k++;
                
                // echo $k;
            }
            echo "<br><br>Done";
        }
    }
    die();
?>
