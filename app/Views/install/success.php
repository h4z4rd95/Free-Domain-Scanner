<?php
/**
 * View: Step 3 - Success
 */
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نصب DomainHub - تکمیل شد</title>
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .installer-container{background:white;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,0.3);max-width:700px;width:100%;overflow:hidden;animation:slideIn 0.5s ease-out}
        @keyframes slideIn{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
        .header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;padding:40px 30px;text-align:center}
        .header h1{font-size:2em;margin-bottom:10px;font-weight:700}
        .header p{opacity:0.9;font-size:0.95em}
        .progress-bar{display:flex;justify-content:space-between;padding:30px 40px;background:#f8f9fa;border-bottom:1px solid #e9ecef}
        .progress-step{display:flex;flex-direction:column;align-items:center;position:relative;flex:1}
        .progress-step:not(:last-child)::after{content:'';position:absolute;top:15px;left:60%;width:80%;height:2px;background:#28a745}
        .step-number{width:30px;height:30px;border-radius:50%;background:#28a745;color:white;display:flex;align-items:center;justify-content:center;font-weight:bold;font-size:0.9em;margin-bottom:8px;z-index:1}
        .step-label{font-size:0.85em;color:#28a745;font-weight:600}
        .content{padding:40px 30px}
        .success-message{text-align:center;padding:20px 0}
        .success-icon{width:80px;height:80px;background:linear-gradient(135deg,#28a745 0%,#20c997 100%);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 25px;animation:scaleIn 0.5s ease-out}
        @keyframes scaleIn{from{transform:scale(0)}to{transform:scale(1)}}
        .success-icon svg{width:40px;height:40px;fill:white}
        .success-message h2{color:#28a745;margin-bottom:15px;font-size:1.8em}
        .success-message p{color:#6c757d;margin-bottom:25px;line-height:1.6}
        .credentials-box{background:#fff3cd;border:2px solid #ffc107;border-radius:10px;padding:20px;margin:25px 0;text-align:center}
        .credentials-box h3{color:#856404;margin-bottom:15px}
        .credential-item{background:white;padding:10px 15px;border-radius:8px;margin:10px 0;display:flex;justify-content:space-between;align-items:center}
        .credential-label{font-weight:600;color:#495057}
        .credential-value{font-family:monospace;background:#f8f9fa;padding:5px 10px;border-radius:5px;color:#667eea;font-size:0.9em}
        .next-steps{background:#f8f9fa;padding:20px;border-radius:10px;margin-top:25px;text-align:right}
        .next-steps h3{color:#495057;margin-bottom:15px;font-size:1.1em}
        .next-steps ul{list-style:none;padding:0}
        .next-steps li{padding:8px 0;color:#6c757d;position:relative;padding-right:25px}
        .next-steps li::before{content:'✓';position:absolute;right:0;color:#28a745;font-weight:bold}
        .warning-box{background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:15px;margin-top:20px;color:#856404;font-size:0.9em;text-align:center}
        .btn-container{margin-top:30px;display:flex;gap:10px;justify-content:center}
        .btn{padding:14px 30px;border:none;border-radius:10px;font-size:1em;font-weight:600;cursor:pointer;transition:all 0.3s ease;font-family:inherit;text-decoration:none;display:inline-block}
        .btn-primary{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white}
        .btn-success{background:linear-gradient(135deg,#28a745 0%,#20c997 100%);color:white}
        .btn:hover{transform:translateY(-2px);box-shadow:0 5px 20px rgba(0,0,0,0.2)}
    </style>
</head>
<body>
<div class="installer-container">
    <div class="header">
        <h1>🎉 نصب DomainHub تکمیل شد!</h1>
        <p>سیستم آماده استفاده است</p>
    </div>
    
    <div class="progress-bar">
        <div class="progress-step">
            <div class="step-number">✓</div>
            <div class="step-label">دیتابیس</div>
        </div>
        <div class="progress-step">
            <div class="step-number">✓</div>
            <div class="step-label">جداول</div>
        </div>
        <div class="progress-step">
            <div class="step-number">✓</div>
            <div class="step-label">پایان</div>
        </div>
    </div>
    
    <div class="content">
        <div class="success-message">
            <div class="success-icon">
                <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            </div>
            <h2>✅ نصب با موفقیت تکمیل شد!</h2>
            <p>سیستم DomainHub آماده استفاده است.</p>
            
            <?php if(isset($adminCredentials)): ?>
            <div class="credentials-box">
                <h3>🔐 اطلاعات ورود به پنل مدیریت</h3>
                <div class="credential-item">
                    <span class="credential-label">نام کاربری:</span>
                    <span class="credential-value"><?php echo htmlspecialchars($adminCredentials['username']); ?></span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">رمز عبور:</span>
                    <span class="credential-value"><?php echo htmlspecialchars($adminCredentials['password']); ?></span>
                </div>
                <p style="color:#856404;font-size:0.85em;margin-top:15px;">
                    ⚠️ این اطلاعات را در جای امنی ذخیره کنید و پس از اولین ورود رمز عبور را تغییر دهید.
                </p>
            </div>
            <?php endif; ?>
            
            <div class="next-steps">
                <h3>📌 مراحل بعدی:</h3>
                <ul>
                    <li>حذف فایل install.php به دلایل امنیتی</li>
                    <li>ورود به پنل مدیریت با اطلاعات بالا</li>
                    <li>تنظیم API Key Namecheap برای جستجوی دامنه</li>
                    <li>تعریف کارت‌های بانکی برای پرداخت دستی</li>
                    <li>تنظیم منابع نرخ ارز (اختیاری)</li>
                    <li>شروع به کار سیستم!</li>
                </ul>
            </div>
            
            <div class="warning-box">
                <strong>⚠️ هشدار امنیتی:</strong><br>
                لطفاً فایل <code>install.php</code> را پس از اطمینان از نصب صحیح حذف کنید.
            </div>
            
            <div class="btn-container">
                <a href="../" class="btn btn-primary">🏠 مشاهده سایت</a>
                <a href="../admin/" class="btn btn-success">🔐 ورود به پنل مدیریت</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
