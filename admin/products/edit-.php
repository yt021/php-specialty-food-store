<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
$adrs = $bu."products/";
    if($_SESSION[$cf]->level == 1){
        if($_SESSION[$cf]->id == "new"){
            $fields = ["name","file_name","weight","price","profit"];
            $error = 0;
            foreach($fields as $field){
                if(isset($_POST[$field]) && check_value('text',$_POST[$field]))$row[$field] = check_value('text',$_POST[$field]);
                else{$error++;}
            }
            if($error === 0){
                $st = "INSERT INTO $tb (name,file_name) VALUES (?,?)";
                $st = $mysqli->prepare($st);
                $st->bind_param('ss',$row["name"],$row["file_name"]);
                $st->execute();
                
                $id = last_id($tb);
                
                $st = "INSERT INTO products_price (pid,weight,price,profit) VALUES (?,?,?,?)";
                $st = $mysqli->prepare($st);
                $st->bind_param('ssss',$id,$row["weight"],$row["price"],$row["profit"]);
                $st->execute();
                
                $_SESSION[$cf]->id = $id;
                
                copy($adrs."index.php",$adrs.$row["file_name"].".php"); 
            }
        }else{
        $fields = ["name","category"];
        foreach($fields as $field){
            if(isset($_POST[$field]) && check_value("text",$_POST[$field])){
                updateInDB($tb,$field,$_POST[$field],"id",$_SESSION[$cf]->id);
            }
        }
        $fields = ["weight","price","profit"];
        $error = 0;
        foreach($fields as $field){
            if(isset($_POST[$field]) && check_value('text',$_POST[$field]))$row[$field] = check_value('text',$_POST[$field]);
            else{$error++;}
        }
        if($error === 0){
            $id = $_SESSION[$cf]->id;
            $st = "INSERT INTO products_price (pid,weight,price,profit) VALUES (?,?,?,?)";
            $st = $mysqli->prepare($st);
            $st->bind_param('ssss',$id,$row["weight"],$row["price"],$row["profit"]);
            $st->execute();
        }
        if(isset($_POST["show_order"])){
            updateInDB($tb,"show_order",$_POST["show_order"],"id",$_SESSION[$cf]->id);
        }
        if(isset($_POST["sale_state"])){
            updateInDB($tb,"sale_state",1,"id",$_SESSION[$cf]->id);
        }else{
            updateInDB($tb,"sale_state",0,"id",$_SESSION[$cf]->id);
        }
        }
    }
?>
<?php
        }
    }
?>
