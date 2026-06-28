<?php
if(isset($indexed)){
    if($indexed == 1){
        if(isset($_POST["period_option"])){
            $_SESSION[$cf]->sql_where["period_option"] = $_POST["period_option"];
            
            $_SESSION[$cf]->sql_where["end_date"] = CaSDate(time()+24*3600);
            switch($_POST["period_option"]){
                case "period":
                    if(isset($_POST["date_period"])){
                        foreach($_POST["date_period"] as $dp_name=>$dp_date)
                        {
                            $_SESSION[$cf]->sql_where["$dp_name"."_date"] = $dp_date;
                        }
                        break;
                    }
                case "last_month":
                    $_SESSION[$cf]->sql_where["start_date"] = $_SESSION[$cf]->sql_where["end_date"];
                    $_SESSION[$cf]->sql_where["start_date"]["day"] = 1;
                    break;    
                case "today":
                    $_SESSION[$cf]->sql_where["start_date"] =CaSDate(time());
                    break;
                case "last_year":
                    $_SESSION[$cf]->sql_where["start_date"] = $_SESSION[$cf]->sql_where["end_date"];
                    $_SESSION[$cf]->sql_where["start_date"]["day"] = 1;
                    $_SESSION[$cf]->sql_where["start_date"]["month"] = 1;
                    break;
                default:
                    switch($_POST["period_option"]){
                        case "last_30_days":
                        $dm = 1;break;
                        case "last_90_days":
                        $dm = 3;break;
                        case "last_6_month":
                        $dm = 6;break;
                    }
                    $_SESSION[$cf]->sql_where["start_date"] = $_SESSION[$cf]->sql_where["end_date"];
                    if($_SESSION[$cf]->sql_where["start_date"]["month"] < $dm+1){
                        $_SESSION[$cf]->sql_where["start_date"]["year"]--;
                    }
                    echo $dm;
                    $_SESSION[$cf]->sql_where["start_date"]["month"] = ($_SESSION[$cf]->sql_where["start_date"]["month"] + 12 - $dm)%12;
                    break;
            }
        }
        if(!isset($_SESSION[$cf]->sql_where["period_option"])){
            // $_SESSION[$cf]->sql_where["period_option"] = "last_month";  // ماه جاری
            // $_SESSION[$cf]->sql_where["end_date"] = CaSDate(time());
            // $_SESSION[$cf]->sql_where["start_date"] = $_SESSION[$cf]->sql_where["end_date"];
            // $_SESSION[$cf]->sql_where["start_date"]["day"] = 1;
            
            
            $_SESSION[$cf]->sql_where["period_option"] = "today";  // ماه جاری
            $_SESSION[$cf]->sql_where["end_date"] = CaSDate(time()+24*3600);
            $_SESSION[$cf]->sql_where["start_date"] = CaSDate(time());
        }
    }
}
?>