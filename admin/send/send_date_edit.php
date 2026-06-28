<?php
    if(isset($indexed)){
        if($indexed == 1){?>
<?php
    $tb = $_SESSION[$cf]->state;
    if($_SESSION[$cf]->level == 1){
        $year = (int)$_SESSION[$cf]->sql_where["date"]["year"];
        $month = (int)$_SESSION[$cf]->sql_where["date"]["month"];
        $month_end = monthNoDaysJ($month,$year);
        if(sizeof($_POST['day']) == $month_end){
            foreach($_POST['day'] as $day=>$state){
                $sql_time = createSQLTime($year,$month,$day,12);
                if(dayoWeekJ_date($year,$month,$day) == 6){
                    $state = "official";    
                }
                if(var_exist($sql_time,$tb,"date")){
                    updateInDB($tb,"state",$state,"date",$sql_time);
                }else{
                    $st = "INSERT INTO $tb (date,state) VALUES (?,?)";
                    $st = $mysqli->prepare($st);
                    $st->bind_param('ss',$sql_time,$state);
                    $st->execute();
                }
            }
        }
    }
?>
<?php
        }
    }
?>
