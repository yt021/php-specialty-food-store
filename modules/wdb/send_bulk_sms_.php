<?php 
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/cart/session_start.php";   
    
    if(isset($_SESSION["a_logged"])){
        if(!isset($_SESSION["admin_sms_bulk"])){
            
            $_SESSION["admin_sms_bulk"] = 1;
            
            $st = "SELECT users.id,name,tel,county FROM users LEFT JOIN addresses ON users.id = addresses.uid WHERE NOT addresses.county = 'شهر تهران' AND users.id < 3305 AND users.id > 3243";
            // $st = "SELECT name,tel FROM users_for_sms WHERE id>570";
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
                $name = $row["name"];
                $tel = $row["tel"];
                
                // $tel = "0".$row["tel"];
                
                // send_sms_temp($tel,"Dastres",$name);
                
                $message = "$name عزیز
فقط امروز می تونیم سفارش یلداییتونو برای شهرهای به جز تهران بپذیریم!
*میوه خشک آبان*
https://abanfruit.com";
                // send_sms($tel,$message);
                
                echo $k;
            }
            
        }
    }
        die;

    
    
?>