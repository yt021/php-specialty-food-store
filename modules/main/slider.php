<?php
    $slides_no = getVarFromDB("slider","count(id)","show_flag",1);
    if($slides_no > 0){
        
        $slide_time = 2;
        $mt = 1;
        $tst = $slides_no * ($slide_time+$mt);
        $p[1] = 100*$mt/$tst;
        $p[2] = 100*$slide_time/$tst + $p[1];
        $p[3] = $p[1] + $p[2];
?>

<div id="slider" class="slider_holder active" style="" name="<?php echo $slide_time; ?>">
    
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
    <div class="slider" style="animation-delay:<?php echo $k*$slide_time- 1/2; ?>s"  >
        <picture>
                <source type="image/webp" srcset="<?php  echo $webp_m_path; ?> 500w,<?php  echo $webp_path; ?>">
                <img src="<?php echo $img_m_path; ?>" srcset="<?php echo $img_m_path; ?> 500w,<?php echo $img_path; ?> " 
                alt="<?php echo $row["title"]; ?>">
        </picture>
        <?php if($row["link"]){ ?><a href='<?php echo $row["link"]; ?>'></a><?php } ?>
    </div>
    <?php
        if($k == 0){
    ?>
    <div class="slider" id="back" style="animation-delay:<?php echo $k*$slide_time- 1/2; ?>s"  >
        <picture>
                <source type="image/webp" srcset="<?php  echo $webp_m_path; ?> 500w,<?php  echo $webp_path; ?>">
                <img src="<?php echo $img_m_path; ?>" srcset="<?php echo $img_m_path; ?> 500w,<?php echo $img_path; ?> " 
                alt="<?php echo $row["title"]; ?>">
        </picture>
        <?php if($row["link"]){ ?><a href='<?php echo $row["link"]; ?>'></a><?php } ?>
    </div>
    
    <?php
            
        }
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
        <li class="bb" onclick="active_slide(<?php echo $index; ?>)"></li>
    <?php
        }
    ?>
    </ul>
<?php
    }
?>
</div>
<style>
    div#slider{
        height:auto;
        /*display:table;*/
    }
    .slider#back{
        position:relative;
        opacity:0;
        animation-name:none;
    }
    div.slider_holder div.slider{
        top:0;
    }
    div.slider a{
        position:absolute;
    }
</style>







<?php
    }
?>