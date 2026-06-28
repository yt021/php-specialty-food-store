<?php

    if(isset($indexed)){

        if($indexed == 1){?>

<?php

    $now = date("Y-m-d H:i:s",time());

    file_put_contents(__DIR__.'/debug.log', "post_ref.php: processing post_ref\n", FILE_APPEND);

    if(isset($_POST["courier"])){
        file_put_contents(__DIR__.'/debug.log', "post_ref.php: handling courier[]\n", FILE_APPEND);
        foreach($_POST["courier"] as $id=>$post_ref){

            if($post_ref){

                updateInDB($tb,"post_ref_id",2,"id",$id);

                updateInDB($tb,"send_date",$now,"id",$id);

                updateInDB($tb,"state",4,"id",$id);

                // Notify Pinket if this is a Pinket order
                require_once __DIR__ . '/../pinket/helpers.php';
                require_once __DIR__ . '/../pinket/status-webhook.php';
                $order = getRow($mysqli, "orders", "id = ?", [$id], "i");
                if ($order && !empty($order['unified_id']) && !ctype_digit($order['unified_id'])) {
                    file_put_contents(__DIR__.'/pinket_debug.log', "post_ref Pinket for OID: $id\n", FILE_APPEND);
                    updateOrderStatusInPinket($id);
                }

            }

        }

    }

    if(isset($_POST["other"])){
        file_put_contents(__DIR__.'/debug.log', "post_ref.php: handling other[]\n", FILE_APPEND);
        foreach($_POST["other"] as $id=>$post_ref){

            if($post_ref){

                updateInDB($tb,"post_ref_id",3,"id",$id);

                updateInDB($tb,"send_date",$now,"id",$id);

                updateInDB($tb,"state",4,"id",$id);

                // Notify Pinket if this is a Pinket order
                require_once __DIR__ . '/../pinket/helpers.php';
                require_once __DIR__ . '/../pinket/status-webhook.php';
                $order = getRow($mysqli, "orders", "id = ?", [$id], "i");
                if ($order && !empty($order['unified_id']) && !ctype_digit($order['unified_id'])) {
                    file_put_contents(__DIR__.'/pinket_debug.log', "post_ref Pinket for OID: $id\n", FILE_APPEND);
                    updateOrderStatusInPinket($id);
                }

            }

        }

    }

    if(isset($_POST["tehran"])){
        file_put_contents(__DIR__.'/debug.log', "post_ref.php: handling tehran[]\n", FILE_APPEND);
        $tp_id = getVarFromDB("sd_setting","value","flag","tp_id");

        foreach($_POST["tehran"] as $id=>$post_ref){

            if($post_ref){

                updateInDB($tb,"post_ref_id",$tp_id,"id",$id);

                updateInDB($tb,"send_date",$now,"id",$id);

                updateInDB($tb,"state",4,"id",$id);

                // Notify Pinket if this is a Pinket order
                require_once __DIR__ . '/../pinket/helpers.php';
                require_once __DIR__ . '/../pinket/status-webhook.php';
                $order = getRow($mysqli, "orders", "id = ?", [$id], "i");
                if ($order && !empty($order['unified_id']) && !ctype_digit($order['unified_id'])) {
                    file_put_contents(__DIR__.'/pinket_debug.log', "post_ref Pinket for OID: $id\n", FILE_APPEND);
                    updateOrderStatusInPinket($id);
                }

            }

        }

    }



    if(isset($_POST["post"])){
        file_put_contents(__DIR__.'/debug.log', "post_ref.php: handling post[]\n", FILE_APPEND);
        foreach($_POST["post"] as $id=>$post_ref){

            $post_ref = str_replace(" ","",$post_ref);
            file_put_contents(__DIR__.'/pinket_debug.log', "Checking post_ref for OID: $id, value: $post_ref\n", FILE_APPEND);
            if(strlen($post_ref) == 24){
                file_put_contents(__DIR__.'/pinket_debug.log', "post_ref is 24 chars for OID: $id\n", FILE_APPEND);
                updateInDB($tb,"post_ref_id",check_value("text",$post_ref),"id",$id);
                updateInDB($tb,"send_date",$now,"id",$id);
                updateInDB($tb,"state",4,"id",$id);
                // Notify Pinket if this is a Pinket order
                require_once __DIR__ . '/../pinket/helpers.php';
                require_once __DIR__ . '/../pinket/status-webhook.php';
                $order = getRow($mysqli, "orders", "id = ?", [$id], "i");
                if ($order && !empty($order['unified_id']) && !ctype_digit($order['unified_id'])) {
                    file_put_contents(__DIR__.'/pinket_debug.log', "post_ref Pinket for OID: $id\n", FILE_APPEND);
                    updateOrderStatusInPinket($id);
                }
                if(getVarFromDB($tb,"ssmss","id",$id) != 1){
                    $uid = getVarFromDB($tb,"uid","id",$id);
                    $tel = getVarFromDB("users","tel","id",$uid);
                    $name = getVarFromDB("users","name","id",$uid);
                    if(send_sms_temp($tel,'sendOstan',$name,$id,$post_ref)[0]){
                        updateInDB($tb,"ssmss",1,"id",$id);
                    }
                }
            }

        }

    }

?>



<?php

        }

    }

?>