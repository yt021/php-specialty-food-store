function childClassIndex(item,cls){
    return Array.prototype.indexOf.call(item.parentNode.getElementsByClassName(cls), item);
}
function childIndex(node){
    for (i=0;(node=node.previousSibling);i++);
    return i;
}

function S_E(item){
    item.classList.add('sd');
    return;
}
function S_C(item){
    items = item.getElementsByClassName("sd");
    for(i=0;i<items.length;i++){
        items[i].classList.remove('sd');
    }
    return;
}
function H_E(item){
    item.classList.add("hide");
    return;
}
function H_C_Class(item,name){
    items = item.getElementsByClassName(name);
    for(i=0;i<items.length;i++){
        items[i].classList.add('hide');
    }
    return;
}
function H_C_Tag(item,name){
    items = item.getElementsByTagName(name);
    for(i=0;i<items.length;i++){
        items[i].classList.add('hide');
    }
    return;
}
function SH_E(item){
    item.classList.remove("hide");
    return;
}
