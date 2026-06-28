<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php

    $id = $_SESSION[$cf]->id;
    if($id == "new"){
        $row = array(
            "name"=>NULL,
            "start_hour"=>NULL,
            "end_hour"=>NULL,
            "price"=>NULL,
            "transporter_id"=>NULL,
            
        );
    }else{
    $st = "SELECT name,transporter_id,start_hour,end_hour,price FROM $tb WHERE id = $id";
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    $row = $res->fetch_assoc();
    }
?>
<form action="<?php echo $URL ?>" method="post">
        <div class="ssrv">
            <div class="ssrv_title">مشخصات</div>        
            <div class="ssrv_dtl">
                <div class="form fr">
                    <div class="form_item ">
                        <label>نام: </label>
                        <input name="name" type="text" value="<?php echo $row["name"]; ?>">
                    </div>
                    <div class="form_item ">
                        <label>ساعت شروع: </label>
                        <input name="start_hour" type="text" value="<?php echo $row["start_hour"]; ?>">
                    </div>
                    <div class="form_item ">
                        <label>ساعت پایان: </label>
                        <input name="end_hour" type="text" value="<?php echo $row["end_hour"]; ?>">
                    </div>
                    <div class="form_item ">
                        <label>هزینه: </label>
                        <input name="price" type="text" value="<?php echo $row["price"]; ?>">
                    </div>
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
    while($row = $res->fetch_assoc())
    {
        $name = $row["name"];
        $id = $row["id"];
        $selected = "";
        if($tp_id == $id)$selected=" selected ";
?>
                            <option <?php echo $selected; ?> value="<?php echo $id; ?>"><?php echo $name; ?></option>
<?php
    }
?>
                        </select>
                    </div>
                </div>
            </div>
        </div>        
        <input type="submit" name="edit" class="btn" value="ثبت">
    </form>
<?php
        }
    }
?>