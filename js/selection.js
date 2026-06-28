ready_box_items(document);
function ready_box_items(item){
    boxes = item.getElementsByClassName('box');
    for(i = 0;i<boxes.length;i++){
        box = boxes[i];
        reset_box_item(box);
    }
}
function reset_box_item(box){
    box_items = box.getElementsByClassName('box_item');
    for(j=0;j<box_items.length;j++){
        bi = box_items[j];
        bi.classList.remove('selected');
        bi.classList.remove('disabled');
        bi.addEventListener('click',select_box_item,false);
        if(dsbl_no = bi.getElementsByClassName('dsbl_no')[0]){
            dsbl_no.innerHTML = "0";
        }else{
            div = document.createElement('DIV');
            div.classList.add('hide');
            div.classList.add('dsbl_no');
            div.innerHTML = "0";
            bi.appendChild(div);
        }
    }
    return;
}
function select_box_item(e){
    bi = this;
    if(bi.classList.contains('disabled')){
        return;
    }
    if(bi.classList.contains('selected')){
        bi.classList.remove('selected');
        i= -1;
    }else{
        bi.classList.add('selected');
        i = 1;
    }
    if(a = bi.getElementsByClassName('dsbl_list')[0]){
        a = a.innerHTML;
        for(j = 0;j<a.length;j++){
            disable_box_item(bi.parentNode,parseInt(a[j]),i);
        }
    }
}
function disable_box_item(PN,index,i){
    dsbld_box = PN.getElementsByClassName('box_item')[index];
    dsbl_no = dsbld_box.getElementsByClassName('dsbl_no')[0];
    dsbl_no.innerHTML = (parseInt(dsbl_no.innerHTML)+i);
    check_disable_box_item(dsbld_box);
}
function check_disable_box_item(item){
    dsbl_no = parseInt(item.getElementsByClassName('dsbl_no')[0].innerHTML);
    if(dsbl_no > 0){
        item.classList.add('disabled');
        item.classList.remove('selected');
    }else{
        item.classList.remove('disabled');
    }
}


ready_selects(document);
function select_option(e){
    so = this;
    dde = so.parentNode;
    fso = dde.getElementsByClassName('show')[0];
    fso.innerHTML = so.innerHTML;
    if(e = dde.getElementsByClassName('selected')[0]){
        e.classList.remove('selected');
    }
    so.classList.add('selected');
//    if(a = so.getElementsByTagName('span')[0]){
//        a = a.innerHTML;
//    }else{
//        a = so.innerHTML;
//    }
    dde.parentNode.getElementsByTagName('input')[0].value = so.innerText;
    if(dde.classList.contains("counties_dd")){
        get_cities(so.innerText,dde);
    }
}
function ready_selects(item){
    if(selects = item.getElementsByClassName('select_dd')){
        for(i=0;i<selects.length;i++){
            select = selects[i];
            set_select_click(select);
            reset_select(select);
        }
    }else{
        return;
    }
}
function set_select_click(select){
    options = select.getElementsByClassName('dd_option');
    for(j=0;j<options.length;j++){
        o = options[j];
        if(!o.classList.contains('show')){
        o.addEventListener('click',select_option,false);
        }
    }
return;
}
function reset_select(select){
    options = select.getElementsByClassName('dd_option');
    for(j=0;j<options.length;j++){
        options[j].classList.remove('selected');
    }
    select.getElementsByClassName('show')[0].innerHTML = select.getElementsByClassName('place_holder')[0].innerHTML;
    return;
}
function check_select(select_dd){
    pre_text = select_dd.getElementsByClassName('place_holder')[0].innerText;
    show_text = select_dd.getElementsByClassName('show')[0].innerText;
    remove_er(select_dd);
    if(show_text === pre_text){
//        show_er(select_dd);
        return 1;
    }
    return show_text;
}

function check_box(item){
    if(item.classList.contains("icon-chk")){
        item.classList.add("icon-chkfl");
        item.classList.remove("icon-chk");
        item.parentNode.getElementsByTagName("input")[0].checked = true;
    }else if(item.classList.contains("icon-chkfl")){
        item.classList.remove("icon-chkfl");
        item.classList.add("icon-chk");
        item.parentNode.getElementsByTagName("input")[0].checked = false;
    }
}

function change_select_input(item,value){
    if(value != ""){
    item.parentNode.getElementsByTagName('input')[0].value = value;
        if(item.classList.contains("counties"))
    get_cities(value,item);
    }
    return;
}