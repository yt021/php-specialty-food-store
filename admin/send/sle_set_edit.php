<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    
    $tb = $_SESSION[$cf]->state;
    if($_SESSION[$cf]->level == 2){
        $fields = ["transporter_id","static_rows"];
        if($_SESSION[$cf]->id == "new"){
            if(isset($_FILES["file"])){
                $dir = $bu."admin/send/excel_files/";
                $size_limit = 3;
                $file = $_FILES["file"];
                $ff = pathinfo(basename($file["name"]),PATHINFO_EXTENSION);
                if($ff == "xlsx" && checkFileType($ff) && $file["size"] < $size_limit*1024*1024){
                    $error = false;
                    foreach($fields as $field){
                        if(isset($_POST[$field]) && check_value("text",$_POST[$field])){}else{$error = true;}
                    }
                    if(!$error){
                        $file_name = checkFileType($ff).(time()*4).".$ff";
                        $target_file = $dir.$file_name;  
                        if(move_uploaded_file($file["tmp_name"],$target_file)){
                            $st = "INSERT INTO $tb (transporter_id,file_name,static_rows) VALUES (?,?,?)";
                            $st = $mysqli->prepare($st);
                            $st->bind_param('sss',$_POST["transporter_id"],$file_name,$_POST["static_rows"]);
                            $st->execute();
                            
                            $sle_id = last_id($tb);
                            
                            // Read Excel File: $target_file
                            
                            $number_of_static_rows = (int)$_POST["static_rows"];
                            include_once $bu."modules/SimpleXLSX.php";
                            
                            if($xlsx = SimpleXLSX::parse($target_file)){
                                $number_of_columns = 0;
                                $k = 0;
                                foreach($xlsx->rows() as $row){
                                    if($k<$number_of_static_rows){
                                        $number_of_columns = max([$number_of_columns,sizeof($row)]);
                                        $row_to_str[$k] = implode(",",$row);
                                    }
                                    $k++;
                                }
                            }
                            unset($xlsx);
                            
                            if(isset($row_to_str,$number_of_columns)){
                                updateInDB($tb,"n_columns",$number_of_columns,"id",$sle_id);
                                foreach($row_to_str as $k=>$str){
                                    $st = "INSERT INTO send_list_excels_static_rows (sleid,row_n,row_to_str) VALUES (?,?,?)";
                                    $st = $mysqli->prepare($st);
                                    $st->bind_param('sss',$sle_id,$k,$str);
                                    $st->execute();
                                }
                            }
                            $_SESSION[$cf]->id = $sle_id;
                        }
                    }
                }
            }
        }else{
            foreach ($fields as $f){
                updateInDB($tb,$f,$_POST[$f],"id",$_SESSION[$cf]->id);
            }
        }
    }
    if($_SESSION[$cf]->level == 3){
        if(isset($_POST["type_select"],$_POST["value_select"],$_POST["value_input"])){
            $type_select = $_POST["type_select"];
            $value_select = $_POST["value_select"];
            $value_input = $_POST["value_input"];
            $detail_table_value = "";
            if(isset($_POST["detail_text"],$_POST["detail_value"])){
//                echo "here";
                $detail_text = $_POST["detail_text"];
                $dt_var_no = substr_count($detail_text,"«متغیر»");
                
                $detail_value = $_POST["detail_value"];
                $dv_str = implode(",",$detail_value).",";
                while(stripos($dv_str,",,")!==False){
                    $dv_str = str_replace(",,",",",$dv_str);
                }
                $dv_str = substr($dv_str,0,strlen($dv_str)-1);
                $dv_len = sizeof(explode(",",$dv_str));
                
                if($dt_var_no > $dv_len){
                    $error_message = "متغیرهای کافی انتخاب نشده‌اند.";
                }
                if($dt_var_no < $dv_len){
                    $dv_str_ar = explode(",",$dv_str);
                    foreach($dv_str_ar as $i=>$v){
                        if($i>=$dt_var_no)$dv_str_ar[$i]="";
                    }
                    $dv_str = implode(",",$dv_str_ar).",";
                    while(stripos($dv_str,",,")!==False){
                        $dv_str = str_replace(",,",",",$dv_str);
                    }
                    $dv_str = substr($dv_str,0,strlen($dv_str)-1);
                    $dv_len = sizeof(explode(",",$dv_str));
                    
                }
                if($dt_var_no == $dv_len){
                    $detail_table_value = $detail_text."|||".$dv_str;
                }
            }
            $k = 0;
            foreach($type_select as $column=>$type){
                switch($type){
                    case 'empty':
                        $column_value[$column] = "";
                        break;
                    case 'constant':
                        $column_value[$column] = $value_input[$column];
                        break;
                    case 'variable':
                        $column_value[$column] = $value_select[$column];
                        break;
                    case 'detail':
                        $column_value[$column] = $detail_table_value;
                        break;
                }
            }
            $id = $_SESSION[$cf]->id;
            foreach($column_value as $column=>$value){
                $tb2 = "send_list_excels_column_values";
                $st = "SELECT id FROM $tb2 WHERE sleid = $id AND column_name = $column LIMIT 1";
                $st = $mysqli->prepare($st);
                if(!$st->execute()){
                    echo "E";
                    exit;
                }
                $st->store_result();
                $st->bind_result($col_id);
                $st->fetch();
                
                if($col_id){
                    updateInDB($tb2,"type",$type_select[$column],"id",$col_id);
                    updateInDB($tb2,"value",$column_value[$column],"id",$col_id);
                }else{
                    $st = "INSERT INTO $tb2 (sleid,column_name,type,value) VALUES (?,?,?,?)";
                    $st = $mysqli->prepare($st);
                    $st->bind_param('ssss',$id,$column,$type_select[$column],$column_value[$column]);
                    if(!$st->execute()){
                        echo "E";
                        exit;
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
