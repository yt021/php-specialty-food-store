<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <div class="ssrv">
                <div class="ssrv_title">پست اینستاگرام</div>        
                <div class="ssrv_dtl">
                    <form action="<?php echo $URL ?>" method="post">
                        <div class="form fr">
<?php
    $id = $_SESSION[$cf]->id;
    if($id == "new"){
        $row["data"]="";
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
                            <label>کد جاسازی (Embed):</label>
                            <textarea name="data">'.$row["data"].'</textarea>
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