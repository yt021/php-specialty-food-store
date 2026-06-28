<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if($_SESSION[$cf]->level == 2){
        if($_SESSION[$cf]->id == "new"){
            if(isset($_POST["name"]) && check_value('text',$_POST["name"]) && isset($_POST["title"]) && check_value('text',$_POST["title"]) && isset($_POST["category"]) && check_value('text',$_POST["category"]) &&  isset($_POST["keywords"]) && check_value('text',$_POST["keywords"]) &&  isset($_POST["description"]) && check_value('text',$_POST["description"])){
                $name = check_value('text',$_POST["name"]);
                $title = check_value('text',$_POST["title"]);
                $section = $_SESSION[$cf]->state;
                $category = check_value('text',$_POST["category"]);
                $keywords = check_value('text',$_POST["keywords"]);
                $description = check_value('text',$_POST["description"]);
                $st = "INSERT INTO $tb (name,title,section,category,keywords,description) VALUES (?,?,?,?,?,?)";
                $st = $mysqli->prepare($st);
                $st->bind_param('ssssss',$name,$title,$section,$category,$keywords,$description);
                $st->execute();
                
                $_SESSION[$cf]->id = last_id($tb);
                $adrs = $bu."posts/";
                copy($adrs."index.php",$adrs.$name.".php"); 

            }
        }else{
        if(isset($_POST["title"]))updateInDB($tb,"title",$_POST["title"],"id",$_SESSION[$cf]->id);
        if(isset($_POST["keywords"]))updateInDB($tb,"keywords",$_POST["keywords"],"id",$_SESSION[$cf]->id);
        if(isset($_POST["description"]))updateInDB($tb,"description",$_POST["description"],"id",$_SESSION[$cf]->id);
        if(isset($_POST["category"]))updateInDB($tb,"category",$_POST["category"],"id",$_SESSION[$cf]->id);
        }
    }else if($_SESSION[$cf]->level == 3){
        if(isset($_POST["content_text"]))updateInDB($tb,"content",$_POST["content_text"],"id",$_SESSION[$cf]->id);
        if($_SESSION[$cf]->sub_id == "single")updateInDB($tb,"first_img_id",$_POST["edit"],"id",$_SESSION[$cf]->id);
        $_SESSION[$cf]->level = 2;
    }
?>
<?php
        }
    }
?>
