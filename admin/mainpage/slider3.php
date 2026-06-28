<?php
    if(isset($indexed)){
        if($indexed == 1){?>

<h2 class="tac">تصاویر</h2><br>
        <div class="images multi">
<?php
    if($_SESSION[$cf]->sub_id == "single"){
        $onc = "single_select(this)";
    }else if($_SESSION[$cf]->sub_id == "multi"){
        $onc = "img_select(this)";
    }
?>
<?php
    $st = "SELECT id,file_name FROM content WHERE del_flag = 0 AND type = 'Image' ORDER BY id DESC";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $dir = $s."content/";
    while($row = $res->fetch_assoc())
    {
        $file = $dir.$row["file_name"];
?>
            <div class="option img" onclick="<?php echo $onc; ?>">
                <img src="<?php echo $file; ?>" />
                <div class="btn mid"></div>
                <span class="id_span hide"><?php echo $row["id"]; ?></span>
            </div>

<?php
    }
?>
            
<script type="text/javascript">
function single_select(item){
    id = item.getElementsByClassName("id_span")[0].innerHTML;
    sub_show('edit_img',id);
    return;
}
</script>
            
<?php
        }
    }
?>