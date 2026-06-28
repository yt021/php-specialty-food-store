<?php
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include_once $bu."modules/wdb/db_connection.php";
    include_once $bu."modules/wdb/db_funcs.php";

    $s = "";
    
    $table = "comments";
    $st = "SELECT name,comment,create_date,reply,reply_date FROM comments WHERE show_flag = 1 ORDER BY id DESC";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $k = 1;
    while($row = $res->fetch_assoc()){
        if($k>5){
        $name = $row["name"];
        $cm = $row["comment"];
        $date = correctDate($row["create_date"]);
        $reply = $row["reply"];
        $reply_date = correctDate($row["reply_date"]);
?>
    <li>
        <b><?php echo $name; ?></b>
        <?php echo $cm; ?>
        <span class="person"><?php echo $date; ?></span>
        <?php
            if($reply){
                ?>
                <div class="cb"></div>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <picture class="admin_logo">
                    <source type="image/webp" srcset="<?php echo $s;?>img/webp/logo-mob.webp 500w,<?php echo $s;?>img/webp/logo.webp">
                    <img class="admin_logo" src="<?php echo $s;?>img/logo-mob.png"  srcset="<?php echo $s;?>img/logo-mob.png 500w,<?php echo $s;?>img/logo.png"/>
                </picture>
                <?php echo $reply;?>
                <?php 
            }
        ?>
    </li>
<?php
        }
        $k++;
    }
?>

