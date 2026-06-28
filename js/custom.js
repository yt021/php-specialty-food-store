function add_to_box(item){
    post_data = get_box_data(item);
    post_data += "&func=add";
    box_handle(post_data);
}
function del_from_box(item){
    post_data = get_box_data(item);
    post_data += "&func=del";
    box_handle(post_data);
}

function inc_box(item){
    post_data = get_box_data(item);
    post_data += "&func=inc";
    box_handle(post_data);
}
function dec_box(item){
    post_data = get_box_data(item);
    post_data += "&func=dec";
    box_handle(post_data);
}
function get_box_data(item){
    pn = item.parentNode;
//    pn=item;
    p = pn.id;
    post_data = "&pid="+p;
    return post_data;
}
function box_handle(post_data){
    var xmlHR = new XMLHttpRequest();
    url = base_url+"modules/custom/add_to_box.php";
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
                    box_no = data.substring(0,pos);
                    data = data.substring(pos+1);
                    pos = data.indexOf("-");
                    box_res = data.substring(0,pos);
                    data = data.substring(pos+1);
                    pos = data.indexOf("|");
                    box_con = data.substring(0,pos);
                    data = data.substring(pos+1);
//                    document.getElementById("box_no").innerHTML = box_no;
//                    document.getElementById("box_residual").innerHTML = box_res;
//                    document.getElementById("box_con").innerHTML = box_con;
                    div = document.getElementById("box_fill");
                    if(div){
                        top_offset = 0;
                        if(box_no>1)
                        top_offset = div.getElementsByTagName('li')[box_no-1].offsetTop - div.offsetTop;
                        
                    div.outerHTML = data;
                        if(post_data.indexOf('&func=add') != -1){
                            document.getElementById("box_fill").scrollTop = top_offset;
                        }
                    }
                }
            }
            return;
        }
    )
    return;
}