function option_value(item){
return item.getElementsByTagName('input')[0].value}
function show_select_serie(value,id){
    if(value == "multi" || value == "period"){
    document.getElementById(id).classList.remove("hide");
    }else{
        document.getElementById(id).classList.add("hide");
    }
}
function option_check(item){
    if(item.classList.contains("checked")){
        item.getElementsByTagName("input")[0].checked = false;
        item.classList.remove("checked");
    }else{
        item.getElementsByTagName("input")[0].checked = true;
        item.classList.add("checked");
    }
    return;
}
function option_radio(item){
    if(item.classList.contains("checked")){
        return
    }else{
        options = item.parentNode.getElementsByClassName("options_item");
        for(i=0;i<options.length;i++){
            options[i].getElementsByTagName("input")[0].checked = false;
            options[i].classList.remove("checked");
        }
        item.getElementsByTagName("input")[0].checked = true;
        item.classList.add("checked");
        
    }
    return;
}