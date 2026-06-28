<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $tb = $cf."_".$_SESSION[$cf]->state;
    $st = "SELECT name,flag FROM $tb";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $file_name = "do_".$_SESSION[$cf]->state."_backup.php";
?>
<?php
    while($row = $res->fetch_assoc()){ 
?>
        <a href="<?php echo $file_name; ?>?way=download&flag=<?php echo $row["flag"]; ?>" class="btn half fr admin_dashboard" >
            <?php echo $row["name"]; ?>
        </a>
<?php
    }
?>
    </div>  
</div>  
<?php
        }
    }
?>