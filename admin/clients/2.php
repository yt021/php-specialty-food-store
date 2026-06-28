<?php
    if(isset($indexed)){
        if($indexed == 1){?>
    <form class="info" action="<?php echo $URL; ?>" method="post">
        <div class="ssrv">
            <div class="ssrv_title">آدرس</div>        
            <div class="ssrv_dtl">
                <div class="form fr">
<?php
    if($_SESSION[$cf]->sub_id == "new"){
        $fields = ["county","city","address","post_code","rec_name","rec_tel","rec_tel_2","janitor"];
        foreach($fields as $f){
            $data["address"][$f]="";
        }
    }else{
        $address = db_get_address($_SESSION[$cf]->sub_id);
        $data["address"] = $address->data();
    }
?>
                    <div class="form_item fr date">
                        <div class="select fr w200">
                            <label for="county" class="fr select">استان</label>
                            <select required class="counties" onchange="get_cities_new(this[this.selectedIndex].value);" name="county">
                            <?php
                                if(isset($data["address"]["county"]) && $data["address"]["county"]){}else{
                            ?>
                                <option value="">انتخاب استان</option>
                            <?php
                                }
                            ?>
                            <?php
                                $st = "SELECT county FROM cities WHERE del_flag = 0";
                                $st = $mysqli->prepare($st);
                                if(!$st->execute()){
                                    echo "E";
                                    exit;
                                }
                                $res = $st->get_result();
                                if($res->num_rows >= 1){
                                    $k = 0;
                                    while($row = $res->fetch_assoc())
                                    {
                                        $county = $row["county"];
                                        $selected="";
                                        if(isset($data["address"]["county"]) && $data["address"]["county"] == $county)$selected = " selected ";
                            ?>
                            <option value="<?php echo $county; ?>" <?php echo $selected; ?>>
                                <?php echo $county; ?>
                            </option>
                            <?php
                                    }
                                }
                            ?>
                            </select>
                        </div>
                        <div class="select fr w200">
                            <label for="city" class="fr tac select date">شهر</label>
                            <select required class="cities_select fr" name="city">
                            <?php
                                if(isset($data["address"]["city"]) && $data["address"]["city"]){}else{
                            ?>
                                <option value="">انتخاب شهر</option>
                            <?php
                                }
                            ?>
                            <?php
                                if(isset($data["address"]["county"]) && $data["address"]["county"]){
                                    $cities = explode(',',getVarFromDB('cities','cities_str','county',$data["address"]["county"]));
                                    foreach($cities as $city){
                                        $selected = "";
                                        if(isset($data["address"]["city"]) && $data["address"]["city"] == $city)$selected = " selected ";
                            ?>
                                <option value="<?php echo $city; ?>" <?php echo $selected; ?>>
                                    <?php echo $city; ?>
                                </option>
                            <?php
                                    }
                                }
                            ?>
                            </select>
                        </div>
                    </div>
                    <div class="cb"></div>
                    <div class="form_item">
                        <label>آدرس:</label>
                        <input type="text" name="address" required <?php if(isset($data["address"]["address"]))echo ' value="'.$data["address"]["address"].'" '; ?>>
                    </div>
                    <div class="form_item">
                        <label for="post_code">کد پستی:</label>
                        <input type="text" name="post_code" <?php if(isset($data["address"]["post_code"]))echo ' value="'.$data["address"]["post_code"].'" '; ?>>
                    </div>
                    <div class="form_item">
                        <label for="rec_name">نام گیرنده:</label>
                        <input type="text" name="rec_name"  <?php if(isset($data["address"]["rec_name"]))echo ' value="'.$data["address"]["rec_name"].'" '; ?>>
                    </div>
                    <div class="form_item">
                        <label for="rec_tel">تلفن گیرنده:</label>
                        <input type="text" name="rec_tel" <?php if(isset($data["address"]["rec_tel"]))echo ' value="'.$data["address"]["rec_tel"].'" '; ?>>
                    </div>
                    <div class="form_item">
                        <label for="rec_tel_2">تلفن ثابت:</label>
                        <input type="text" name="rec_tel_2" <?php if(isset($data["address"]["rec_tel_2"]))echo ' value="'.$data["address"]["rec_tel_2"].'" '; ?>>
                    </div>
                    
                </div>
            </div>
        </div>
        <input type="submit" name="edit" class="btn" value="ثبت تغییر">
    </form>
            
                        
<script type="text/javascript">
function get_cities_new(county){
    post_data = "&county="+county;
    var xmlHR = new XMLHttpRequest();
    url = base_url+"modules/cart/cities_dd_new.php";
    xmlHR.open("POST", url, false);
    xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHR.send(post_data);
    data = xmlHR.responseText;
    options = data.split(",");
    select_inner = '<option value="">انتخاب شهر</option>';
    for(i=0;i<options.length-1;i++){
        select_inner += '<option value="'+options[i]+'">'+options[i]+'</option>';
    }
    cities = document.getElementsByClassName('cities_select')[0];
    cities.innerHTML = select_inner;
    return;
}
function get_cities(item){
    pp = item.parentNode.parentNode.parentNode;
    post_data = "&county="+item.innerText;
    var xmlHR = new XMLHttpRequest();
    url = base_url+"modules/cart/cities_dd.php";
    xmlHR.open("POST", url, false);
    xmlHR.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlHR.send(post_data);
    data = xmlHR.responseText;
    ul = pp.getElementsByClassName("cities_dd")[0];
    ul.classList.remove("disabled");
    ul.innerHTML = data;
    ready_selects(ul.parentNode);
    return;
}
</script>
<script src="<?php echo $s; ?>js/selection.js"></script>
<script src="<?php echo $s; ?>js/cities.js"></script>
<?php
        }
    }
?>