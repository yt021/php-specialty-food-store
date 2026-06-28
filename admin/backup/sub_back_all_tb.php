<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

$db = "abanfrui_shop";
$st = "SHOW TABLES FROM $db";
$st = $mysqli->prepare($st);
if(!$st->execute()){$error = "خطا - لیست جداول خوانده نمی‌شود.";}
else{
    $res = $st->get_result();
    while($row = $res->fetch_array()){
        $table = $row["Tables_in_$db"];
        $columns = table_columns_comma($table,$db);
        
        $st3 = "SHOW CREATE TABLE $table";
        $st3 = $mysqli->prepare($st3);
        if(!$st3->execute()){
            $error = "خطا - جدول $table ساخته نمی‌شود.";
        }else{
            $res3 = $st3->get_result();
            $res3 = $res3->fetch_assoc();
            $create_table_statement = $res3["Create Table"];
            $text1 = "\n\n-- Create Table: $table: \n\n";
            $text2 = "".$create_table_statement."\n\n";
        
            $data .= $text1.$text2;
        }
    }
}
?>
<?php
        }
    }
?>