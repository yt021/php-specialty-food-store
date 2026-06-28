<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $state_names = explode(",",getVarFromDB("admin_modules","state_names","flag",$cf));
    $state_flags = explode(",",getVarFromDB("admin_modules","state_flags","flag",$cf));
    foreach($state_flags as $key=>$flag){
?>
        <a onclick="sub_show('state','<?php echo $flag; ?>')" class="btn half fr admin_dashboard" ><?php echo $state_names[$key]; ?></a>
<?php }
?>
    </div>  
</div>            
<?php
        }
    }
?>