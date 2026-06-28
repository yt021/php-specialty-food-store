<?php
if(isset($indexed)){
    if($indexed == 1){
        
        header('Content-Type: application/json; charset=utf-8');
        
        // بررسی ورودی‌ها
        if(!isset($_POST['addressId']) || !isset($_POST['county']) || !isset($_POST['city'])) {
            echo json_encode(['success' => false, 'error' => 'پارامترهای ورودی ناقص است']);
            exit;
        }
        
        $addressId = (int)$_POST['addressId'];
        $county = trim($_POST['county']);
        $city = trim($_POST['city']);
        
        // بررسی اعتبار داده‌ها
        if($addressId <= 0 || empty($county) || empty($city)) {
            echo json_encode(['success' => false, 'error' => 'داده‌های ورودی نامعتبر است']);
            exit;
        }
        
        // بررسی وجود آدرس
        $stmt = $mysqli->prepare("SELECT id, uid, address FROM addresses WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $addressId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if(!$addressRow = $result->fetch_assoc()) {
            echo json_encode(['success' => false, 'error' => 'آدرس مورد نظر یافت نشد']);
            exit;
        }
        $stmt->close();
        
        // استفاده از کلاس address موجود برای اعتبارسنجی
        include_once $bu."modules/cart/cart_funcs.php";
        
        // ایجاد آرایه آدرس برای اعتبارسنجی
        $addressData = [
            'county' => $county,
            'city' => $city,
            'address' => $addressRow['address'], // استفاده از آدرس موجود
            'post_code' => '', // اختیاری
            'rec_name' => '', // اختیاری
            'rec_tel' => '', // اختیاری
            'rec_tel_2' => '', // اختیاری
            'janitor' => '' // اختیاری
        ];
        
        // اعتبارسنجی با استفاده از کلاس address موجود
        $address = new address($addressData);
        $addressInfo = $address->data();
        
        if($addressInfo['error'][0] > 0) {
            $errors = $addressInfo['error'][1];
            $errorMessages = [];
            
            if(isset($errors[0])) $errorMessages[] = 'استان نامعتبر';
            if(isset($errors[1])) $errorMessages[] = 'شهر نامعتبر';
            if(isset($errors[2])) $errorMessages[] = 'آدرس نامعتبر';
            
            echo json_encode(['success' => false, 'error' => 'داده‌های آدرس نامعتبر: ' . implode(', ', $errorMessages)]);
            exit;
        }
        
        // بروزرسانی آدرس
        $stmt = $mysqli->prepare("UPDATE addresses SET county = ?, city = ? WHERE id = ?");
        $stmt->bind_param("ssi", $county, $city, $addressId);
        
        if($stmt->execute()) {
            // ثبت لاگ
            $logMessage = "آدرس تصحیح شد: ID=$addressId, UID={$addressRow['uid']}, County=$county, City=$city";
            error_log("[ADDRESS_CORRECTION] " . $logMessage);
            
            echo json_encode([
                'success' => true, 
                'message' => 'آدرس با موفقیت بروزرسانی شد',
                'data' => [
                    'addressId' => $addressId,
                    'county' => $county,
                    'city' => $city,
                    'fullAddress' => $address->full_address()
                ]
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'خطا در بروزرسانی آدرس: ' . $mysqli->error]);
        }
        
        $stmt->close();
        
    }
}
?> 