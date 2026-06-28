<?php
    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/wdb/db_funcs.php";
    $id = last_id("instagram");
    echo "<div style='max-width:330px;'>";
    echo getVarFromDB("instagram","data","id",$id);
?>
</div>
