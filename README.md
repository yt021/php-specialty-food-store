# فروشگاه آنلاین محصولات غذایی | Specialty Food Store

[فارسی](#فارسی) · [English](#english)

یک پروژه فروشگاه اینترنتی مبتنی بر PHP و MySQL شامل ویترین محصولات،
سبد خرید، حساب کاربری و پنل مدیریت. این مخزن نسخه‌ای پاک‌سازی‌شده برای
نمایش عمومی کد است و اطلاعات واقعی یا اتصال فعال به سرویس‌های بیرونی ندارد.

A legacy PHP/MySQL e-commerce application featuring a product storefront,
shopping cart, customer accounts, and an administration panel. This repository
is a sanitized public code showcase with no real data or active external
integrations.

> [!IMPORTANT]
> This is a portfolio/reference project, not a production-ready distribution.
> Payment, SMS, email, SnappPay, Pinket, and delivery integrations are disabled.

---

## فارسی

### معرفی پروژه

این پروژه برای مدیریت فروش آنلاین محصولات غذایی تخصصی طراحی شده است. ساختار
اصلی برنامه حفظ شده تا نحوه توسعه یک سامانه فروشگاهی PHP سنتی، از صفحات
محصول تا فرایند سفارش و مدیریت عملیات فروشگاه، قابل بررسی باشد.

محتویات پوشه `public_html` به ریشه مخزن منتقل شده‌اند. مسیر پایه فایل‌ها در
`base_shop.php` از محل واقعی پروژه محاسبه می‌شود و دیگر به مسیر هاست وابسته
نیست.

### امکانات

- نمایش و دسته‌بندی محصولات
- صفحه جزئیات و انتخاب ویژگی‌های محصول
- سبد خرید و فرایند ثبت سفارش چندمرحله‌ای
- ثبت‌نام، ورود و مدیریت حساب مشتری
- مشاهده سفارش‌ها و فاکتور
- صفحات محتوا، مطالب، نظرات و پرسش‌های متداول
- پنل مدیریت محصولات، دسته‌بندی‌ها، سفارش‌ها و مشتریان
- گزارش‌های فروش، فعالیت و اطلاعات جغرافیایی
- ابزارهای مدیریت ارسال، تولید، محتوا و صفحه اصلی
- ساختار ماژولار برای درگاه پرداخت، پیامک، ایمیل و سرویس‌های همکار

### فناوری‌ها

| بخش | فناوری |
| --- | --- |
| Backend | PHP 8+ |
| Database | MySQL / `mysqli` |
| Frontend | HTML، CSS، JavaScript |
| Web server | Apache + `mod_rewrite` یا PHP Development Server |
| Libraries | PHPMailer، NuSOAP، SimpleXLSX، XLSXWriter |

### ساختار پروژه

```text
account/      حساب کاربری مشتری
admin/        پنل مدیریت و عملیات فروشگاه
cart/         مراحل سبد خرید، پرداخت و فاکتور
modules/      منطق مشترک، دیتابیس و اتصال سرویس‌ها
products/     صفحات و قالب‌های محصولات
pages/        صفحات ثابت سایت
posts/        مطالب و محتوای وبلاگ
comment/      ثبت و نمایش نظرات
css/          استایل‌های عمومی و مدیریتی
js/           اسکریپت‌های رابط کاربری
fonts/        فونت‌های محلی پروژه
img/          تصاویر رابط کاربری
```

### اجرای محلی

پیش‌نیازها:

- PHP 8.0 یا بالاتر
- MySQL
- افزونه‌های `mysqli`، `curl`، `json` و `mbstring`

متغیرهای نمونه در فایل `.env.example` قرار دارند. برنامه متغیرهای دیتابیس را
مستقیماً از محیط اجرا می‌خواند:

```text
DB_HOST=127.0.0.1
DB_NAME=specialty_food_store
DB_USER=showcase_user
DB_PASSWORD=change-me
```

پس از آماده‌سازی یک دیتابیس محلی:

```bash
php -S 127.0.0.1:8080
```

سپس `http://127.0.0.1:8080/` را باز کنید.

این مخزن شامل دیتابیس یا اطلاعات نمونه کامل نیست؛ بنابراین نمایش کامل
محصولات و سفارش‌ها به یک schema و داده آزمایشی محلی نیاز دارد.

### امنیت نسخه عمومی

- تمام رمزها، توکن‌ها، شناسه‌های درگاه و کلیدهای واقعی حذف شده‌اند.
- لاگ‌ها، دیتابیس‌ها، تراکنش‌ها، اطلاعات مشتریان و کلید خصوصی منتشر نمی‌شوند.
- سرویس‌های پرداخت، پیامک، ایمیل، ارسال و مارکت‌پلیس غیرفعال هستند.
- مقادیر موجود در تنظیمات صرفاً placeholder و عمداً غیرقابل استفاده‌اند.
- فایل `.gitignore` از ثبت فایل‌های حساس و تولیدشده جلوگیری می‌کند.

ثابت `EXTERNAL_INTEGRATIONS_ENABLED` در `base_shop.php` عمداً `false` است.
فعال‌سازی سرویس‌ها باید فقط در یک نسخه خصوصی و پس از بازبینی امنیتی انجام شود.

### پیشنهاد برای تکمیل ویترین پروژه

برای جذاب‌تر شدن صفحه GitHub، این موارد را به مخزن اضافه کنید:

1. تصویر صفحه اصلی و فهرست محصولات
2. تصویر صفحه محصول و سبد خرید
3. تصویر داشبورد مدیریت، سفارش‌ها و گزارش‌ها با داده کاملاً ساختگی
4. GIF کوتاه از مسیر «محصول ← سبد خرید ← ثبت سفارش آزمایشی»
5. نمودار ساده معماری یا جریان ثبت سفارش
6. بخش «چالش‌های فنی» شامل مدیریت کد قدیمی، پاک‌سازی اطلاعات و اصلاح مسیرها
7. برنامه توسعه شامل Docker، migration دیتابیس، تست خودکار و بهبود امنیت

تصاویر را می‌توان در مسیر `docs/screenshots/` قرار داد و با این قالب نمایش داد:

```md
![صفحه اصلی فروشگاه](docs/screenshots/storefront.webp)
![پنل مدیریت](docs/screenshots/admin-dashboard.webp)
```

---

## English

### Overview

This project manages the online sale of specialty food products. Its original
folder structure has been retained to demonstrate a traditional PHP commerce
application covering the storefront, checkout flow, customer accounts, and
back-office operations.

The former `public_html` contents now live at the repository root.
`base_shop.php` resolves the filesystem root from its own location instead of
using a production hosting path.

### Features

- Product catalog and category filtering
- Product detail and option-selection pages
- Multi-step cart and order workflow
- Customer registration, authentication, and account management
- Order history and invoices
- Content pages, posts, comments, and FAQs
- Product, category, order, and customer administration
- Sales, activity, and geographic reports
- Shipping, production, content, and homepage management tools
- Modular payment, SMS, email, and marketplace integration code

### Technology

| Area | Technology |
| --- | --- |
| Backend | PHP 8+ |
| Database | MySQL with `mysqli` |
| Frontend | HTML, CSS, JavaScript |
| Web server | Apache with `mod_rewrite`, or PHP's development server |
| Libraries | PHPMailer, NuSOAP, SimpleXLSX, XLSXWriter |

### Project structure

```text
account/      Customer account features
admin/        Administration and store operations
cart/         Cart, checkout, payment, and invoices
modules/      Shared logic, database, and service adapters
products/     Product pages and templates
pages/        Static content pages
posts/        Articles and blog content
comment/      Customer comments
css/          Storefront and administration styles
js/           Browser-side behavior
fonts/        Local fonts
img/          Interface assets
```

### Local setup

Requirements:

- PHP 8.0+
- MySQL
- PHP extensions: `mysqli`, `curl`, `json`, and `mbstring`

Safe example values are documented in `.env.example`. Database configuration
is read from the process environment:

```text
DB_HOST=127.0.0.1
DB_NAME=specialty_food_store
DB_USER=showcase_user
DB_PASSWORD=change-me
```

After preparing a local database, start the application from the repository
root:

```bash
php -S 127.0.0.1:8080
```

Then open `http://127.0.0.1:8080/`.

The production database and customer data are intentionally excluded. Complete
catalog and order functionality therefore requires a reviewed local schema and
synthetic seed data.

### Public-repository safety

- Real credentials, tokens, merchant identifiers, and private keys were removed.
- Logs, databases, transactions, customer records, and generated exports are excluded.
- Payment, SMS, email, delivery, SnappPay, Pinket, and webhook traffic is disabled.
- Committed configuration values are deliberately nonfunctional placeholders.
- `.gitignore` blocks common secret, runtime, database, backup, and key files.

`EXTERNAL_INTEGRATIONS_ENABLED` is deliberately set to `false` in
`base_shop.php`. Connecting real services requires a separate private
configuration and security review.

### Recommended showcase additions

For a stronger GitHub presentation, add:

1. Storefront and catalog screenshots
2. Product and shopping-cart screenshots
3. Admin dashboard, order, and report screenshots using synthetic data
4. A short GIF of the product-to-test-order flow
5. A compact architecture or checkout-flow diagram
6. A “Technical challenges” section covering legacy modernization,
   sanitization, and path migration
7. A roadmap for Docker, database migrations, automated tests, and security work

Store media under `docs/screenshots/` and reference it as follows:

```md
![Storefront](docs/screenshots/storefront.webp)
![Administration dashboard](docs/screenshots/admin-dashboard.webp)
```

## Disclaimer

This repository is intended for portfolio and code-review purposes. It should
not be deployed for real commerce without dependency upgrades, database
migrations, automated testing, and a full application-security review.
