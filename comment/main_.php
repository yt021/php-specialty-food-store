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