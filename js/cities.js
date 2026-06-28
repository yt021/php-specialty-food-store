function get_cities(county,item){
    pp = item.parentNode.parentNode;
    post_data = "&county="+county;
    var xmlHR = new XMLHttpRequest();
    url = base_url+"modules/cart/cities_dd.php";
    xmlHR.open("POST", url, false);
    xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHR.send(post_data);
    data = xmlHR.responseText;
    city_input = pp.getElementsByTagName("input")[1];
    city_input.value = "";
    ul = pp.getElementsByClassName("cities_dd")[0];
    ul.classList.remove("disabled");
    ul.innerHTML = get_ul_cities(data);
    ready_selects(ul.parentNode);
    
    select = pp.getElementsByClassName('cities_select')[0];
    select.classList.remove("disabled");
    select.innerHTML = get_select_cities(data);
    
    return;
}
function get_ul_cities(data){
    res = '<li class="dd_option show"></li>';
    last_index = data.indexOf(",");
    $k = 0;
    while(last_index>=0){
        if($k == 0){
            $k = 1;
            res += '<li class="hide place_holder">'+data.substring(0,last_index)+'</li>';
        }else{
            res += '<li class="dd_option">'+data.substring(0,last_index)+'</li>';
        }
        data = data.substring(last_index+1);
        last_index = data.indexOf(",");
    }
    return res;
}
function get_select_cities(data){
    res = "";
    last_index = data.indexOf(",");
    $k = 0;
    while(last_index>=0){
        if($k == 0){
            $k = 1;
            res += '<option>'+data.substring(0,last_index)+'</li>';
        }else{
            res += '<option value="'+data.substring(0,last_index)+'">'+data.substring(0,last_index)+'</li>';
        }
        data = data.substring(last_index+1);
        last_index = data.indexOf(",");
    }
    return res;
}
