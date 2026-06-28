<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        <div class="ssrv">
                <div class="ssrv_title">مشخصات نظر دهنده</div>        
                <div class="ssrv_dtl">
                    <form>
                        <div class="form fr">
<?php
    $id = $_SESSION[$cf]->id;
//    include_once $bu."modules/cart/cart_funcs.php";
//    $uid = getVarFromDB($tb,"uid","id",$oid);
//    $aid = getVarFromDB($tb,"aid","id",$oid);
//    $user = db_get_user($uid,$aid);
    $st = "SELECT * FROM $tb WHERE id = $id";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    
    ?>  
    
    <?php

    echo '              
                        <div class="form_item">
                            <label>نام:</label>
                            <input disabled type="text" value="'.$row["name"].'">
                        </div>
                        <div class="form_item">
                            <label>تلفن همراه:</label>
                            <input disabled type="text" value="'.$row["tel"].'">
                        </div>
                        <div class="form_item">
                            <label>رایانامه:</label>
                            <input disabled type="text" value="'.$row["email"].'">
                        </div>
                    ';
?>
 <!--                       <a class="btn" onclick="sub_show('uid','<?php echo $_SESSION["$cf"]->sub_id;?>','clients/')">مشاهده حساب</a> -->
                        </div>
                    </form>
                </div>
            </div>
            <div class="ssrv">
                <div class="ssrv_title">نظر</div>        
                <div class="ssrv_dtl">
                    <form>
                        <div class="form fr">
<?php
    echo '              
                        <div class="form_item">
                            <label>تاریخ ثبت:</label>
                            <input disabled type="text" value="'.correctDate($row["create_date"]).'">
                        </div>
                        <div class="form_item">
                            <label>متن نظر:</label>
                            <textarea disabled>'.$row["comment"].'
                            </textarea>
                        </div>
                    ';
?>
                        </div>
                    </form>
                </div>
            </div>
            <div class="ssrv">
                <div class="ssrv_title">پاسخ</div>        
                <div class="ssrv_dtl">
                    <form action="<?php echo $URL; ?>" method="post">
                        <div class="form fr">
<?php
    echo '              
                    <div class="form_item">
                        <label>تاریخ پاسخ:</label>
                        <input disabled type="text" value="'.correctDate($row["reply_date"]).'">
                    </div>
                    <div class="form_item">
                        <label>متن پاسخ:</label>
                        <textarea name="reply" data-emoji-input="unicode" data-emojiable="true" >'.$row["reply"].'</textarea>
                    </div>
                ';
?>
                        </div>
                    
                </div>
            </div>
            <input type="submit" name="edit" class="btn" value="ثبت پاسخ">
            </form>
<?php
        }
    }
?>