<?php
    $user_level = $_SESSION["a_logged"]->get_level();
//    echo $user_level;
    $st = "SELECT * FROM $atb";
    $st = $mysqli->prepare($st);
//                    $st->bind_param('s',$uid);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    ?>
    <?php
    while($row = $res->fetch_assoc()){ ?>
        <a onclick="sub_show('state','<?php echo $row["flag"]; ?>')" class="btn half fr admin_dashboard" ><?php echo $row["name"]; ?></a>
    <?php }
    if($user_level >= 2){
    ?>
    <a onclick="show_admin_service('categories')" class="btn half fr admin_dashboard">
        مدیریت دسته‌بندی
    </a>
<?php
    }
?>