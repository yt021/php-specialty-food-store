<?php
    include_once($bu."admin/orders/send_detail_barcode_fixies.php");

    $left  = str_split(str_pad((int)($number / 100000) , 5 , "0" , STR_PAD_LEFT));
    $right = str_split(str_pad((int)($number % 100000) , 5 , "0" , STR_PAD_LEFT));
    
    $type = 0;
    $check = 3*((int)$left[1] + (int)$left[3] + (int)$right[0] + (int)$right[2] + (int)$right[4])
    + ((int)$right[1] + (int)$right[3] + (int)$left[0] + (int)$left[2] + (int)$left[4]);
    $check = (10 - ($check % 10))%10;
    
    
    

    
    
?>

<div class="upc">
    <ul>
        <?php
            echo create_gaurd('q');
            echo create_gaurd('s');
            
            //left digits
            echo create_digit($type,'l g');
            
            foreach($left as $l){
                echo create_digit((int)$l,'l');
            }
            
            
            echo create_gaurd('m');
            //right digits
            foreach($right as $r){
                echo create_digit((int)$r,'r');
            }
            
            echo create_digit($check,'r g');
            
            echo create_gaurd('e');
            echo create_gaurd('q');
        ?>

    </ul>
    <div class="vis type"><?php echo $type; ?></div>
    <div class="vis lv"><?php echo implode("",$left); ?></div>
    <div class="vis rv"><?php echo implode("",$right); ?></div>
    <div class="vis check"><?php echo $check; ?></div>
</div>

