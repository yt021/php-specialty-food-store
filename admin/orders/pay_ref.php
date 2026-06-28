<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $now = date("Y-m-d H:i:s",time());

    if(isset($_POST["pay"])){
        foreach($_POST["pay"] as $id=>$post_ref){
            if($post_ref != '0'){
                $post_ref = str_replace(" ","",$post_ref);
                $pay_ref_no = substr($post_ref,0,stripos($post_ref,"-"));
                $pay_id = substr($post_ref,stripos($post_ref,"-")+1);
                // if(strlen($post_ref) == 24){
                if($pay_ref_no)
                    updateInDB($tb,"pay_ref_no",check_value("text",$pay_ref_no),"id",$id);
                if($pay_id)    
                    updateInDB($tb,"pay_id",check_value("text",$pay_id),"id",$id);
                    updateInDB($tb,"payment_date",$now,"id",$id);
                    updateInDB($tb,"state",1,"id",$id);
                    // if(getVarFromDB($tb,"ssmss","id",$id) != 1){
                        
                        // $uid = getVarFromDB($tb,"uid","id",$id);
                        // $tel = getVarFromDB("users","tel","id",$uid);
                        // $name = getVarFromDB("users","name","id",$uid);
                        
                        // if(send_sms_temp($tel,'sendOstan',$name,$id,$post_ref)[0]){
                            // updateInDB($tb,"ssmss",1,"id",$id);
                        // }
                    // }
                // }
            }
        }
    }
?>

<?php
        }
    }
?>