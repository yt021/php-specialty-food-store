<?php 
    error_reporting(0);
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/cart/session_start.php";   
    
   

            // echo 'start<br><br><br>';
            
            $st = "SELECT offset FROM mail_cronjob ORDER BY id DESC LIMIT 1";
            $offset = return_sel_sql($st)->fetch_assoc()['offset'];
            if(!$offset)$offset = 0 ;
            $limit = 30;
            
            $_SESSION["admin_sms_bulk"] = 1;
            $st = "SELECT id,name,email FROM users WHERE email IS NOT NULL AND email != 'NULL' AND email != '' ORDER BY id ASC LIMIT $limit OFFSET $offset ";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "E";
                exit;
            }
            $res = $st->get_result();
            
            
            
            while($row = $res->fetch_assoc())
            {
                
                $name = $row['name'];
                $email = $row['email'];
                $subject = "فروش عیدانه";
                $file_name = "Image6460191308.jpg";
                $file_path = $bu."/content/$file_name";
                
                send_sale_mail($subject,$name,$email,$file_name,$file_path);
                // echo  $k."-".$row['id']. "-" .$email. "<br>" ;
                
                $k++;
                
                
            }
            // echo $offset;
            $offset += $limit;
            $st = "INSERT INTO mail_cronjob (offset) Values ($offset)";
            $st = $mysqli->prepare($st);
            $st->execute();
            

        die;

    
    
?>