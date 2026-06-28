<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
if(isset($_POST["edit"])){
    $tb = $_SESSION[$cf]->state;
    $fields = ["name","transporter_id","price"];
    $error = true;
    foreach($fields as $f){
        if(isset($_POST[$f]) && check_value("text",$_POST[$f])){}
        else{$error = false;}
    }
    
    if($error){
        $name = $_POST["name"];
        $start_hour = $_POST["start_hour"];
        $end_hour = $_POST["end_hour"];
        $tp_id = $_POST["transporter_id"];
        $price = $_POST["price"];
        
        if($_SESSION[$cf]->id == "new"){
            $st = "INSERT INTO $tb (name,start_hour,end_hour,transporter_id,price) VALUES (?,?,?,?,?)";
            $st = $mysqli->prepare($st);
            $st->bind_param('sssss',$name,$start_hour,$end_hour,$tp_id,$price);
            $st->execute();

            $_SESSION[$cf]->level = 1;
            $_SESSION[$cf]->id = null;
        }else{
            $fields = ["name","start_hour","end_hour","transporter_id","price"];
            foreach ($fields as $f){
                updateInDB($tb,$f,$_POST[$f],"id",$_SESSION[$cf]->id);
            }
        }
    }
}
?>

<?php
        }
    }
?>