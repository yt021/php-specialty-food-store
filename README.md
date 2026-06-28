# فروشگاه آنلاین محصولات غذایی | Specialty Food Store

[فارسی](#فارسی) · [English](#english)

یک فروشگاه اینترنتی مبتنی بر PHP و MySQL برای نمایش محصولات غذایی، مدیریت
سبد خرید و سفارش‌ها، حساب مشتریان و عملیات مدیریتی فروشگاه.

A PHP and MySQL e-commerce application for specialty food products, customer
accounts, shopping carts, orders, and store administration.

> این مخزن نسخه عمومی و نمایشی پروژه است. اطلاعات واقعی و اتصال فعال به
> سرویس‌های پرداخت، پیامک، ایمیل و مارکت‌پلیس در آن وجود ندارد.
>
> This repository is a public showcase. It contains no real customer data or
> active payment, SMS, email, or marketplace connections.

---

## فارسی

### درباره پروژه

این پروژه بخش‌های اصلی یک فروشگاه اینترنتی را در قالب یک برنامه PHP سنتی
پیاده‌سازی می‌کند. ساختار برنامه شامل ویترین محصولات، فرایند خرید، حساب
کاربری مشتری و پنل مدیریت یکپارچه است.

### امکانات

- نمایش محصولات و فیلتر براساس دسته‌بندی
- صفحات جزئیات و انتخاب ویژگی‌های محصول
- سبد خرید و فرایند چندمرحله‌ای ثبت سفارش
- ثبت‌نام، ورود و مدیریت حساب مشتری
- تاریخچه سفارش‌ها و نمایش فاکتور
- صفحات محتوایی، مطالب، نظرات و پرسش‌های متداول
- مدیریت محصولات، دسته‌بندی‌ها، سفارش‌ها و مشتریان
- گزارش‌های فروش، فعالیت و اطلاعات جغرافیایی
- ابزارهای مدیریت ارسال، تولید، محتوا و صفحه اصلی
- معماری ماژولار برای سرویس‌های پرداخت، پیامک و فروش همکار

### فناوری‌ها

| بخش | فناوری |
| --- | --- |
| Backend | PHP 8+ |
| Database | MySQL و `mysqli` |
| Frontend | HTML، CSS و JavaScript |
| Web server | Apache یا PHP Development Server |
| Libraries | PHPMailer، NuSOAP، SimpleXLSX و XLSXWriter |

### ساختار پروژه

```text
account/      حساب کاربری مشتری
admin/        پنل مدیریت و عملیات فروشگاه
cart/         سبد خرید، ثبت سفارش، پرداخت و فاکتور
modules/      منطق مشترک، دیتابیس و سرویس‌ها
products/     صفحات و قالب‌های محصولات
pages/        صفحات ثابت
posts/        مطالب و محتوای سایت
comment/      نظرات مشتریان
css/          استایل‌های فروشگاه و پنل مدیریت
js/           اسکریپت‌های رابط کاربری
fonts/        فونت‌های محلی
img/          تصاویر رابط کاربری
```

### اجرای محلی

پیش‌نیازها:

- PHP 8.0 یا بالاتر
- MySQL
- افزونه‌های `mysqli`، `curl`، `json` و `mbstring`

ابتدا یک دیتابیس محلی ایجاد و متغیرهای اتصال را تنظیم کنید:

```powershell
$env:DB_HOST="127.0.0.1"
$env:DB_NAME="specialty_food_store"
$env:DB_USER="root"
$env:DB_PASSWORD=""
```

سپس از ریشه پروژه برنامه را اجرا کنید:

```powershell
php -S 127.0.0.1:8080
```

آدرس برنامه:

```text
http://127.0.0.1:8080
```

دیتابیس اصلی و اطلاعات کاربران در این مخزن قرار ندارند. برای نمایش کامل
فروشگاه باید از schema سازگار و داده‌های آزمایشی استفاده شود.

### نسخه نمایشی عمومی

در این نسخه سرویس‌های پرداخت، پیامک، ایمیل، ارسال، SnappPay و Pinket غیرفعال
هستند. تنظیمات موجود صرفاً مقادیر نمایشی و غیرقابل استفاده‌اند.

---

## English

### About

This project implements the primary parts of an online store as a traditional
PHP application. It combines a product storefront, purchasing workflow,
customer accounts, and an integrated administration panel.

### Features

- Product catalog and category filtering
- Product details and option selection
- Multi-step shopping-cart and order workflow
- Customer registration, authentication, and account management
- Order history and invoices
- Content pages, posts, comments, and FAQs
- Product, category, order, and customer administration
- Sales, activity, and geographic reports
- Shipping, production, content, and homepage management
- Modular payment, SMS, and marketplace service adapters

### Technology

| Area | Technology |
| --- | --- |
| Backend | PHP 8+ |
| Database | MySQL with `mysqli` |
| Frontend | HTML, CSS, and JavaScript |
| Web server | Apache or PHP's development server |
| Libraries | PHPMailer, NuSOAP, SimpleXLSX, and XLSXWriter |

### Project structure

```text
account/      Customer account features
admin/        Administration and store operations
cart/         Cart, checkout, payment, and invoices
modules/      Shared logic, database, and service adapters
products/     Product pages and templates
pages/        Static content pages
posts/        Articles and site content
comment/      Customer comments
css/          Storefront and administration styles
js/           Browser-side behavior
fonts/        Local fonts
img/          Interface assets
```

### Local development

Requirements:

- PHP 8.0+
- MySQL
- PHP extensions: `mysqli`, `curl`, `json`, and `mbstring`

Create a local database and configure the connection:

```powershell
$env:DB_HOST="127.0.0.1"
$env:DB_NAME="specialty_food_store"
$env:DB_USER="root"
$env:DB_PASSWORD=""
```

Start the application from the repository root:

```powershell
php -S 127.0.0.1:8080
```

Open:

```text
http://127.0.0.1:8080
```

The production database and customer records are not included. A compatible
local schema and synthetic data are required to demonstrate the complete
catalog and order workflow.

### Public showcase

Payment, SMS, email, delivery, SnappPay, and Pinket integrations are disabled
in this version. Committed configuration values are nonfunctional placeholders.

## Status

This repository is maintained as a portfolio and code-review showcase of a
legacy PHP commerce application.
