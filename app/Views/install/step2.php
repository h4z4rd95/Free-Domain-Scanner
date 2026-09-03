<?php
/**
 * View: Step 2 - Admin User Creation
 */
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نصب DomainHub - مرحله ۲</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .installer-container{background:white;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.3);max-width:650px;width:100%;overflow:hidden;animation:slideIn 0.5s ease-out}
        @keyframes slideIn{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
        .header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;padding:40px 30px;text-align:center}
        .header h1{font-size:2em;margin-bottom:10px;font-weight:700}
        .header p{opacity:0.9;font-size:0.95em}
        .progress-bar{display:flex;justify-content:space-between;padding:30px 40px;background:#f8f9fa;border-bottom:1px solid #e9ecef}
        .progress-step{display:flex;flex-direction:column;align-items:center;position:relative;flex:1}
        .progress-step:not(:last-child)::after{content:'';position:absolute;top:15px;left:60%;width:80%;height:2px;background:#e9ecef}
        .progress-step.active:not(:last-child)::after{background:#667eea}
        .progress-step.completed:not(:last-child)::after{background:#28a745}
        .step-number{width:30px;height:30px;border-radius:50%;background:#e9ecef;color:#6c757d;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:0.9em;margin-bottom:8px;transition:all 0.3s ease;z-index:1}
        .progress-step.active .step-number{background:#667eea;color:white}
        .progress-step.completed .step-number{background:#28a745;color:white}
        .step-label{font-size:0.85em;color:#6c757d;font-weight:500}
        .progress-step.active .step-label{color:#667eea;font-weight:600}
        .content{padding:40px 30px}
        .form-group{margin-bottom:25px}
        .form-group label{display:block;margin-bottom:8px;color:#495057;font-weight:600;font-size:0.95em}
        .form-group input{width:100%;padding:12px 15px;border:2px solid #e9ecef;border-radius:10px;font-size:1em;transition:all 0.3s ease;font-family:inherit}
        .form-group input:focus{outline:none;border-color:#667eea;box-shadow:0 0 0 3px rgba(102,126,234,0.1)}
        .form-group small{display:block;margin-top:5px;color:#6c757d;font-size:0.85em}
        .alert{padding:15px 20px;border-radius:10px;margin-bottom:25px;font-size:0.95em}
        .alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
        .alert-info{background:#e7f3ff;color:#004085;border:1px solid #b3d9ff}
        .btn{padding:14px 30px;border:none;border-radius:10px;font-size:1em;font-weight:600;cursor:pointer;transition:all 0.3s ease;font-family:inherit}
        .btn-primary{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;width:100%}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 5px 20px rgba(102,126,234,0.4)}
    </style>
</head>
<body>
<div class="installer-container">
    <div class="header">
        <h1>🚀 نصب DomainHub</h1>
        <p>سیستم حرفه‌ای جستجو و ثبت دامنه‌های اینترنتی</p>
    </div>
    
    <div class="progress-bar">
        <div class="progress-step completed">
            <div class="step-number">✓</div>
            <div class="step-label">تنظیمات دیتابیس</div>
        </div>
        <div class="progress-step active">
            <div class="step-number">2</div>
            <div class="step-label">اطلاعات ادمین</div>
        </div>
        <div class="progress-step">
            <div class="step-number">3</div>
            <div class="step-label">پایان نصب</div>
        </div>
    </div>
    
    <div class="content">
        <?php if(!empty($error)): ?>
        <div class="alert alert-error">
            <strong>⚠️ خطا:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <div class="alert alert-success">
            ✅ اتصال به دیتابیس با موفقیت برقرار شد.<br>
            حالا باید اطلاعات مدیر کل سیستم را وارد کنید.
        </div>
        
        <div class="alert alert-info">
            <strong>🔧 نکته مهم:</strong><br>
            پس از نصب، می‌توانید تنظیمات پیشرفته مانند API Key Namecheap را از پنل مدیریت تغییر دهید.
        </div>
        
        <form method="POST" action="install.php?step=3">
            <input type="hidden" name="step" value="2">
            
            <div class="form-group">
                <label for="admin_username">نام کاربری مدیر کل</label>
                <input type="text" id="admin_username" name="admin_username" value="<?php echo htmlspecialchars($_POST['admin_username'] ?? 'admin'); ?>" required>
                <small>برای ورود به پنل مدیریت</small>
            </div>
            
            <div class="form-group">
                <label for="admin_password">رمز عبور مدیر کل</label>
                <input type="password" id="admin_password" name="admin_password" value="<?php echo htmlspecialchars($_POST['admin_password'] ?? 'admin123'); ?>" required>
                <small>حداقل ۸ کاراکتر توصیه می‌شود</small>
            </div>
            
            <div class="form-group">
                <label for="admin_email">ایمیل مدیر</label>
                <input type="email" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($_POST['admin_email'] ?? 'admin@example.com'); ?>" required>
                <small>برای اطلاع‌رسانی‌های سیستم</small>
            </div>
            
            <button type="submit" class="btn btn-primary">📦 ایجاد جداول و تکمیل نصب</button>
        </form>
    </div>
</div>
</body>
</html>
