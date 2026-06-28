<?php
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/admin/session_start.php";
    
    
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
            $tb = "orders";
            $st = "UPDATE $tb SET recieve_shift = 4 WHERE recieve_shift = 2 AND create_date < '2019-12-02 00:00:00'";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "<br>Error Noon";
            }
            echo "<br>Done - Noon";
            
            $st = "UPDATE $tb SET recieve_shift = 5 WHERE recieve_shift = 3 AND create_date < '2019-12-02 00:00:00'";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "<br>Error Afternoon";
            }
            echo "<br>Done - Afternoon";
            
            
            $st = "UPDATE $tb SET recieve_shift = 3 WHERE recieve_shift = 1 ";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "<br>Error Out of Queue";
            }
            echo "<br>Done - Out of Queue";
            
            $tb = "orders";
            $st = "UPDATE $tb SET recieve_shift = 6 WHERE recieve_shift = 4 AND create_date > '2019-12-02 00:00:00'";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "<br>Error New Noon";
            }
            echo "<br>Done - New Noon";
            
            $tb = "orders";
            $st = "UPDATE $tb SET recieve_shift = 7 WHERE recieve_shift = 5 AND create_date > '2019-12-02 00:00:00'";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "<br>Error New a Noon";
            }
            echo "<br>Done - New a Noon";
            
            
            
            $tb = "orders";
            
            $st = "UPDATE $tb SET post_ref_id = 2 WHERE post_ref_id = 'پیک'";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "<br>Error پیک ویژه";
            }
            echo "<br>Done - پیک ویژه";
            
            $st = "UPDATE $tb SET post_ref_id = 3 WHERE post_ref_id = 'ارسال متفرقه'";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "<br>Error ارسال متفرقه";
            }
            echo "<br>Done - ارسال متفرقه";
            
            
            $st = "UPDATE $tb SET post_ref_id = 5 WHERE post_ref_id = 'تی‌نکست'";
            $st = $mysqli->prepare($st);
            if(!$st->execute()){
                echo "<br>Error تی‌نکست";
            }
            echo "<br>Done - تی‌نکست";
?>
<?php
        }
    }
?>