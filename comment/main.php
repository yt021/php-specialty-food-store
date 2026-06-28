<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(!isset($state) || !$state){
?>
<main>
    <div class="content">
        <h1 class="tac">ثبت نظر</h1>
        <form class="info" action="<?php echo $URL; ?>" method="post" style="margin:auto;">
<?php
    if(!isset($_SESSION["logged"])){
?>
            <div class="form_item fr">
                <label for="name">نام</label>
                <input type="text" required name="name"  placeholder="نام و نام خانوادگی" <?php if(isset($data["name"]))echo ' value="'.$data["name"].'" '; ?>>
            </div>
            <div class="form_item fr">
                <label for="tel">تلفن همراه</label>
                <input type="text" required name="tel" <?php if(isset($data["tel"]))echo ' value="'.$data["tel"].'" '; ?>  placeholder="*********09">
            </div>

<?php
    }
?>
            <div class="form_item fr ta">
                <label for="comment">نظر</label>
                <textarea name="comment" required data-emoji-input="unicode" data-emojiable="true"  placeholder="نظر خود را وارد کنید"><?php if(isset($data["address"]["address"]))echo ' value="'.$data["address"]["address"].'" '; ?></textarea>
            </div>
            <input type="submit" class="cb btn middle" name="submit" value="ثبت نظر">
        </form>
    </div>
    <div class="cut"></div>
    <div class="content">
        <div class="comments fr" style="width:100%;">
                <h2 class="tac">نظرات</h2>
                <ul style="height:auto;">
                <?php
                    $table = "comments";
                    $st = "SELECT name,comment,create_date,reply,reply_date FROM $table WHERE show_flag = 1 ORDER BY id DESC";
                    $st = $mysqli->prepare($st);
                    if(!$st->execute()){
                        echo "E";
                        exit;
                    }
                    $res = $st->get_result();
                    while($row = $res->fetch_assoc()){
                        
                        $name = $row["name"];
                        $cm = $row["comment"];
                        $date = correctDate($row["create_date"]);
                        $reply = $row["reply"];
                        $reply_date = correctDate($row["reply_date"]);
                ?>
                    <li>
                        <b><?php echo $name; ?></b>
                        <?php echo $cm; ?>
                        <span class="person"><?php echo $date; ?></span>
                        <?php
                            if($reply){
                                ?>
                                <div class="cb"></div>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <img class="admin_logo" src="<?php echo $s; ?>img/logo.png" />
                                <?php echo $reply;?>
                                <?php 
                            }
                        ?>
                    </li>
                <?php
                    }
                ?>
                </ul>
            </div>
        </div>
</main>
<?php
    }else{
?>
<main>
    <div class="content">
        <h1 class="tac">ثبت نظر</h1>
        <p class="tac">نظر شما با موفقیت ثبت شد.</p>
        <br>
        <a class="tac btn half mid" href="<?php echo $s; ?>">بازگشت به صفحه اصلی</a>
    </div>
</main>    

<?php
    }
?>
<?php
        }
    }
?>