vaz=0;
var code_send_in_progress = false;

function code_find_parent_with_tag(node, tagName){
    tagName = (tagName || "").toUpperCase();
    while(node && node.nodeType === 1){
        if(node.tagName && node.tagName.toUpperCase() === tagName){
            return node;
        }
        node = node.parentNode;
    }
    return null;
}

function code_find_tel_input(source){
    var node = source;
    if(node && node.nodeType !== 1){
        node = null;
    }
    if(node && node.tagName && node.tagName.toUpperCase() === "INPUT" && node.name === "tel"){
        return node;
    }

    var form = null;
    if(node && typeof node.closest === "function"){
        form = node.closest("form");
    }
    if(!form){
        form = code_find_parent_with_tag(node, "FORM");
    }
    if(form){
        var formTel = form.querySelector('input[name="tel"]');
        if(formTel){
            return formTel;
        }
    }

    var telContainer = document.getElementById("tel");
    if(telContainer){
        var telInput = telContainer.querySelector('input[name="tel"]');
        if(telInput){
            return telInput;
        }
    }
    return null;
}

function code_find_send_button(form){
    if(!form){
        return null;
    }
    var telContainer = form.querySelector("#tel");
    if(!telContainer){
        telContainer = document.getElementById("tel");
    }
    if(!telContainer){
        return null;
    }
    var btn = telContainer.querySelector("[data-send-create-code]");
    if(btn){
        return btn;
    }
    btn = telContainer.querySelector("a[onclick*='send_create_code'],button[onclick*='send_create_code']");
    if(btn){
        return btn;
    }
    return null;
}

function init_code_login_submit(){
    var form = document.getElementById("signup_form");
    if(!form){
        return;
    }
    var telInput = form.querySelector('input[name="tel"]');
    if(!telInput){
        return;
    }

    telInput.addEventListener("keydown", function(e){
        if((e.key && e.key === "Enter") || e.keyCode === 13){
            var telStage = document.getElementById("tel");
            if(telStage && !telStage.classList.contains("hide")){
                e.preventDefault();
                var triggerBtn = code_find_send_button(form);
                send_create_code(triggerBtn || telInput);
            }
        }
    });

    form.addEventListener("submit", function(e){
        var telStage = document.getElementById("tel");
        if(telStage && !telStage.classList.contains("hide")){
            e.preventDefault();
            var triggerBtn = code_find_send_button(form);
            send_create_code(triggerBtn || telInput);
        }
    });
}

if(document.readyState === "loading"){
    document.addEventListener("DOMContentLoaded", init_code_login_submit);
}else{
    init_code_login_submit();
}

function show_error(response){
    clear_hints();
    div = document.getElementById("user_error");
    div.innerHTML = response;
    div.classList.remove("hide")
}
function show_hint(response){
    clear_hints();
    div = document.getElementById("user_hint");
    div.innerHTML = response;
    div.classList.remove("hide")
}
function clear_hints(){
    div = document.getElementById("user_error");
    div.innerHTML = "";
    div.classList.add("hide");
    div = document.getElementById("user_hint");
    div.innerHTML = "";
    div.classList.add("hide");
}
function show_stage(stage){
    switch(stage){
        case 0:
            close = "create_code";
            open = "tel";
            break;
        case 1:
            close = "tel";
            open = "create_code";
            break;
        default:
            return;
    }
    clear_hints();
    
    open = document.getElementById(open);
    close = document.getElementById(close);
    
    close.classList.add("hide");
    open.classList.remove("hide");
    open.getElementsByTagName("input")[0].focus();
    return;
}

function send_create_code(item){
    if(code_send_in_progress){
        return 0;
    }
    var xmlHR = new XMLHttpRequest();
    url = base_url+"modules/cart/create_code.php";
    telInput = code_find_tel_input(item);
    if(!telInput){
        show_error("شماره تلفن را وارد نمایید.");
        return 0;
    }
    tel = telInput.value;
    post_data = "tel="+encodeURIComponent(tel);
    code_send_in_progress = true;
    xmlHR.open("POST", url, true);
    xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHR.send(post_data);
    
    xmlHR.addEventListener('readystatechange',
        function(e){
            if (xmlHR.readyState == 4 && xmlHR.status == 200) {
                data = xmlHR.responseText;
                if(data[0] == "D"){
                    document.getElementById('create_code').getElementsByTagName('h4')[0].innerHTML = data.substr(1) + " را وارد نمایید.";
                    show_stage(1);
                    start_timer();
                    code_send_in_progress = false;
                    return 1;
                }
                if(data[0]== "E"){
                    response = data.substring(1);
                    show_error(response);
                    code_send_in_progress = false;
                    return 0;
                }
                code_send_in_progress = false;
            }
            else if (xmlHR.readyState == 4 && xmlHR.status != 200) {
                code_send_in_progress = false;
                return 0;
            }
        }
    )
}
function rep_create_code(item){
    var xmlHR = new XMLHttpRequest();
    url = base_url+"modules/cart/rep_code.php";
    xmlHR.open("POST", url, true);
    xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHR.send("");
    document.getElementById("timer").innerHTML = "00:30";
    start_timer();
    xmlHR.addEventListener('readystatechange',
        function(e){
            if (xmlHR.readyState == 4 && xmlHR.status == 200) {
                data = xmlHR.responseText;
                response = data.substring(1);
                if(data[0] == "D"){
                    show_hint(response)
                    return 1;
                }
                if(data[0]== "E"){
                    show_error(response);
                    return 0;
                }                
                if(data[0]== "A"){
                    show_stage(0);
                    show_error(response);
                    return 0;
                }
            }
            else if (xmlHR.readyState == 4 && xmlHR.status != 200) {
                return 0;
            }
        }
    )
}
function start_timer(){
    time_str = document.getElementById("timer").innerHTML;

    colon = time_str.lastIndexOf(":");
    seconds = parseInt(time_str.substring(colon+1));
    time_str = time_str.substring(0,colon);
    colon = time_str.lastIndexOf(":");
    minutes = parseInt(time_str.substring(colon+1));

    time = minutes*60+seconds;
    time = time-1;
    
    if(time === 0){
        show_timer(0);
        clearInterval(time_interval);
        delete time_interval;
    }else{
        if(typeof time_interval !== 'undefined'){
            document.getElementById("recode").classList.add("hide");
        }else{
            show_timer(1);
            time_interval = setInterval(start_timer,1000);
        }
    }
    
    minutes = Math.floor(time/60);
    seconds = time - minutes*60;
    if(seconds < 10){seconds = "0"+seconds;}
    if(minutes < 10){minutes = "0"+minutes;}
    time_str = minutes+":"+seconds;
    document.getElementById("timer").innerHTML = time_str;
    
}
function reset_timer(){
    document.getElementById("timer").innerHTML = "00:30";
    start_timer();
    return;
}
function show_timer(state){
    switch(state){
        case 0:
            document.getElementById("recode").classList.remove("hide");
            document.getElementById("timer_holder").classList.add("hide");
            break;
        case 1:
            document.getElementById("recode").classList.add("hide");
            document.getElementById("timer_holder").classList.remove("hide");
            break;
    }
    return;
}