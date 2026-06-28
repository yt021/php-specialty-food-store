<?php
    if(isset($indexed)){
        if($indexed == 1){?>
            <div class="ssrv">
                <div class="ssrv_title">مشخصات</div>        
                <div class="ssrv_dtl">
                    <form action="<?php echo $URL; ?>" method="post">
                        <div class="form fr">
<?php
    if($_SESSION[$cf]->id == "new"){$name = "";$section="";$show_order="";}else{
    $name = getVarFromDB($tb,"name","id",$_SESSION[$cf]->id);
    $section = getVarFromDB($tb,"section","id",$_SESSION[$cf]->id);
    $show_order = getVarFromDB($tb,"show_order","id",$_SESSION[$cf]->id);
    }
    echo '              
                        <div class="form_item">
                            <label>نام:</label>
                            <input name="name" type="text" value="'.$name.'">
                        </div>
                        <div class="form_item">
                            <label>گروه مطالب:</label>
                            <select name="section">';
                            $st = "SELECT name,flag FROM admin_categories_groups ";
                            $st = $mysqli->prepare($st);
                            if(!$st->execute()){
                                echo "E";
                                exit;
                            }
                            $res = $st->get_result();
                            while($row = $res->fetch_assoc())
                            {
                                if($section == $row["flag"]){
                                    $selected = " selected ";
                                }else{$selected = "";}
                                echo '
                                    <option value="'.$row["flag"].'" '.$selected.'>'.$row["name"].'</option>';
                            }    
echo                        '</select>
                        </div>
                        <div class="form_item">
                            <label>ترتیب نمایش:</label>
                            <input name="show_order" type="text" value="'.$show_order.'">
                        </div>              
                        
                    ';
?>
                        </div>
                </div>
            </div>
            <input type="submit" name="edit" class="btn" value="ثبت">
</form>     

<?php
        }
    }
?>