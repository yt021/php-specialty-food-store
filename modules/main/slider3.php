<?php
    $slides_no = getVarFromDB("slider","count(id)","show_flag",1);
    if($slides_no > 0){
?>

<div id="slider" class="slider_holder" style="height:<?php echo (getVarFromDB("content_usages","d_height","flag","slider")/getVarFromDB("content_usages","d_width","flag","slider")*100); ?>vw;">

<?php
    if($slides_no > 1){
?>

    <div class="arrow_btn l vCent" id="slider_left" onclick="active_slide(+1,'+');">
        <span class="icon-a"></span>
    </div>
    <div class="arrow_btn r vCent" id="slider_right" onclick="active_slide(-1,'+');">
        <span class="icon-a"></span>
    </div>
    
<?php
    }
?>
    
    <?php
        $st = "SELECT image_id,title,link FROM slider WHERE show_flag = 1 AND del_flag = 0;";
        $st = $mysqli->prepare($st);
        if(!$st->execute()){
            echo "E";
            exit;
        }
        $res = $st->get_result();
        $k = 0;
        $dir = $s."content";
        while($row = $res->fetch_assoc())
        {
            
            $iid = $row["image_id"];
            if(var_exist($iid,"content","id") && getVarFromDB("content","type","id",$iid) == "Image"){
                $image_fn = getVarFromDB("content","file_name","id",$iid);
                $ff = pathinfo($image_fn, PATHINFO_EXTENSION);
                $fn = pathinfo($image_fn, PATHINFO_FILENAME);
                $webp_path = "$dir/webp/$fn.webp";
                $webp_m_path = "$dir/webp/$fn-mob.webp";
                $img_path = "$dir/$image_fn";
                $img_m_path = "$dir/$fn-mob.$ff";
    ?>
    <div class="slider <?php if($k == 0)echo "active";//if($k == 2)echo "post_active";if($k == 0)echo "pre_active"; ?>" alt="<?php echo $row["title"]; ?>" >
        <picture>
                <source type="image/webp" srcset="<?php  echo $webp_m_path; ?> 500w,<?php  echo $webp_path; ?>">
                <img src="<?php echo $img_m_path; ?>" srcset="<?php echo $img_m_path; ?> 500w,<?php echo $img_path; ?> ">
        </picture>
        <?php if($row["link"]){ ?><a href='<?php echo $row["link"]; ?>'></a><?php } ?>
    </div>
    <?php
        $k++;
            }
        }
    ?>
    
<?php
    if($slides_no > 1){
?>
    <ul class="bottom_btn">
    <?php
        for($index=0;$index<$k;$index++){
    ?>
        <li class="bb <?php if($index == 0)echo "bb_active"; ?>" onclick="active_slide(<?php echo $index; ?>)"></li>
    <?php
        }
    ?>
    </ul>
<?php
    }
?>
</div>
<script type="text/javascript">
<?php
    if($slides_no > 1){
?>
var tis = 3500;
var delay = 500;
var slider_interval = setInterval(slider_move,tis);
var slider = document.getElementById('slider');
var slides = slider.getElementsByClassName('slider');
var bb = slider.getElementsByClassName('bb');

function slider_move(){
    n = slides.length;
    if(slider.getElementsByClassName('active')){
        active = slider.getElementsByClassName('active')[0];
        i = childClassIndex(active,'slider');
    }else{i=0;}
    pre = (i+1)%n;
    slider_to(i,pre,1);
    return;
}
function active_slide(index,way=""){
    clearInterval(slider_interval);
    n = slides.length;
    if(slider.getElementsByClassName('active')){
        active = slider.getElementsByClassName('active')[0];
        i = childClassIndex(active,'slider');
    }else{i=0;}
    if(way=="+"){
        next = (i+index+n)%n;
    }else{
        next = (index+n)%n;
        index = 0;
    }
    slider_to(i,next,index);
    slider_interval = setInterval(slider_move,tis);
    return;
}
function slider_to(now,next,way){
    if(way != 1 && way != -1){if(next>now)way = 1;else way=-1;}
    if(way == 1){next_str = 'pre_active';now_str = 'post_active';}
    if(way == -1){next_str = 'post_active';now_str = 'pre_active';}
    
    next_bb = bb[next];
    now_bb = bb[now];
    
    now = slides[now];
    next = slides[next];
    
    next.classList.add(next_str);
    setTimeout(function(){
        next.classList.remove('hide');
        next.classList.remove(next_str);
        now.classList.remove('active');
        now.classList.add(now_str);
        next.classList.add('active');
        now_bb.classList.remove('bb_active');
        next_bb.classList.add('bb_active');
        setTimeout(function(){
            now.classList.remove(now_str);
        },delay)
    },delay);
    
}
<?php
    }
?>


</script>


<?php
    }
?>