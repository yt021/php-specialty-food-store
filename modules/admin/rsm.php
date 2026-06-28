<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<div id="cover" class="hide" onclick="close_rsm()"></div>
<div id="header">
    <div class="open_rsm_btn" onclick="open_rsm()">
        <span class="icon-menu"></span>
    </div>
    <div id="rsm" class="rsm_hide">
        <ul id="rsm_ul" class="">
            <a href = "<?php echo $s; ?>">
                <li class="rsm_li_l1">
                    مشاهده صفحه اصلی تارنما
                </li>
            </a>
            <a href = "<?php echo $s."admin/"; ?>">
                <li class="rsm_li_l1">
                    داشبورد
                </li>
            </a>
            <?php
                $u_level = $_SESSION["a_logged"]->get_level();
                $st = "SELECT flag,name FROM admin_modules ORDER BY id ASC";
                $st = $mysqli->prepare($st);
                if(!$st->execute()){
                    echo "E";
                    exit;
                }
                $res = $st->get_result();
                $k = 0;
                while($row = $res->fetch_assoc())
                {
                    if($_SESSION["a_logged"]->check_access($row["flag"])){
            ?>
                    <li class="rsm_li_l1 curpo" onclick="show_admin_service('<?php echo $row["flag"] ?>')">
                        <?php
                            echo $row["name"];
                        ?>
                    </li> 
                    
                    
            <?php
                    }
                }
            ?>
            <li class="rsm_li_l1 curpo" onclick="show_admin_service('logout.php')">
                خروج
            </li>
        </ul>
    </div>
</div>

<script type="text/javascript">

//rsm functions

function open_menu_dd(item){
    close_menu_dd();
    item.classList.add('active');
    if(item.getElementsByClassName('dd')[0]){
        item_btn = item.getElementsByClassName('dd')[0];
        item_btn.classList.remove('dd');
        item_btn.classList.add('cls');
        if(item.getElementsByTagName('ul')[0]){
            item.getElementsByTagName('ul')[0].classList.remove('hide');
        }
        var onclick = 'close_menu_dd()';
        item.setAttribute('onclick',onclick);
    }
}
function close_menu_dd(){
    items = document.getElementsByClassName('rsm_li_l1');
    for (i=0;i<items.length;i++){
        item = items[i];
        item.classList.remove('active');
        if(item.getElementsByTagName('ul')[0]){
            item.getElementsByTagName('ul')[0].classList.add('hide');
            if(item.getElementsByClassName('cls')[0]){
                item_btn = item.getElementsByClassName('cls')[0];
                item_btn.classList.remove('cls');
                item_btn.classList.add('dd');
            }
            var onclick = 'open_menu_dd(this)';
            item.setAttribute('onclick',onclick);
        }
    }
}

function sub_show(key,value,address="",n_tab=""){
    var form = document.createElement("form");
    var input = document.createElement("input");
    
    form.method = "POST";
    form.action = "<?php echo $URL; ?>";
    if(address != ""){
        form.action = "<?php echo $s."admin/"; ?>"+address;
    }
    if(n_tab != ""){
        form.target = "_blank";
    }
    input.value = value;
    input.name = key;
    form.appendChild(input);
    
    document.body.appendChild(form);
    form.submit();
}

function show_admin_service(flag){
    var form = document.createElement("form");
    var input = document.createElement("input");
    form.method = "POST";
    form.action = "<?php echo $s.$module_name; ?>/"+flag+"/";
    input.value = '0';
    input.name = 'clear';
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}
function open_rsm(){
    document.getElementById('rsm').classList.remove('rsm_hide');
    document.getElementById('cover').classList.remove('hide');
    window.setTimeout(function(){document.getElementById('rsm_ul').classList.remove('hide');},250)
}
function close_rsm(){
    document.getElementById('rsm').classList.add('rsm_hide');
    document.getElementById('cover').classList.add('hide');
    document.getElementById('rsm_ul').classList.add('hide');
}
</script>
<?php
        }
    }
?>
