<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

function db_new_comment($user_data,$comment){
    include $GLOBALS['bu'].$GLOBALS['dbc_adrs'];
    $table = "comments";
    $st = "INSERT INTO $table (name,tel,comment) VALUES (?,?,?);";
    $st = $mysqli->prepare($st);
    $st->bind_param('sss',$user_data["name"],$user_data["tel"],$comment);

    if(!$st->execute()){
        return false;
    }
    return true;
}


if(isset($_POST["submit"])){

    if(isset($_SESSION["logged"])){
        if(isset($_POST["comment"]) && check_value('text',$_POST["comment"])){
            $user_data = $_SESSION["user"]->data();
            $state = db_new_comment($user_data,check_value('text',$_POST["comment"]));
        }
    }
    else{    
        if(isset($_POST["name"]) && check_value('text',$_POST["name"]))$user_data["name"]=check_value('text',$_POST["name"]);
        if(isset($_POST["tel"]) && check_value('tel',$_POST["tel"]))$user_data["tel"]=check_value('tel',$_POST["tel"]);
        
        if(isset($_POST["comment"]) && isset($user_data["name"]) && isset($user_data["tel"]) ){
            $state = db_new_comment($user_data,check_value('text',$_POST["comment"]));
        }
    }
}


?>

<?php
        }
    }
?>