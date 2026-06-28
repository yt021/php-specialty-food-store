<?php
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Cache-Control: max-age=0');

    $indexed = 1;
    include($_SERVER['DOCUMENT_ROOT']."/base_shop.php");
    include $bu."modules/admin/session_start.php";

    include_once($bu."modules/xlsxwriter.class.php");
    
?>
<?php
    
    
    $tb = "users";
    $st = "SELECT 
        users.id,
        users.name,
        users.tel,
        users.last_login,
        addresses.county,
        addresses.city
        FROM users 
        RIGHT JOIN addresses ON users.id = addresses.uid GROUP BY users.tel
        ";
    // echo $st;
    $st = $mysqli->prepare($st);
    if(!$st->execute()){
        echo "E";
        exit;
    }
    $res = $st->get_result();
    
    $file_name = "تاریخ ".correctDate_ts(time());
    
    $excel_rows = array();
    
    $excel_rows[0] = array(
        'موبایل',
        'نام و نام خانوادگی',
        'استان',
        'شهر',
        'آخرین مراجعه',
        'مرتبه خرید',
        'مجموع مبلغ خرید'
    );
    
    
    $k = 1;
    while($row = $res->fetch_assoc())
    {
        // if($row['id']){
        //     $st = 'SELECT count(orders.cart_pure) as no,sum(orders.cart_pure) as su FROM orders WHERE uid = '.$row['id'].' AND state>0 AND del_flag = 0';
        //     $st_res = return_sel_sql($st)->fetch_assoc();
        //     $row['orders_no'] = $st_res['no'];
        //     $row['orders_sum'] = $st_res['su'] ;
        // }else{
            $row['orders_no'] = 0;
            $row['orders_sum'] = 0;
        // }
        
        $row['last_login'] = correctDate($row['last_login']);
        
        $new_excel_row = array(
                        $row['tel'],
                        $row['name'],
                        $row['county'],
                        $row['city'],
                        $row['last_login'],
                        $row['orders_no'],
                        $row['orders_sum']
                        
                        
        );
        
        
        
        $excel_rows[$k] = $new_excel_row;
        $k++;
    }
    header('Content-Disposition: attachment;filename="لیست مشتریان '.$file_name.'.xlsx"');
    $writer = new XLSXWriter();
    $writer->setRightToLeft(true);
    $writer->writeSheet($excel_rows);
    $writer->writeToStdOut();
?>
