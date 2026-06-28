<footer>
    <div class="content">
        <div>
            <ul class="link">
            <?php
                $table = "pages";
                $st = "SELECT name,title FROM $table WHERE del_flag = 0 ORDER BY id ASC";
                $st = $mysqli->prepare($st);
                if(!$st->execute()){
                    echo "E";
                    exit;
                }
                $res = $st->get_result();
                while($row = $res->fetch_assoc()){
                    $name = $row["name"];
                    $title = $row["title"];
            ?>
                <a href="<?php echo $s."pages/$name.php"; ?>">
                    <li>
                        <?php echo $title; ?>
                    </li>
                </a>
            <?php
                }
            ?>
            </ul>
        </div>
        <div>
            <ul>
                <li>
            ارتباط با ادمین از طریق:</li>
            <?php
                $table = "contact_info";
                $st = "SELECT name,value,link FROM $table WHERE del_flag = 0 AND footer_ac = 1 ORDER BY id ASC";
                $st = $mysqli->prepare($st);
                if(!$st->execute()){
                    echo "E";
                    exit;
                }
                $res = $st->get_result();
                while($row = $res->fetch_assoc()){
            ?>
                <li>
                    <?php if($row["link"]){ ?>
                    <a href="<?php  echo $row["link"]; ?>">
                    <?php } ?>
                    <?php if($row["name"] == "واتسآپ"){ ?>
                        <span class="icon-whatsapp" style="font-size: 30px;background-color: #00E676;border-radius: 100%;line-height: 12px;"></span>
                    <?php } ?>
                    <?php if($row["name"] == "تلگرام"){ ?>
                        <span class="icon-telegram" style="font-size: 27px;color: #35ACE1;border-radius: 100%;line-height: 12px;background-color: white;border: 3px solid white;"></span>
                    <?php } ?>
                    <?php echo $row["name"].": ".$row["value"]; ?>
                    
                    <?php if($row["link"]){ ?>
                    </a>
                    <?php } ?>
                </li>
            <?php
                }
            ?>
            </ul>
            <ul>
            <?php
                $table = "contact_info";
                $st = "SELECT name,value,link FROM $table WHERE del_flag = 0 AND footer_sf = 1 ORDER BY id ASC";
                $st = $mysqli->prepare($st);
                if(!$st->execute()){
                    echo "E";
                    exit;
                }
                $res = $st->get_result();
                while($row = $res->fetch_assoc()){
            ?>
                <li>
                    <?php if($row["link"]){ ?>
                    <a href="<?php  echo $row["link"]; ?>">
                    <?php } ?>
                    
                    <?php echo $row["name"].": ".$row["value"]; ?>
                    
                    <?php if($row["link"]){ ?>
                    </a>
                    <?php } ?>
                </li>
            <?php
                }
            ?>
            </ul>
        </div>
        <?php if(1==1){ ?>
        <div id="enamad_holder">
            <div style="display:block;float:right;" >
                
            </div>
            
            <a referrerpolicy='origin' target='_blank' href='https://trustseal.enamad.ir/?id=414621&Code=7fZMDmGQzLkGn3AZSqmgFN7SRv33PsMx'><img referrerpolicy='origin' src='https://trustseal.enamad.ir/logo.aspx?id=414621&Code=7fZMDmGQzLkGn3AZSqmgFN7SRv33PsMx' alt='' style='cursor:pointer' Code='7fZMDmGQzLkGn3AZSqmgFN7SRv33PsMx'></a>
            
            </div>
        </div>
        <?php } ?>
    </div>
</footer>
<style>
    #enamad_holder a{display:block;min-width:30px;}
</style>
<?php
// if(isset($_SESSION['logged']) && in_array($_SESSION['logged']->uid,['4228','1039','1045'])){
    include $bu."modules/main/chat.php";
// }

?>
<script async src="<?php echo asset_url('js/main.js'); ?>" type="text/javascript"></script>
<script async src="<?php echo asset_url('js/cart.js'); ?>" type="text/javascript"></script>
