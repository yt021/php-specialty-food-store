<div id="chat_people">
    <ul id="pp_holder">   
<?php
    $st = "SELECT chats.id,chats.name as cname,users.name as uname,users.tel FROM chats LEFT JOIN users ON users.id = chats.uid 
    WHERE chats.last_time > now() - INTERVAL 14 day ORDER BY chats.last_time DESC LIMIT 30";
    $res = return_sel_sql($st);
    while($row = $res->fetch_assoc()){
        if(!$row['uname'])$row['uname'] = $row['cname'];
?>
        <li id="<?php echo 'ch'.$row['id'];?>"><?php echo "${row['uname']} (${row['tel']})"; ?></li>
<?php
    }
?>  
    </ul>
</div>
<div id="chat_box">
    <div class="top_bar" id="top_bar" name=""></div>
    <ul id="chat_holder" class="chat_holder">
    </ul>
    <div class="chat_input">
        <input type="text" name="chat" placeholder="پیام خود را بنویسید">
        <span class="icon-a" onclick="send_pm()"></span>
    </div>
</div>
<style>
#chat_people{
    width:30%;
    height:600px;
    max-height:100%;
    border:1px solid #ddd;
    float:right;
}
#chat_people #pp_holder{
    width:100%;
    height:100%;
    overflow-y:scroll;
}
#chat_people #pp_holder li{
    float:none;
    padding:10px;
    cursor:pointer;
    /* border-bottom:1px solid gray; */
}
#chat_people #pp_holder li:hover{
    background:#eee;
}
#chat_people #pp_holder li.selected{
    background:rgb(65,159,217);
    color:white;
}
#chat_box{
    width:69%;
    height:600px;
    max-height:100%;
    border:1px solid #ddd;
    float:right;
    position:relative;
    overflow-x:hidden;
}
#chat_box .top_bar{
    position:absolute;
    top:0;
    right:0;
    width:100%;
    height:60px;
    /* background:#008000; */
    border-bottom:1px solid #ddd;
    line-height:60px;
    /* color:white; */
    padding-right:15px;
}
#chat_box .chat_holder{
    position:absolute;
    bottom:50px;
    right:0;
    background-image:linear-gradient(to bottom right,rgba(65,159,217,0.2),rgba(65,159,217,0.5));
    overflow-y:scroll;
    width:103%;
    height:calc(100% - 50px - 60px);
    /* background:yellow; */
}
#chat_box .chat_input{
    width:100%;
    position:absolute;
    bottom:0;
    height:50px;
    
    border-top:1px solid rgba(0,0,0,0.5);
}
#chat_box .chat_input input{
    padding:10px;
    height:100%;
    width:calc(100% - 50px);
}
#chat_box .chat_input .icon-a{
    position:absolute;
    /* margin:10px; */
    padding:10px;
    background:rgba(65,159,217);
    color:#fff;
    border-radius:30px;
    bottom:5px;
    left:5px;
    transform:rotate(90deg);
    cursor:pointer;
}
#chat_box .chat_input .icon-a:hover{
    background:rgba(85,179,237);
}

#chat_box .chat_holder li{
    max-width:70%;
    font-size:14px;
    margin:5px;
    padding:5px 15px;;
    border-radius:5px;
    position:relative;
    background:rgb(0, 142, 255);
    color:white;
    clear:both;
}
#chat_box .chat_holder li.rec{
    position:relative;
    clear:both;
    float:left;
    background:white;
    color:black;
    margin-left:7px;
}
#chat_box .chat_holder li span{
    margin-right:10px;
    display:inline-block;
    font-size:12px;
    float:left;
    position:relative;
    top:8px;
    right:8px;
}
#chat_box .chat_holder li.rec span{
    float:right;
    margin-right:0;
    margin-left:10px;
    right:auto;
    left:8px;
}    

</style>
<script>
// select chat event listener
document.getElementById('pp_holder').addEventListener('click',(e)=>{if(e.target.tagName == 'LI')select_chat(e.target);});
document.getElementById('chat_box').getElementsByTagName('input')[0].addEventListener('keydown',(e)=>{if(e.keyCode == 13)send_pm();});

var check_interval;
var interv = 2000;
var interv_count = 0;
var chat_interval = setInterval(() => {
    chats_lists();
}, 10000);
function select_chat(item){
    chid = item.id.substring(2);
    top_bar = document.getElementById('top_bar');
    top_bar.name = chid;
    top_bar.innerHTML = item.innerText;
    if(item.parentNode.getElementsByClassName('selected').length)
    item.parentNode.getElementsByClassName('selected')[0].classList.remove('selected');
    item.classList.add('selected');
    // clear chat area 
    document.getElementById('chat_holder').innerHTML = "";
    // show chats
    read_chat();
}
function send_pm(){
    item = document.getElementById('chat_box').getElementsByTagName('input')[0];
    chid = document.getElementById('top_bar').name;
    data = "new="+item.value+"&chid="+chid;
    var xmlHR = new XMLHttpRequest();
    url = base_url+"modules/chat/send_pm.php";
    xmlHR.open("POST", url, true);
    xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHR.send(data);
    
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
    chats_lists();
    return;
}
function read_chat(){
    chid = document.getElementById('top_bar').name;
    
    chat_holder = document.getElementById('chat_holder');
    if(chat_holder.children.length)
    last_read = chat_holder.children[chat_holder.children.length-1].id;
    else last_read = 0;
    data = 'lr='+last_read+"&chid="+chid;
    var xmlHR = new XMLHttpRequest();
    url = base_url+"modules/chat/read_pm.php";
    xmlHR.open("POST", url, true);
    xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHR.send(data);
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
                                }, interv*3);
                            }else{
                                clearInterval(check_interval);
                                check_interval = setInterval(() => {
                                    read_chat();
                                }, interv*6);
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
function reset_interv(){
    clearInterval(check_interval);
    interv_count = 0;
    check_interval = setInterval(() => {
        read_chat();
    }, interv);
}
function chats_lists(){
    cr = document.getElementById('top_bar').name;
    var xmlHR = new XMLHttpRequest();
    url = base_url+"modules/chat/chat_list.php";
    xmlHR.open("POST", url, true);
    xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHR.send('cl=0&cr='+cr);
    xmlHR.addEventListener('readystatechange',
        function(e){
            if (xmlHR.readyState == 4 && xmlHR.status == 200) {
                data = xmlHR.responseText;
                switch(data[0]){
                    case 'D':
                        document.getElementById('pp_holder').innerHTML = data.substring(1);
                }
            }
        }
        
    )
}
</script>