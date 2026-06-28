<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
if(isset($_POST["edit"])){
    $fields = ["insta_id","ad_current_followers","cost_direct","cost_oid","ad_view","follower_increase"];
    
    if($_SESSION[$cf]->id == "new"){
        if(isset($_POST["name"]) && check_value("text",$_POST["name"])){
            
            $name = check_value("text",$_POST["name"]);
            $st = "INSERT INTO $tb (name) VALUES (?)";
            $st = $mysqli->prepare($st);
            $st->bind_param('s',$name);
            $st->execute();
            
            $id = last_id($tb);
            
            foreach($fields as $f){
                updateInDB($tb,$f,$_POST[$f],"id",$id);
            }
            updateInDB($tb,"ad_date",cSQLTimefS($_POST),"id",$id);
            $_SESSION[$cf]->id = $id;
        }
    }else{
        array_push($fields,"name");
        foreach($fields as $f){
            if(isset($_POST[$f])){
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