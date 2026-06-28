<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $tb = $_SESSION[$cf]->state;
    
    if($_SESSION[$cf]->level == 1){
        $st = "SELECT flag FROM $tb";
        $st = $mysqli->prepare($st);
        if(!$st->execute()){
            echo "E";
            exit;
        }
        $res = $st->get_result();
        while($row = $res->fetch_assoc())
        {
            $f = $row["flag"];
            if(isset($_POST[$f]))updateInDB($tb,"value",$_POST[$f],"flag",$f);   
        }
    }
?>
<?php
        }
    }
?>
