<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $tb = $_SESSION[$cf]->state;
    if($_SESSION[$cf]->level == 2){
        if($_SESSION[$cf]->id == "new"){
        }else{
            $fields = ["county","cities_str","cost"];$error = 0;

            foreach($fields as $f){
                if(isset($_POST[$f]))$data[$f]=$_POST[$f];
                if(isset($data[$f]))updateInDB($tb,$f,$data[$f],"id",$_SESSION[$cf]->id);
            }
        }
    }
?>
<?php
        }
    }
?>
