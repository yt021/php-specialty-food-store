<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $id = $_SESSION[$cf]->id;
    if($id == "new"){
        $row = array(
            "transporter_id"=>null,
            "static_rows"=>1
        );
    }else{
        $st = "SELECT transporter_id,static_rows,n_columns FROM $tb WHERE id = $id";
        $st = $mysqli->prepare($st);
        if(!$st->execute()){
            echo "E";
            exit;
        }
        $res = $st->get_result();
        $row = $res->fetch_assoc();
    }
?>
        <form action="<?php echo $URL; ?>" method="post" enctype="multipart/form-data">
            <div class="ssrv">
                <div class="ssrv_title">مشخصات</div>        
                <div class="ssrv_dtl">
                    <div class="form fr">
                        <div class="form_item ">
                            <label>ارسال‌کننده: </label>
                            <select name="transporter_id">
            
<?php
    $tp_id = $row["transporter_id"];
    if(!$tp_id){
        $tp_id = getVarFromDB("sd_setting","value","flag","tp_id");
    }

    $st = "SELECT id,name FROM transporters
    WHERE del_flag = 0";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row2 = $res->fetch_assoc())
    {
        $name = $row2["name"];
        $t_id = $row2["id"];
        $selected = "";
        if($tp_id == $id)$selected=" selected ";
?>
                                <option <?php echo $selected; ?> value="<?php echo $t_id; ?>"><?php echo $name; ?></option>
<?php
    }
?>
                            </select>
                        </div>
                        <div class="form_item ">
                            <label>تعداد ردیف ثابت: </label>
                            <input name="static_rows" type="text" value="<?php echo $row["static_rows"]; ?>">
                        </div>
<?php
    if($id != "new"){
?>
                        <div class="form_item ">
                            <label>تعداد ستون پرونده: </label>
                            <input disabled type="text" value="<?php echo $row["n_columns"]; ?>">
                        </div>
<?php
    }
?>
                    </div>
                </div>
            </div>
<?php
    if($id == "new"){
?>
            <div class="ssrv">
                <div class="ssrv_title">نمونه پرونده لیست ارسال</div>        
                <div class="ssrv_dtl">
                    <form action="<?php echo $URL; ?>" method="post" enctype="multipart/form-data">
                        <div class="upload w300 fr">
                            <input required type="file" name="file">
                            <div class="gallery"></div>
                            <div class="cb"></div>
                            <label class="btn">فایل‌ را انتخاب کنید یا در این قسمت رها کنید .</label>
                        </div>
                        <div class="form fr has_upload">
                        </div>
                </div>
            </div>
            <script src="<?php echo $s; ?>js/file_upload.js"></script>
<?php
    }
?>
            <input type="submit" name="edit" class="btn" value="ثبت">
        </form>
<?php
    if($_SESSION[$cf]->id != "new"){
?>
        </div>  
    </div>
    <div class="cut w100p"></div>
    <div id="sect3" class="sect ">
        <div class="middle container">
            <a class="btn" onclick="sub_show('change','variable_data')">تنظیم محتوای متغیر (ستون‌ها)</a>
        </div>  
    </div>
    <div class="cut w100p"></div>
    <div id="sect3" class="sect ">
        <div class="middle container">
        <h4 class="tac">محتوای ثابت پرونده نمونه لیست ارسال</h4>
        <table class="tracking">
            <thead>
                <tr>
                    <th></th>
<?php
    $alphas = range('A', 'Z');
    for($i=0;$i<(int)$row["n_columns"];$i++){
        echo "<th>".$alphas[$i]."</th>";
    }
?>
                </tr>
            </thead>
<style type="text/css">
    th{
        background-color:#D63650;
    }
</style>
            <tbody>
<?php
    $st = "SELECT row_n,row_to_str FROM send_list_excels_static_rows WHERE sleid = $id ORDER BY row_n ASC";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    while($row2 = $res->fetch_assoc()){
        echo "<tr>";
        echo "<th>".($row2["row_n"]+1)."</th>";
        $row_items = explode(",",$row2["row_to_str"]);
        foreach($row_items as $item){
            echo "<td>$item</td>";
        }
        echo "</tr>";
    }
?>
            </tbody>
        </table>
<?php
    }
?>
<?php
        }
    }
?>
