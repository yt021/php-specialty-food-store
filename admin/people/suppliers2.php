<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

    $id = $_SESSION[$cf]->id;
    if($id == "new"){
        $row = array(
            "name"=>NULL,
            "detail"=>NULL,
            
        );
    }else{
    $st = "SELECT name,detail FROM $tb WHERE id = $id";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    }
?>
    <form action="<?php echo $URL ?>" method="post">
        <div class="ssrv">
            <div class="ssrv_title">مشخصات</div>        
            <div class="ssrv_dtl">
                <div class="form fr">
                    <div class="form_item ">
                        <label>نام: </label>
                        <input name="name" type="text" value="<?php echo $row["name"]; ?>">
                    </div>
                    <div class="form_item ">
                        <label>توضیحات: </label>
                        <textarea name="detail" type="text" ><?php echo $row["detail"]; ?></textarea>
                    </div>
                </div>
            </div>
        </div>        
        <input type="submit" name="edit" class="btn" value="ثبت">
    </form>

<?php
        }
    }
?>