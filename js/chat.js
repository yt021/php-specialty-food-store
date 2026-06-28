document.getElementById('chat_box').getElementsByTagName('input')[0].addEventListener('keydown',(e)=>{if(e.keyCode == 13)send_pm();});
var check_interval;
var interv = 2000;
var interv_count = 0;
var big_interv = 30000;
var big_interv_count = 0;

function start_chat(){
    logname = document.getElementById("logname").value;
    url = base_url+"modules/chat/new_chat.php";
    var xmlHR = new XMLHttpRequest();
    xmlHR.open("POST", url, true);
    xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHR.send('ln='+logname);
    xmlHR.addEventListener('readystatechange',
        function(e){
            if (xmlHR.readyState == 4 && xmlHR.status == 200) {
                data = xmlHR.responseText;
                switch(data[0]){
                    case 'D':
                        $data = data.substring(1);
                        state = 0;
                        document.getElementById('login_holder').outerHTML = $data;
                        
                        break;
                }
            }
        }
        
    )
    
}

function open_chat(){
document.getElementById('chat_open').classList.add('hide');
document.getElementById('chat_box').classList.remove('hide');
document.getElementById('chat_open').classList.remove('vibrate');
if(state == 0){
read_chat();
reset_interv();
}
}
function close_chat(){
document.getElementById('chat_open').classList.remove('hide');
document.getElementById('chat_box').classList.add('hide');
if(state == 0){
reset_big_interv();
}
}
function reset_interv(){
clearInterval(check_interval);
interv_count = 0;
check_interval = setInterval(() => {
    read_chat();
}, interv);
}
function reset_big_interv(){
clearInterval(check_interval);
big_interv_count = 0;
check_interval = setInterval(() => {
    check_new();
}, big_interv);
}
function read_chat(){
chat_holder = document.getElementById('chat_holder');
if(chat_holder.children.length)
last_read = chat_holder.children[chat_holder.children.length-1].id;
else last_read = 0;
url = base_url+"modules/chat/read_pm.php";
var xmlHR = new XMLHttpRequest();
xmlHR.open("POST", url, true);
xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xmlHR.send('lr='+last_read);
xmlHR.addEventListener('readystatechange',
    function(e){
        if (xmlHR.readyState == 4 && xmlHR.status == 200) {
            data = xmlHR.responseText;
            switch(data[0]){
                case 'D':
                    reset_interv();
                    $data = data.substring(1);
                    chat_holder.innerHTML += $data;
                    chat_holder.scrollTop =  chat_holder.scrollHeight;
                    break;
                case 'S':
                    interv_count++;
                    if(interv_count>10){
                        if(interv_count<20){
                            clearInterval(check_interval);
                            check_interval = setInterval(() => {
                                read_chat();
                            }, interv*5);
                        }else{
                            clearInterval(check_interval);
                            check_interval = setInterval(() => {
                                read_chat();
                            }, interv*10);
                        }
                    }
                    break;
                case 'E':
                clearInterval(check_interval);
                break;
            }
        }
    }
    
)
}
function check_new(){
big_interv_count++;
if(big_interv_count > 10){
    if(big_interv_count < 20){
        clearInterval(check_interval);
        check_interval = setInterval(()=>{check_new();},big_interv*5);
    }else{
        clearInterval(check_interval);
        if(big_interv_count<30){
            check_interval = setInterval(()=>{check_new();},big_interv*10);
        }else{
            close_chat(1);
        }
    }
}
chat_holder = document.getElementById('chat_holder');
if(chat_holder.children.length)
last_read = chat_holder.children[chat_holder.children.length-1].id;
else last_read = 0;
var xmlHR = new XMLHttpRequest();
url = base_url+"modules/chat/check_pm.php";
xmlHR.open("POST", url, true);
xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xmlHR.send('lr='+last_read);
xmlHR.addEventListener('readystatechange',
    function(e){
        if (xmlHR.readyState == 4 && xmlHR.status == 200) {
            data = xmlHR.responseText;
            switch(data[0]){
                case 'D':
                    document.getElementById('chat_open').classList.add('vibrate');
                    break;
            }
        }
    }
    
)
}
function send_pm(){
item = document.getElementById('chat_box').getElementsByTagName('input')[0];
var xmlHR = new XMLHttpRequest();
url = base_url+"modules/chat/send_pm.php";
xmlHR.open("POST", url, true);
xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xmlHR.send('new='+item.value);

xmlHR.addEventListener('readystatechange',
    function(e){
        if (xmlHR.readyState == 4 && xmlHR.status == 200) {
            data = xmlHR.responseText;
            if(data[0] == 'D'){
                item.value = "";
                item.focus();
            }
        }
    }
    
);
read_chat();
return;
}