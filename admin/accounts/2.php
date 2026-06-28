<?php
    if(isset($indexed)){
        if($indexed == 1){?>
        
        <h2 class="tac">ماژول‌ها</h2><br>
        <div class="images multi">
<?php
    $onc = "img_select(this)";
?>
<?php
    $st = "SELECT flag,name FROM admin_modules ORDER BY id ASC";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $master_a_str = getVarFromDB($tb,"access_str","id",$_SESSION["a_logged"]->uid);
    $cs_str = getVarFromDB($tb,"access_str","id",$_SESSION[$cf]->id);  //Current Select String
    while($row = $res->fetch_assoc())
    {
        if($master_a_str && get_str_index($master_a_str,",",$row["flag"])[0] !== null){
?>
            <div class="<?php if($cs_str && get_str_index($cs_str,",",$row["flag"])[0] !== null) echo" choosed "; ?>option img" onclick="<?php echo $onc; ?>">
                <?php echo $row["name"]; ?>
                <div class="btn mid"></div>
                <span class="id_span hide"><?php echo $row["flag"]; ?></span>
            </div>
<?php
        }
    }
?>
            <div class="plus img" onclick="multi_select()">
                ثبت
            </div>
<?php
?>
        </div>
        
        
<script type="text/javascript">
//function single_select(item){
//    id = item.getElementsByClassName("id_span")[0].innerHTML;
//    sub_show('img_str',id);
//    return;
//}
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
cs_list = [];
function find_image(id){
    n = cs_list.length;
    for(i=0;i<n;i++){
        if(cs_list[i] == id){
            return i;
        }
    }
    return -1;
}
function remove_image(id){
    if(find_image(id) != -1){
        f = find_image(id);
        n = cs_list.length;
        for(i=f;i<n-1;i++){
            cs_list[i] = cs_list[i+1];
        }
        cs_list.length = n-1;
    }
    return;
}
function add_image(id){
    if(find_image(id) == -1){
        n = cs_list.length;
        cs_list[n] = id;
    }
    return;
}
function up_image(id){
    if(find_image(id) != -1 && find_image(id) != 0){
        f = find_image(id);
        a = cs_list[f-1];
        cs_list[f-1] = cs_list[f];
        cs_list[f] = a;
    }
    return;
}
function down_image(id){
    if(find_image(id) != -1 && find_image(id) != cs_list.length-1){
        f = find_image(id);
        a = cs_list[f+1];
        cs_list[f+1] = cs_list[f];
        cs_list[f] = a;
    }
    return;
}
function show_cs_list(){
    alert(cs_list);
    return;
}
function multi_select(){
    cs_str = "";
    n = cs_list.length;
    for(i=0;i<n;i++){
        cs_str += cs_list[i] + ",";
    }
    cs_str.length = cs_str.length - 1;
    sub_show('edit',cs_str);
}
<?php
    if($cs_str){
        $cs = get_str_index($cs_str,",")[1];
        foreach($cs as $item){
            echo "add_image('$item');";
        }
    }
?>

</script>
        
        
        
<?php
        }
    }
?>
