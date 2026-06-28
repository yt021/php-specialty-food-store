<?php
    include_once($bu."admin/orders/send_detail_barcode_fixies.php");
    $number = str_split(strval((int)$number));
     
    
    // Type A
    $check = $num_val['sa'];
    
    foreach($number as $k=>$n){
        $check += ($k+1)*$num_val[(int)$n];
    }
    $check = $check % 103;
    
    

    
    
?>

<div class="upc">
    <ul style="width:<?php echo 0.33*(9*2 + 11*(3+sizeof($number))+4) ?>mm">
        <?php
            
            echo create_gaurd('q');
            
            echo create_digit('sa','r');

            foreach($number as $n){
                echo create_digit((int)$n,'r');
            }
            echo create_digit($check,'check');

            echo create_digit('sp','r');
            
            echo create_gaurd('q');
        ?>

    </ul>
    <div class="vis check cb"><?php echo implode("",$number); ?></div>
</div>

