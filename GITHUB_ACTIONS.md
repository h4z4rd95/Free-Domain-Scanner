# 🔄 راهنمای اتصال به GitHub و Codespaces

## مرحله ۱: ساخت Repository در GitHub

1. به [github.com](https://github.com) بروید
2. روی دکمه **+** → **New repository** کلیک کنید
3. نام repository را وارد کنید (مثلاً `domainhub`)
4. گزینه **Public** یا **Private** را انتخاب کنید
5. روی **Create repository** کلیک کنید

## مرحله ۲: اتصال پروژه به GitHub

در ترمینال دستورات زیر را اجرا کنید:

```bash
cd /workspace
git remote add origin https://github.com/YOUR_USERNAME/domainhub.git
git branch -M main
git push -u origin main
```

جای `YOUR_USERNAME` نام کاربری گیت‌هاب خود را وارد کنید.

## مرحله ۳: اجرای پروژه در GitHub Codespaces

1. به صفحه repository خود در GitHub بروید
2. روی دکمه سبز **Code** کلیک کنید
3. تب **Codespaces** را انتخاب کنید
4. روی **Create codespace on main** کلیک کنید
5. صبر کنید تا Codespace ساخته شود
6. ترمینال Codespace باز می‌شود

## مرحله ۴: اجرای پروژه در Codespaces

در ترمینال Codespaces دستورات زیر را اجرا کنید:

```bash
# نصب PHP و MySQL (اگر نیاز بود)
sudo apt-get update
sudo apt-get install -y php php-mysql mysql-server

# ساخت دیتابیس تست
mysql -u root -e "CREATE DATABASE domainhub_test;"

# کپی فایل تنظیمات
cp config/database.php.example config/database.php

# اجرای سرور PHP
cd public
php -S localhost:8080
```

## مرحله ۵: دسترسی به پروژه

پس از اجرای سرور، GitHub یک لینک عمومی نمایش می‌دهد:
```
Your server is running on http://localhost:8080
```

روی لینک کلیک کنید تا پروژه را ببینید.

## نکات مهم

### 🔐 امنیت
- فایل‌های حساس مثل `config/database.php` در `.gitignore` هستند
- بعد از نصب، فایل `install.php` را حذف کنید
- رمز عبور ادمین را تغییر دهید

### 📦 فایل‌های ضروری
- ✅ `README.md` - مستندات کامل
- ✅ `DEPLOYMENT.md` - راهنمای استقرار
- ✅ `public/install.php` - نصب‌کننده
- ✅ `sql/install_schema.sql` - ساختار دیتابیس

### 🚀 آپدیت پروژه

برای ارسال تغییرات جدید:

```bash
git add .
git commit -m "description of changes"
git push
```

---

## ❓ مشکل دارید؟

اگر با مشکلی مواجه شدید:
1. لاگ‌ها را بررسی کنید: `storage/logs/`
2. مستندات را بخوانید: `README.md`
3. Issue باز کنید در GitHub

