<?php
    if(isset($indexed)){
        if($indexed == 1){?>

<main>
    <div class="content">
<!--        <h1 class="tac">تکمیل فرآیند خرید</h1>-->
            
<br><br><br>                در اینجا به درگاه پرداخت منتقل خواهد شد<br><br><br><br><br>
        <form class="info" action="<?php echo $URL; ?>" method="post">
            <input name="submit" type="submit" class="btn middle" value="صفحه بازگشت از درگاه پرداخت اینترنتی">
        </form>
    </div>
</main>        
<?php
        }
    }
?>