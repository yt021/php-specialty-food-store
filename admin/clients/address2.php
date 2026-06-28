<?php
    if(isset($indexed)){
        if($indexed == 1){?>
    <div class="ssrv">
                <div class="ssrv_title">آدرس</div>        
                <div class="ssrv_dtl">
                    <form class="info" action="<?php echo $URL; ?>" method="post">
                        <div class="form fr">
<?php
    $address = db_get_address($_SESSION[$cf]->sub_id);
    $data["address"] = $address->data();
?>
                            <div class="form_item fr">
                                <label for="county" class="fr select">استان</label>
                                <div class="select fr">
                                    <input type="text" required name="county" <?php if(isset($data["address"]["county"]))echo ' value="'.$data["address"]["county"].'" '; ?> class="hide" >
                                    <ul class="select_dd counties_dd">
                                        <li class="hide place_holder">
                                            <?php if(isset($data["address"]["county"]))echo $data["address"]["county"];else echo "انتخاب استان"; ?>
                                        </li>
                                        <li class="dd_option show">
                                            انتخاب استان
                                        </li>
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

                                        ?>
                                        <li class="dd_option">
                                            <?php echo $county; ?>
                                        </li>
                                        <?php
                                                }
                                            }
                                        ?>
                                    </ul>
                                </div>
                                <label for="city" class="fr tac select">شهر</label>
                                <div class="select fr">
                                    <input type="text" required name="city" <?php if(isset($data["address"]["city"]))echo ' value="'.$data["address"]["city"].'" '; ?> class="hide">
                                    <ul class="select_dd cities_dd disabled">
                                        <?php
                                            include $bu."modules/cart/cities_dd.php";
                                        ?>
                                    </ul>
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
                                <label for="post_code">نام گیرنده:</label>
                                <input type="text" name="rec_name"  <?php if(isset($data["address"]["rec_name"]))echo ' value="'.$data["address"]["rec_name"].'" '; ?>>
                            </div>
                            <div class="form_item">
                                <label for="post_code">تلفن گیرنده:</label>
                                <input type="text" name="rec_tel" <?php if(isset($data["address"]["rec_tel"]))echo ' value="'.$data["address"]["rec_tel"].'" '; ?>>
                            </div>
                        </div>
                        
                </div>
            </div>
        <input type="submit" name="edit" class="btn" value="ثبت تغییر">
    </form>
            
                        
<script type="text/javascript">
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
//    if(item.innerText == "شهر تهران" || item.innerText == "استان تهران"){
//        document.getElementById("post_code").required = false;
//    }else{
//        document.getElementById("post_code").required = true;
//    }
    return;
}
</script>
<script src="<?php echo $s; ?>js/selection.js"></script>
<script src="<?php echo $s; ?>js/cities.js"></script>
<?php
        }
    }
?>