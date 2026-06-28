<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<main>
    <div class="content">
        <h1 class="tac">مطالب میوه خشک آبان</h1>
        <div class="articles">
<?php
    $st = "SELECT name FROM categories WHERE del_flag = 0 AND section = 'articles'";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res_ = $st->get_result();
    while($row_ = $res_->fetch_assoc())
    {    
        $category = $row_["name"];
?>
            <div class="articles_column">
                <div class="a_c_title">
                    <h2><?php echo $category; ?></h2>
                </div>
<?php
    $tb = "posts";
    $order_str = "";
    $section = 'articles'; 
    $st = "SELECT name,title,description,last_update,first_img_id FROM $tb WHERE del_flag = 0 AND section = '$section' AND category = '$category' $order_str";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    
    $name = $row["name"];
    $title = $row["title"];
    $description = $row["description"];
    $last_update = correctDate($row["last_update"]);
    $first_img_id = $row["first_img_id"];
    if($first_img_id)$first_img_fn = getVarFromDB("content","file_name","id",$first_img_id);else $first_img_fn = false;
?>
                <div class="a_c_article_big">
                    <a href="<?php echo $s."posts/$name.php"; ?>"><img src="<?php if($first_img_fn)echo $s."content/$first_img_fn"; ?>" /></a>
                    <a href="<?php echo $s."posts/$name.php"; ?>"><h3><?php echo $title; ?></h3></a>
                    <div class="a_c_article_data"><?php echo $last_update; ?></div>
                    <p><?php echo $description; ?></p>
                </div>
<?php
    while($row = $res->fetch_assoc())
    {
        $name = $row["name"];
        $title = $row["title"];
        $description = $row["description"];
        $last_update = correctDate($row["last_update"]);
        $first_img_id = $row["first_img_id"];
        if($first_img_id)$first_img_fn = getVarFromDB("content","file_name","id",$first_img_id);else $first_img_fn = false;
?>
                <div class="a_c_article">
                    <a href="<?php echo $s."posts/$name.php"; ?>">
                        <img src="<?php if($first_img_fn)echo $s."content/$first_img_fn"; ?>" />
                    </a>
                    <div class="a_c_article_data">
                        <a href="<?php echo $s."posts/$name.php"; ?>">
                            <h3><?php echo $title; ?></h3>
                        </a>
                        <?php echo $last_update; ?>
                    </div>
                </div>
<?php
    }
?>
            </div>
<?php
    }
?>
        </div>
        
    </div>  
</main>
<?php
        }
    }
?>