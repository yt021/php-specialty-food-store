<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
if($_SESSION["a_logged"]->get_level() >= 1){
    if($_SESSION[$cf]->level == 2){
        if(isset($_POST["county"],$_POST["city"],$_POST["address"])){
            $address = $_POST;
            $address["janitor"] = "";
            $address2 = new address($address,$_SESSION[$cf]->sub_id);
            if($address2->data()["error"][0]==0){
                if($_SESSION[$cf]->sub_id == "new"){
                    db_new_address($_SESSION[$cf]->id,$address);
                    $_SESSION[$cf]->level = 1;
                }else{
                    $fields = ["county","city","address","post_code","rec_name","rec_tel","rec_tel_2"];
                    foreach($fields as $f){
                        updateInDB("addresses",$f,$address2->data()[$f],"id",$address2->data()["aid"]);
                    }
                }
            
            }
        }
    }
    if($_SESSION[$cf]->level == 1){
        if(isset($_POST["name"]))updateInDB($tb,"name",$_POST["name"],"id",$_SESSION[$cf]->id);
        if(isset($_POST["tel"]))updateInDB($tb,"tel",$_POST["tel"],"id",$_SESSION[$cf]->id);
        if(isset($_POST["email"]))updateInDB($tb,"email",$_POST["email"],"id",$_SESSION[$cf]->id);
        if(isset($_POST["social"]))updateInDB($tb,"social",$_POST["social"],"id",$_SESSION[$cf]->id);

        if(isset($_POST["password"]) && $_POST["password"])updateInDB($tb,"password",$_POST["password"],"id",$_SESSION[$cf]->id);
        
    }
}
?>
<?php
        }
    }
?>
