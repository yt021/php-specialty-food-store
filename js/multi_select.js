function item_select(item){
    id = item.getElementsByClassName("id_span")[0].innerHTML;
    if(item.classList.contains('choosed')){
        item.classList.remove('choosed');
        remove_item(id);
    }else{
        item.classList.add('choosed');
        add_item(id);
    }
    return;
}
cs_list = [];
function find_item(id){
    n = cs_list.length;
    for(i=0;i<n;i++){
        if(cs_list[i] == id){
            return i;
        }
    }
    return -1;
}
function remove_item(id){
    if(find_item(id) != -1){
        f = find_item(id);
        n = cs_list.length;
        for(i=f;i<n-1;i++){
            cs_list[i] = cs_list[i+1];
        }
        cs_list.length = n-1;
    }
    return;
}
function add_item(id){
    if(find_item(id) == -1){
        n = cs_list.length;
        cs_list[n] = id;
    }
    return;
}
function up_item(id){
    if(find_item(id) != -1 && find_item(id) != 0){
        f = find_item(id);
        a = cs_list[f-1];
        cs_list[f-1] = cs_list[f];
        cs_list[f] = a;
    }
    return;
}
function down_item(id){
    if(find_item(id) != -1 && find_item(id) != cs_list.length-1){
        f = find_item(id);
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
function multi_select(field='ms',address="",n_tab=""){
    cs_str = "";
    n = cs_list.length;
    for(i=0;i<n;i++){
        cs_str += cs_list[i] + ",";
    }
    cs_str.length = cs_str.length - 1;
    sub_show(field,cs_str,address,n_tab);
}


function check_box_woi(item){
    if(item.classList.contains("icon-chk")){
        item.classList.add("icon-chkfl");
        item.classList.remove("icon-chk");
    }else if(item.classList.contains("icon-chkfl")){
        item.classList.remove("icon-chkfl");
        item.classList.add("icon-chk");
    }
}

function select_all_items(item){
    cs_list = [];
    id_spans = document.getElementsByClassName("id_span");
    for(i=0;i<id_spans.length;i++){
         add_item(id_spans[i].innerHTML);
         id_spans[i].parentNode.getElementsByClassName("chkholder")[0].classList.remove("icon-chk");
         id_spans[i].parentNode.getElementsByClassName("chkholder")[0].classList.add("icon-chkfl");
         id_spans[i].parentNode.classList.add('choosed');
    }
    item.setAttribute("onclick","deselect_all_items(this)");
    return;
}
function deselect_all_items(item){
    cs_list = [];
    id_spans = document.getElementsByClassName("id_span");
    for(i=0;i<id_spans.length;i++){
         id_spans[i].parentNode.getElementsByClassName("chkholder")[0].classList.remove("icon-chkfl");
         id_spans[i].parentNode.getElementsByClassName("chkholder")[0].classList.add("icon-chk");
         id_spans[i].parentNode.classList.remove('choosed');
    }
    item.setAttribute("onclick","select_all_items(this)");
    return;
}