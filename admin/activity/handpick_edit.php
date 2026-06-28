<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
if(isset($_POST["edit"])){
    $state = $_SESSION[$cf]->state;
    $fields = ["eid","day","month","year","pid","sid","t_weight","w_weight"];
    $error = true;
    foreach($fields as $f){
        if(isset($_POST[$f]) && check_value("text",$_POST[$f])){}
        else{$error = false;}
    }
    if($error){
        $date = cSQLTimefS($_POST);
        $eid = $_POST["eid"];
        $pid = $_POST["pid"];
        $sid = $_POST["sid"];
        $t_weight = $_POST["t_weight"];
        $w_weight = $_POST["w_weight"];
        if($_SESSION[$cf]->id == "new"){
            $st = "INSERT INTO $tb (eid,asf,v1,v2,v3,v4,v5) VALUES (?,?,?,?,?,?,?)";
            $st = $mysqli->prepare($st);
            $st->bind_param('sssssss',$eid,$state,$pid,$t_weight,$date,$w_weight,$sid);
            $st->execute();
            
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = null;
        }else{
            updateInDB($tb,"eid",$eid,"id",$_SESSION[$cf]->id);
            updateInDB($tb,"v1",$pid,"id",$_SESSION[$cf]->id);
            updateInDB($tb,"v2",$t_weight,"id",$_SESSION[$cf]->id);
            updateInDB($tb,"v3",$date,"id",$_SESSION[$cf]->id);
            updateInDB($tb,"v4",$w_weight,"id",$_SESSION[$cf]->id);
            updateInDB($tb,"v5",$sid,"id",$_SESSION[$cf]->id);
        }
    }
}
?>

<?php
        }
    }
?>