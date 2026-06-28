<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <div class="ssrv">
                <div class="ssrv_title">اطلاعات تماس</div>        
                <div class="ssrv_dtl">
                    <form action="<?php echo $URL ?>" method="post">
                        <div class="form fr">
<?php
    $id = $_SESSION[$cf]->id;
    if($id == "new"){
        $row["name"]="";
        $row["value"]="";
        $row["link"]="";
    }else{
    $st = "SELECT * FROM $tb WHERE id = $id";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    }
    
    echo '              

                        <div class="form_item">
                            <label>عنوان:</label>
                            <input name="name" type="text" value="'.$row["name"].'">
                        </div>
                        <div class="form_item">
                            <label>مقدار:</label>
                            <input name="value" type="text" value="'.$row["value"].'">
                        </div>
                        <div class="form_item">
                            <label>لینک:</label>
                            <input name="link" type="text" value="'.$row["link"].'">
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