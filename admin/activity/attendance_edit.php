<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
if(isset($_POST["edit"])){
    $state = $_SESSION[$cf]->state;
    $fields = ["eid","day","month","year","traffic"];
    $error = true;
    foreach($fields as $f){
        if(isset($_POST[$f]) && check_value("text",$_POST[$f])){}
        else{$error = false;}
    }
    foreach(["hour","minute"] as $f){
        if(isset($_POST[$f])){}
        else{$error = false;}
    }
    if($error){
        $time = $_POST["hour"]*60+$_POST["minute"];
        $traffic = $_POST["traffic"];
        $date = cSQLTimefS($_POST);
        $eid = $_POST["eid"];
        if($_SESSION[$cf]->id == "new" || isset($_POST["new_act"])){
            $st = "INSERT INTO $tb (eid,asf,v1,v2,v3) VALUES (?,?,?,?,?)";
            $st = $mysqli->prepare($st);
            $st->bind_param('sssss',$eid,$state,$time,$traffic,$date);
            $st->execute();
            
            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = null;
        }else{
                updateInDB($tb,"eid",$eid,"id",$_SESSION[$cf]->id);
                updateInDB($tb,"v1",$time,"id",$_SESSION[$cf]->id);
                updateInDB($tb,"v2",$traffic,"id",$_SESSION[$cf]->id);
                updateInDB($tb,"v3",$date,"id",$_SESSION[$cf]->id);
        }
    }
}
?>

<?php
        }
    }
?>