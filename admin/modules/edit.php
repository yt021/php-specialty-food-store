<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if($_SESSION[$cf]->level == 1){
        $fields = ["name_fa","title","keywords","description"];
        foreach($fields as $field){
            if(isset($_POST[$field]) && check_value("text",$_POST[$field])){
                updateInDB($tb,$field,check_value("text",$_POST[$field]),"id",$_SESSION[$cf]->id);
            }
        }
    }
?>
<?php
        }
    }
?>
