<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if($_SESSION[$cf]->sub_id == "detail"){
?>
        <h2 class="tac">توضیحات</h2><br>
<?php include $bu."modules/admin/TE_toolbars.php"; ?>

<?php
    }else{
?>
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
    $img_str = getVarFromDB($tb,"img_str","id",$_SESSION[$cf]->id); 
    while($row = $res->fetch_assoc())
    {
        $file = $dir.$row["file_name"];
?>
            <div class="<?php if($_SESSION[$cf]->sub_id == "multi" && $img_str && get_str_index($img_str,",",$row["id"])[0] !== null) echo" choosed "; ?>option img" onclick="<?php echo $onc; ?>">
                <img src="<?php echo $file; ?>" />
                <div class="btn mid"></div>
                <span class="id_span hide"><?php echo $row["id"]; ?></span>
            </div>
<?php
    }
    if($_SESSION[$cf]->sub_id == "multi"){
?>
            <div class="plus img" onclick="multi_select()">
                ثبت
            </div>
<?php
    }
?>
        </div>
        
        
<script type="text/javascript">
function single_select(item){
    id = item.getElementsByClassName("id_span")[0].innerHTML;
    sub_show('img_str',id);
    return;
}
function img_select(item){
    id = item.getElementsByClassName("id_span")[0].innerHTML;
    if(item.classList.contains('choosed')){
        item.classList.remove('choosed');
        remove_image(id);
    }else{
        item.classList.add('choosed');
        add_image(id);
    }
    return;
}
image_list = [];
function find_image(id){
    n = image_list.length;
    for(i=0;i<n;i++){
        if(image_list[i] == id){
            return i;
        }
    }
    return -1;
}
function remove_image(id){
    if(find_image(id) != -1){
        f = find_image(id);
        n = image_list.length;
        for(i=f;i<n-1;i++){
            image_list[i] = image_list[i+1];
        }
        image_list.length = n-1;
    }
    return;
}
function add_image(id){
    if(find_image(id) == -1){
        n = image_list.length;
        image_list[n] = id;
    }
    return;
}
function up_image(id){
    if(find_image(id) != -1 && find_image(id) != 0){
        f = find_image(id);
        a = image_list[f-1];
        image_list[f-1] = image_list[f];
        image_list[f] = a;
    }
    return;
}
function down_image(id){
    if(find_image(id) != -1 && find_image(id) != image_list.length-1){
        f = find_image(id);
        a = image_list[f+1];
        image_list[f+1] = image_list[f];
        image_list[f] = a;
    }
    return;
}
function show_image_list(){
    alert(image_list);
    return;
}
function multi_select(){
    img_str = "";
    n = image_list.length;
    for(i=0;i<n;i++){
        img_str += image_list[i] + ",";
    }
    img_str.length = img_str.length - 1;
    sub_show('img_str',img_str);
}
<?php
    if($img_str){
        $images=get_str_index($img_str,",")[1];
        foreach($images as $image){
            echo "add_image($image);";
        }
    }
?>

</script>
<?php
}
?>
<?php
        }
    }
?>