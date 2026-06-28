<?php
    $st = "SELECT * FROM $atb";
    $st = $mysqli->prepare($st);
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
    ?>