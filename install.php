<?php
/**
 * DomainHub Installer
 * این فایل تنها یکبار اجرا می‌شود و پس از تکمیل نصب، غیرفعال می‌گردد.
 */

// غیرفعال کردن گزارش خطا برای نمایش تمیز (در محیط پروداکشن)
// در حین نصب خطاها را نشان می‌دهیم تا دیباگ راحت باشد
ini_set('display_errors', 1);
error_reporting(E_ALL);

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$dbHost = $dbName = $dbUser = $dbPass = $dbPrefix = '';
$adminEmail = $adminPass = '';
$message = '';
$messageType = ''; // success, error

// بررسی اینکه آیا قبلاً نصب شده است
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
    if (defined('DB_NAME') && defined('INSTALLED') && INSTALLED === true) {
        die("<h1 style='text-align:center; font-family:tahoma; margin-top:50px;'>سیستم قبلاً نصب شده است.<br>برای امنیت بیشتر، فایل <code>install.php</code> را حذف کنید.</h1>");
    }
}

// پردازش فرم‌ها
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step === 2) {
        $dbHost = trim($_POST['db_host']);
        $dbName = trim($_POST['db_name']);
        $dbUser = trim($_POST['db_user']);
        $dbPass = $_POST['db_pass'];
        $dbPrefix = trim($_POST['db_prefix']) ?: 'dh_';

        // تست اتصال به دیتابیس
        try {
            $dsn = "mysql:host=$dbHost;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // بررسی وجود دیتابیس
            $stmt = $pdo->query("SHOW DATABASES LIKE '$dbName'");
            if ($stmt->rowCount() === 0) {
                // اگر دیتابیس وجود نداشت، سعی می‌کنیم بسازیم (نیاز به دسترسی روت دارد)
                // در هاست‌های اشتراکی معمولاً دیتابیس از قبل در سی‌پنل ساخته می‌شود
                throw new Exception("دیتابیس <b>$dbName</b> یافت نشد. لطفاً ابتدا آن را در پنل هاست خود بسازید.");
            }
            
            // ذخیره موقت در سشن برای مرحله بعد
            session_start();
            $_SESSION['db_config'] = [
                'host' => $dbHost,
                'name' => $dbName,
                'user' => $dbUser,
                'pass' => $dbPass,
                'prefix' => $dbPrefix
            ];
            
            header('Location: install.php?step=3');
            exit;
        } catch (PDOException $e) {
            $message = "خطا در اتصال به دیتابیس: " . $e->getMessage();
            $messageType = 'error';
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    } elseif ($step === 3) {
        session_start();
        if (!isset($_SESSION['db_config'])) {
            header('Location: install.php?step=2');
            exit;
        }
        
        $dbConfig = $_SESSION['db_config'];
        $adminEmail = trim($_POST['admin_email']);
        $adminPass = password_hash($_POST['admin_pass'], PASSWORD_DEFAULT);
        
        try {
            $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // خواندن فایل SQL و اجرای آن
            $sqlFile = __DIR__ . '/sql_schema.sql';
            if (!file_exists($sqlFile)) {
                throw new Exception("فایل sql_schema.sql یافت نشد.");
            }
            $sql = file_get_contents($sqlFile);
            
            // جایگزینی پیشوند جدول در فایل SQL
            $sql = str_replace('{{PREFIX}}', $dbConfig['prefix'], $sql);
            
            // اجرای کوئری‌ها
            $pdo->exec($sql);
            
            // ایجاد کاربر ادمین
            $prefix = $dbConfig['prefix'];
            $stmt = $pdo->prepare("INSERT INTO {$prefix}users (email, password, role, created_at) VALUES (?, ?, 'admin', NOW())");
            $stmt->execute([$adminEmail, $adminPass]);
            
            // ایجاد فایل config.php
            $configContent = "<?php\n";
            $configContent .= "define('DB_HOST', '{$dbConfig['host']}');\n";
            $configContent .= "define('DB_NAME', '{$dbConfig['name']}');\n";
            $configContent .= "define('DB_USER', '{$dbConfig['user']}');\n";
            $configContent .= "define('DB_PASS', '{$dbConfig['pass']}');\n";
            $configContent .= "define('DB_PREFIX', '{$dbConfig['prefix']}');\n";
            $configContent .= "define('INSTALLED', true);\n";
            $configContent .= "define('SITE_URL', '" . (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]' . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . "');\n";
            $configContent .= "// کلید API Namecheap (بعداً در پنل مدیریت قابل تغییر است)\n";
            $configContent .= "define('NAMECHEAP_API_USER', '');\n";
            $configContent .= "define('NAMECHEAP_API_KEY', '');\n";
            $configContent .= "define('NAMECHEAP_CLIENT_IP', '');\n";
            
            if (file_put_contents(__DIR__ . '/config.php', $configContent)) {
                // حذف سشن و هدایت به پایان
                session_destroy();
                header('Location: install.php?step=4');
                exit;
            } else {
                throw new Exception("امکان نوشتن فایل config.php وجود ندارد. دسترسی پوشه را بررسی کنید (chmod 755 یا 777).");
            }
            
        } catch (Exception $e) {
            $message = "خطا در نصب: " . $e->getMessage();
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نصب DomainHub</title>
    <style>
        :root { --primary: #4f46e5; --bg: #f3f4f6; --card: #ffffff; --text: #1f2937; }
        body { font-family: Tahoma, 'Segoe UI', sans-serif; background: var(--bg); color: var(--text); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background: var(--card); padding: 2rem; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 100%; max-width: 500px; text-align: center; }
        h1 { color: var(--primary); margin-bottom: 1.5rem; font-size: 1.8rem; }
        .form-group { margin-bottom: 1rem; text-align: right; }
        label { display: block; margin-bottom: 0.5rem; font-weight: bold; font-size: 0.9rem; }
        input[type="text"], input[type="password"], input[type="email"] { width: 100%; padding: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; box-sizing: border-box; font-family: inherit; }
        input:focus { outline: 2px solid var(--primary); border-color: transparent; }
        button { background: var(--primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 0.5rem; cursor: pointer; width: 100%; font-size: 1rem; font-weight: bold; transition: background 0.3s; }
        button:hover { background: #4338ca; }
        .alert { padding: 1rem; border-radius: 0.5rem; margin-bottom: 1rem; font-size: 0.9rem; }
        .alert-error { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .alert-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .steps { display: flex; justify-content: space-between; margin-bottom: 2rem; position: relative; }
        .steps::before { content: ''; position: absolute; top: 50%; left: 0; right: 0; height: 2px; background: #e5e7eb; z-index: 0; transform: translateY(-50%); }
        .step { width: 30px; height: 30px; background: #e5e7eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: bold; position: relative; z-index: 1; color: #6b7280; }
        .step.active { background: var(--primary); color: white; }
        .step.completed { background: #10b981; color: white; }
        .note { font-size: 0.8rem; color: #6b7280; margin-top: 0.5rem; text-align: right; }
    </style>
</head>
<body>

<div class="container">
    <div class="steps">
        <div class="step <?php echo $step >= 1 ? 'active' : '' ?> <?php echo $step > 1 ? 'completed' : '' ?>">1</div>
        <div class="step <?php echo $step >= 2 ? 'active' : '' ?> <?php echo $step > 2 ? 'completed' : '' ?>">2</div>
        <div class="step <?php echo $step >= 3 ? 'active' : '' ?> <?php echo $step > 3 ? 'completed' : '' ?>">3</div>
        <div class="step <?php echo $step >= 4 ? 'active' : '' ?>">4</div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if ($step === 1): ?>
        <h1>خوش آمدید</h1>
        <p>به نصب‌کننده هوشمند <strong>DomainHub</strong> خوش آمدید.</p>
        <p style="font-size: 0.9rem; line-height: 1.6; color: #4b5563;">
            این اسکریپت به صورت خودکار جداول دیتابیس را ایجاد کرده و فایل پیکربندی را می‌سازد.
            قبل از شروع، مطمئن شوید که:<br>
            ✅ یک دیتابیس MySQL در هاست خود ساخته‌اید.<br>
            ✅ نام کاربری و رمز عبور دیتابیس را دارید.<br>
            ✅ دسترسی نوشتن در پوشه اصلی سایت وجود دارد.
        </p>
        <a href="?step=2" style="text-decoration: none;"><button>شروع نصب</button></a>

    <?php elseif ($step === 2): ?>
        <h1>تنظیمات دیتابیس</h1>
        <form method="POST">
            <div class="form-group">
                <label>میزبان دیتابیس (Database Host)</label>
                <input type="text" name="db_host" value="localhost" required>
                <div class="note">معمولاً localhost است. در برخی هاست‌ها آدرس IP یا دامنه خاص است.</div>
            </div>
            <div class="form-group">
                <label>نام دیتابیس (Database Name)</label>
                <input type="text" name="db_name" required placeholder="مثال: mysite_domain">
                <div class="note">باید از قبل در پنل هاست ساخته شده باشد.</div>
            </div>
            <div class="form-group">
                <label>نام کاربری دیتابیس (Username)</label>
                <input type="text" name="db_user" required>
            </div>
            <div class="form-group">
                <label>رمز عبور دیتابیس (Password)</label>
                <input type="password" name="db_pass" required>
            </div>
            <div class="form-group">
                <label>پیشوند جداول (Table Prefix)</label>
                <input type="text" name="db_prefix" value="dh_" placeholder="dh_">
                <div class="note">برای امنیت بهتر تغییر دهید، مخصوصاً اگر چند اسکریپت روی یک دیتابیس دارید.</div>
            </div>
            <button type="submit">بررسی اتصال و ادامه</button>
        </form>
        <div style="margin-top: 1rem;"><a href="?step=1" style="color: #6b7280; font-size: 0.9rem;">بازگشت</a></div>

    <?php elseif ($step === 3): ?>
        <h1>تنظیمات مدیر کل</h1>
        <p style="font-size: 0.9rem; margin-bottom: 1.5rem;">اطلاعات ورود به پنل مدیریت را وارد کنید.</p>
        <form method="POST">
            <div class="form-group">
                <label>ایمیل مدیر (Admin Email)</label>
                <input type="email" name="admin_email" required>
            </div>
            <div class="form-group">
                <label>رمز عبور (Password)</label>
                <input type="password" name="admin_pass" required minlength="6">
            </div>
            <button type="submit">نهایی کردن نصب</button>
        </form>
        <div style="margin-top: 1rem;"><a href="?step=2" style="color: #6b7280; font-size: 0.9rem;">بازگشت</a></div>

    <?php elseif ($step === 4): ?>
        <h1 style="color: #10b981;">نصب با موفقیت انجام شد! 🎉</h1>
        <p>سیستم DomainHub آماده استفاده است.</p>
        <div class="alert alert-success" style="text-align: right;">
            <strong>اقدامات امنیتی مهم:</strong><br>
            1. فایل <code>install.php</code> و <code>sql_schema.sql</code> را همین حالا از سرور حذف کنید.<br>
            2. فایل <code>config.php</code> حاوی اطلاعات حساس است؛ دسترسی آن را محدود کنید.
        </div>
        <a href="index.php" style="text-decoration: none;"><button>ورود به سایت</button></a>
        <div style="margin-top: 1rem;"><a href="admin/login.php" style="color: var(--primary); font-size: 0.9rem;">ورود به پنل مدیریت</a></div>
    <?php endif; ?>
</div>

</body>
</html>
