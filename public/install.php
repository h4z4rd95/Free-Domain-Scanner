<?php
/**
 * DomainHub Installer
 * نسخه: 1.0.0
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$dbFile = __DIR__ . '/../config/database.php';
$sqlFile = __DIR__ . '/../sql/install_schema.sql';
$success = false;
$error = '';
$adminCredentials = null;

if ($step == 1) {
    $requirements = [
        'PHP Version >= 7.4' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'PDO Extension' => extension_loaded('pdo_mysql'),
        'JSON Extension' => extension_loaded('json'),
        'Config Writable' => is_writable(__DIR__ . '/../config'),
        'Storage Writable' => is_writable(__DIR__ . '/../storage'),
        'SQL Exists' => file_exists($sqlFile)
    ];
    include __DIR__ . '/../app/Views/install/step1.php';
} 
elseif ($step == 2) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $host = trim($_POST['db_host'] ?? 'localhost');
        $port = trim($_POST['db_port'] ?? '3306');
        $database = trim($_POST['db_name'] ?? '');
        $username = trim($_POST['db_user'] ?? '');
        $password = $_POST['db_pass'] ?? '';
        
        if (empty($host) || empty($database) || empty($username)) {
            $error = 'تمامی فیلدهای دیتابیس الزامی هستند.';
        } else {
            try {
                $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
                $pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
                
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $pdo->exec("USE `{$database}`");
                
                $_SESSION['install_data'] = [
                    'host' => $host, 'port' => $port, 'database' => $database,
                    'username' => $username, 'password' => $password
                ];
                
                header('Location: install.php?step=2&connected=1');
                exit;
            } catch (PDOException $e) {
                $error = 'خطا در اتصال به دیتابیس: ' . $e->getMessage();
            }
        }
    }
    
    if (isset($_GET['connected']) && !empty($_SESSION['install_data'])) {
        include __DIR__ . '/../app/Views/install/step2.php';
    } else {
        header('Location: install.php?step=1');
        exit;
    }
} 
elseif ($step == 3) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $_SESSION['install_data'] ?? [];
        
        if (empty($data['host']) || empty($data['database']) || empty($data['username'])) {
            $error = "اطلاعات دیتابیس ناقص است.";
        } else {
            try {
                $dsn = "mysql:host={$data['host']};port={$data['port']};dbname={$data['database']};charset=utf8mb4";
                $pdo = new PDO($dsn, $data['username'], $data['password'], [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);

                if (!file_exists($sqlFile)) throw new Exception('فایل SQL یافت نشد.');
                
                $sql = file_get_contents($sqlFile);
                foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                    if (!empty($stmt) && !preg_match('/^--/', $stmt)) $pdo->exec($stmt);
                }

                $adminUser = trim($_POST['admin_username'] ?? 'admin');
                $adminPass = password_hash($_POST['admin_password'] ?? 'admin123', PASSWORD_DEFAULT);
                $adminEmail = trim($_POST['admin_email'] ?? 'admin@example.com');
                
                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, 'admin', NOW())");
                $stmt->execute([$adminUser, $adminEmail, $adminPass]);
                
                $adminCredentials = ['username' => $adminUser, 'password' => $_POST['admin_password'] ?? 'admin123'];

                $cfg = "<?php\nreturn [\n    'host' => '".addslashes($data['host'])."',\n    'port' => '".addslashes($data['port'])."',\n    'database' => '".addslashes($data['database'])."',\n    'username' => '".addslashes($data['username'])."',\n    'password' => '".addslashes($data['password'])."',\n    'charset' => 'utf8mb4'\n];";
                
                if (file_put_contents($dbFile, $cfg) === false) throw new Exception("خطا در نوشتن فایل کانفیگ");
                file_put_contents(__DIR__ . '/../config/install.lock', date('Y-m-d H:i:s'));

                $success = true;
                unset($_SESSION['install_data']);
            } catch (Exception $e) {
                $error = "خطا: " . $e->getMessage();
            }
        }
    }
    
    if ($success) include __DIR__ . '/../app/Views/install/success.php';
    else echo "<div style='direction:rtl;font-family:tahoma;padding:20px;color:red'><h2>❌ خطا</h2><p>$error</p><a href='install.php?step=2'>بازگشت</a></div>";
} 
else {
    header('Location: install.php?step=1');
    exit;
}
?>
