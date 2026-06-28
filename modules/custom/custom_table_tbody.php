<?php
    if(isset($_SESSION["custom"]) && is_a($_SESSION["custom"],"order_box")){
?>


<ul class="s_products" id="box_fill">
    <h4>
        <?php echo getVarFromDB("products","name","id",$_SESSION["custom"]->pid); ?>
        <br>
        ظرفیت باقیمانده: <span id="box_residual"><?php echo $_SESSION["custom"]->capacity-$_SESSION["custom"]->content->number(); ?></span> باکس
    </h4>
    <?php
        for($i=0;$i<sizeof($_SESSION["custom"]->content->orders);$i++){
            $item = $_SESSION["custom"]->content->orders[$i];
            $img_fn = getVarFromDB("products","first_img_id","id",$item->pid);
            $img_fn = getVarFromDB("content","file_name","id",$img_fn);
            $name = $item->name();
            for($no=0;$no<$item->no;$no++){
    ?>
        <li class="s_product" id="<?php echo $item->pid; ?>" onclick="dec_box(this.children[0])">
            <div class="img">
                <img src="<?php echo "../content/$img_fn"; ?>"/>
            </div>
            <?php echo $name; ?>
            <div class="plus">
            ×
            </div>
        </li>        
    <?php
            }
        }
        for($i=0;$i<($_SESSION["custom"]->capacity-$_SESSION["custom"]->content->number());$i++){
    ?>
    <li class="s_product under"><div class="img"></div></li>
    <?php } ?>
</ul>

<?php
    }else{
        echo "جعبه خالی است، محتوای آن را انتخاب کنید.";
    }
?>