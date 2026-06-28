<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

$tables = explode(",",getVarFromDB("backup_tables","list","flag",$flag));
if(!is_array($tables)){$error = "خطا - لیست جداول خوانده نمی‌شود.";}
else{
    
    foreach($tables as $table){
        $columns = table_columns_comma($table,$db);
        
        $st3 = "SHOW CREATE TABLE $table";
        $st3 = $mysqli->prepare($st3);
        if(!$st3->execute()){
            $error = "خطا - جدول ساخته نمی‌شود.";
        }else{
            $limit = "";
            if($flag == "data"){
                switch($table){
                    case "sub_orders":
                        $limit = 1500;
                        break;
                    default:
                        $limit = 300;
                }
                $limit = " ORDER BY id DESC  LIMIT $limit";
            }
            $res3 = $st3->get_result();
            $res3 = $res3->fetch_assoc();
            $create_table_statement = $res3["Create Table"];
            $text1 = "\n\n-- Create Table: $table: \n\n";
            $text2 = "".$create_table_statement."\n\n";
        
            $data .= $text1.$text2;

            $st2 = "SELECT * FROM $table $limit";
            $st2 = $mysqli->prepare($st2);
            if(!$st2->execute()){
                $error = "خطا - داده در دسترس نیست.";
            }else{
                $res2 = $st2->get_result();
                if($res2->num_rows >= 1){
                    $insert_statement = "INSERT INTO $table $columns VALUES ";
                    $values = "\n";
                    
                    while($row2 = $res2->fetch_assoc()){
                        for($row_i=0;$row_i<sizeof($row2);$row_i++){
                            $row2[$row_i] = "'".$row2[$row_i]."'";
                        }
                        $values .= "(".implode(",",$row2)."),";
                    }
                    $values = substr($values,0,strlen($values)-1).";";
                    $insert_statement .= $values;
                    
                    $text3 = "\n\n -- Insert Statements: $table \n\n";
                    $text4 = $insert_statement;
                    
                    $data .= $text3.$text4;
                }
            }
        }
    }
}
?>
<?php
        }
    }
?>