<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    if(isset($_SESSION["address"]))$data["address"] = $_SESSION["address"]->data();
?>
                <div class="form_item fr">
                    <label for="county" class="fr select">استان</label>
                    <div class="select fr">
                    <select class="counties" onchange="change_select_input(this,this[this.selectedIndex].value);">
                        <option value=""><?php if(isset($data["address"]["county"]))echo $data["address"]["county"];else echo "انتخاب استان"; ?></option>
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
                        <option value="<?php echo $county; ?>">
                            <?php echo $county; ?>
                        </option>
                        <?php
                                }
                            }
                        ?>
                    </select>
                        <input type="text" name="county" <?php if(isset($data["address"]["county"]))echo ' value="'.$data["address"]["county"].'" '; ?> class="hide" >
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
                        <select class="cities_select" onchange="change_select_input(this,this[this.selectedIndex].value);">
                            <option value=""><?php if(isset($data["address"]["city"]))echo $data["address"]["city"];else echo "انتخاب شهر"; ?></option>
                        </select>
                        <input type="text" name="city" <?php if(isset($data["address"]["city"]))echo ' value="'.$data["address"]["city"].'" '; ?> class="hide">
                        <ul class="select_dd cities_dd disabled">
                            <li class="hide place_holder">
                                <?php if(isset($data["address"]["city"]))echo $data["address"]["city"];else echo "انتخاب شهر"; ?>
                            </li>
                            <li class="dd_option show"></li>
                        </ul>
                    </div>
                </div>
                <div class="form_item fr address">
                    <label for="address">آدرس</label>
                    <input type="text" name="address" required <?php if(isset($data["address"]["address"]))echo ' value="'.$data["address"]["address"].'" '; ?>  placeholder="آدرس کامل را وارد کنید">
                </div>
                <div class="form_item fr post_code">
                    <label for="post_code">کد پستی</label>
                    <input type="text" name="post_code" id="post_code" <?php if(isset($data["address"]["post_code"]))echo ' value="'.$data["address"]["post_code"].'" '; ?> placeholder="کد پستی">
                </div>
                <div class="form_item fr">
                    <label for="name">نام گیرنده</label>
                    <input type="text" name="rec_name"  placeholder="نام و نام خانوادگی" <?php if(isset($data["address"]["rec_name"]))echo ' value="'.$data["address"]["rec_name"].'" '; ?>>
                </div>
                <div class="form_item fr">
                    <label for="tel">تلفن گیرنده</label>
                    <input type="text" name="rec_tel" <?php if(isset($data["address"]["rec_tel"]))echo ' value="'.$data["address"]["rec_tel"].'" '; ?>  placeholder="*********09">
                </div>
                <div class="form_item fr">
                    <label for="tel">تلفن ثابت</label>
                    <input type="text" name="rec_tel_2" <?php if(isset($data["address"]["rec_tel_2"]))echo ' value="'.$data["address"]["rec_tel_2"].'" '; ?>  placeholder="">
                </div>
                <div class="form_item fr">
                    <div class="checkbox fr <?php if(isset($data["address"]["janitor"]) && $data["address"]["janitor"] == "yes")echo '  icon-chkfl';else echo "icon-chk"; ?>" onclick="check_box(this)"></div>
                    <input type="checkbox" name="janitor" <?php if(isset($data["address"]["janitor"]) && $data["address"]["janitor"] == "yes")echo 'checked'; ?> value="yes" class="hide">
                    در صورت عدم حضور تحویل نگهبان/سرایدار شود.
                </div>
<?php
        }
    }
?>                
