function add_to_cart(item){
    post_data = get_cart_data(item);
    post_data += "&func=add";
    cart_handle(post_data);
}
function del_from_cart(item){
    post_data = get_cart_data(item);
    post_data += "&func=del";
    cart_handle(post_data);
}
function change_weight_cart(item){
    pn = item.parentNode;
    post_data = get_cart_data(item),
    old_weight = pn.getElementsByClassName("cur_weight")[0];
    old_weight = old_weight.innerText;
    old_weight = old_weight.substr(0,old_weight.indexOf(" "));
    post_data += "&func=change_w&old_weight="+old_weight;
    cart_handle(post_data);
}
function inc_cart(item){
    post_data = get_cart_data(item);
    post_data += "&func=inc";
    cart_handle(post_data);
}
function dec_cart(item){
    post_data = get_cart_data(item);
    post_data += "&func=dec";
    cart_handle(post_data);
}
function get_cart_data(item){
    pn = item.parentNode;
    p = pn.id;
    weight = pn.getElementsByClassName("weight_options")[0].getElementsByClassName("sd")[0].innerText;
    weight = weight.substr(0,weight.indexOf(" گرم"));
    opbid = pn.getAttribute('name');
    post_data = "&pid="+p+"&weight="+weight+"&opbid="+opbid;
    return post_data;
}
function cart_handle(post_data){
    var xmlHR = new XMLHttpRequest();
    url = base_url+"modules/cart/add_to_cart.php";
    xmlHR.open("POST", url, true);
    xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHR.send(post_data);
    
    xmlHR.addEventListener('readystatechange',
        function(e){
            if (xmlHR.readyState == 4 && xmlHR.status == 200) {
                data = xmlHR.responseText;
                if(data.indexOf("</script>")>= 0){
                    data = data.substring(data.indexOf("</script>")+11);
                }
                if(data[0] == "D"){
                    data = data.substring(1);
                    pos = data.indexOf("-");
                    cart_no = data.substring(0,pos);
                    data = data.substring(pos+1);
                    pos = data.indexOf("|");
                    cart_total = data.substring(0,pos);
                    data = data.substring(pos+1);
                    document.getElementById("h_cart_no").innerHTML = cart_no;
                    document.getElementById("h_cart_tot").innerHTML = cart_total;
                    div = document.getElementById("cart_holder");
                    if(div){
                    div.innerHTML = data;
                    }
                }
            }
            return;
        }
        
    )
    return;
}
function open_product_weight(item){
    pp = item.parentNode.parentNode;
    pp.getElementsByClassName("weight_options")[0].classList.remove("hide");
    item.parentNode.classList.add("hide");
    return;
}
function select_weight(item){
    pp = item.parentNode.parentNode;
    w = item.innerText;
    w = w.substr(0,w.indexOf(" "));
    pn = item.parentNode;
    ii = Array.prototype.indexOf.call(pn.children, item);
    price = pp.getElementsByClassName("price")[0];
    S_C(item.parentNode);
    S_E(item);
    H_C_Tag(price,"span");
    SH_E(price.children[ii]);
}

function open_cart_local(){
    SH_E(document.getElementById("cart_local"));
}