# 🚀 راهنمای آماده‌سازی برای تولید (Production)

## 📋 فایل‌های تست که باید حذف شوند

قبل از استقرار در محیط تولید، فایل‌های زیر را حذف کنید:

### **فایل‌های تست اصلی:**
```
admin/pinket/test-dashboard.php
admin/pinket/test-config.php
admin/pinket/test-api.php
admin/pinket/test-database.php
admin/pinket/test-logging.php
admin/pinket/test-api-connection.php
admin/pinket/PRODUCTION_CLEANUP.md
```

### **فایل‌های مستندات تست:**
```
admin/pinket/README.md
admin/pinket/CHANGELOG.md
```

## 🔧 تغییرات لازم در فایل‌های اصلی

### **1. حذف لینک تست از `ls_main.php`**

در فایل `admin/pinket/ls_main.php`، این خط را حذف کنید:

```php
// حذف این خط:
<a href="test-dashboard.php" style="float: left; background: #28a745; color: white; padding: 5px 15px; text-decoration: none; border-radius: 5px; font-size: 14px;">🧪 تست سیستم</a>
```

### **2. بررسی تنظیمات تولید در `pinket-config.php`**

مطمئن شوید که تنظیمات زیر برای محیط تولید درست هستند:

```php
// تنظیمات تولید
'baseUrl' => 'https://api.pinket.com', // URL اصلی API
'logLevel' => 'ERROR', // فقط خطاها را لاگ کنید
'debugMode' => false, // حالت دیباگ غیرفعال
```

## ✅ چک‌لیست نهایی

قبل از استقرار، موارد زیر را بررسی کنید:

- [ ] تمام فایل‌های تست حذف شده‌اند
- [ ] لینک تست از رابط اصلی حذف شده است
- [ ] تنظیمات API برای محیط تولید درست هستند
- [ ] لاگ‌ها فقط خطاها را ثبت می‌کنند
- [ ] حالت دیباگ غیرفعال است
- [ ] تمام endpoint های API کار می‌کنند
- [ ] اتصال دیتابیس پایدار است

## 🛡️ امنیت

### **تنظیمات امنیتی:**
1. مطمئن شوید که فایل‌های لاگ قابل دسترسی از وب نیستند
2. توکن‌های API را در جای امنی نگهداری کنید
3. دسترسی به فایل‌های تنظیمات را محدود کنید

### **فایل‌های حساس:**
```
admin/pinket/pinket-config.php (تنظیمات)
admin/pinket/logs/ (پوشه لاگ‌ها)
```

## 📞 پشتیبانی

در صورت بروز مشکل در محیط تولید:
1. لاگ‌ها را بررسی کنید: `admin/pinket/logs/pinket.log`
2. تنظیمات API را کنترل کنید
3. اتصال دیتابیس را بررسی کنید

---

**⚠️ مهم:** این فایل را قبل از استقرار در تولید حذف کنید! 