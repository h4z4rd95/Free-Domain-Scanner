<?php
/**
 * Helper Functions
 */

/**
 * Redirect to a URL
 */
function redirect(string $url): void
{
    header("Location: {$url}");
    exit;
}

/**
 * Get asset URL
 */
function asset(string $path): string
{
    return '/public/assets/' . ltrim($path, '/');
}

/**
 * Get base URL
 */
function baseUrl(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME']));
    return "{$protocol}://{$host}{$basePath}";
}

/**
 * Sanitize input
 */
function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCsrfToken(string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Check if user is logged in
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID
 */
function getCurrentUserId(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

/**
 * Check if user is admin
 */
function isAdmin(): bool
{
    return ($_SESSION['user_role'] ?? '') === 'admin';
}

/**
 * Require login
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        redirect(baseUrl() . '/login');
    }
}

/**
 * Require admin
 */
function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        die('دسترسی غیرمجاز');
    }
}

/**
 * Format price in Toman
 */
function formatPrice(float $amount): string
{
    return number_format($amount) . ' تومان';
}

/**
 * Format price in USD
 */
function formatPriceUsd(float $amount): string
{
    return '$' . number_format($amount, 2);
}

/**
 * Convert USD to Toman
 */
function usdToToman(float $usdAmount): float
{
    $rate = getCurrencyRate();
    return $usdAmount * $rate;
}

/**
 * Get current currency rate (USD to IRT)
 */
function getCurrencyRate(): float
{
    $config = require BASE_PATH . '/config/api.php';
    $cacheFile = BASE_PATH . '/storage/cache/currency_rate.json';
    
    // Check cache first
    if (file_exists($cacheFile)) {
        $cacheData = json_decode(file_get_contents($cacheFile), true);
        if ($cacheData && (time() - $cacheData['timestamp']) < $config['currency']['cache_duration']) {
            return (float) $cacheData['rate'];
        }
    }
    
    // Fetch from sources with fallback
    $rate = $config['currency']['default_rate'];
    $sources = $config['currency']['sources'];
    
    foreach ($sources as $source) {
        try {
            $fetchedRate = fetchCurrencyRate($source);
            if ($fetchedRate > 0) {
                $rate = $fetchedRate;
                break;
            }
        } catch (\Exception $e) {
            error_log("Failed to fetch rate from {$source}: " . $e->getMessage());
            continue;
        }
    }
    
    // Cache the rate
    $cacheDir = dirname($cacheFile);
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }
    
    file_put_contents($cacheFile, json_encode([
        'rate' => $rate,
        'timestamp' => time(),
        'source' => $source
    ]));
    
    return $rate;
}

/**
 * Fetch currency rate from a source
 */
function fetchCurrencyRate(string $source): float
{
    // Mock implementation - in production, use actual API calls
    // Bonbast.com and TGJU.org don't have free public APIs
    // You'll need to implement web scraping or use paid APIs
    
    switch ($source) {
        case 'bonbast':
            // Implement Bonbast scraping here
            return 0; // Return 0 to indicate failure
            
        case 'tgju':
            // Implement TGJU scraping here
            return 0; // Return 0 to indicate failure
            
        default:
            return 0;
    }
}

/**
 * Generate order number
 */
function generateOrderNumber(): string
{
    return 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
}

/**
 * Get client IP
 */
function getClientIp(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Create JSON response
 */
function jsonResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Persian date formatter
 */
function persianDate(string $date): string
{
    $datetime = new DateTime($date);
    $timestamp = $datetime->getTimestamp();
    
    // Simple conversion (for production, use jdf library)
    $day = date('d', $timestamp);
    $month = date('m', $timestamp);
    $year = date('Y', $timestamp);
    
    // Convert to Persian numbers
    $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $englishNumbers = range(0, 9);
    
    $persianDate = str_replace($englishNumbers, $persianNumbers, "{$year}/{$month}/{$day}");
    
    return $persianDate;
}

/**
 * Upload file
 */
function uploadFile(array $file, string $destination): array
{
    $config = require BASE_PATH . '/config/app.php';
    
    $allowedTypes = $config['upload']['allowed_types'];
    $maxSize = $config['upload']['max_size'];
    
    // Check for errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'خطا در آپلود فایل'];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'حجم فایل بیش از حد مجاز است'];
    }
    
    // Check file type
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedTypes)) {
        return ['success' => false, 'message' => 'نوع فایل مجاز نیست'];
    }
    
    // Generate unique filename
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $filepath = $destination . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => true, 'filename' => $filename, 'path' => $filepath];
    }
    
    return ['success' => false, 'message' => 'خطا در ذخیره فایل'];
}

/**
 * Get search history from cookies (for guests)
 */
function getGuestSearchHistory(): array
{
    $config = require BASE_PATH . '/config/app.php';
    $cookieName = $config['cookie']['search_history'];
    
    if (!isset($_COOKIE[$cookieName])) {
        return [];
    }
    
    $history = json_decode($_COOKIE[$cookieName], true);
    return is_array($history) ? $history : [];
}

/**
 * Save search to cookie (for guests)
 */
function saveGuestSearch(array $searchData): void
{
    $config = require BASE_PATH . '/config/app.php';
    $cookieName = $config['cookie']['search_history'];
    $maxItems = $config['cookie']['max_items'];
    $lifetime = $config['cookie']['history_lifetime'] * 86400; // days to seconds
    
    $history = getGuestSearchHistory();
    
    // Add new search at beginning
    array_unshift($history, $searchData);
    
    // Limit items
    $history = array_slice($history, 0, $maxItems);
    
    setcookie($cookieName, json_encode($history), time() + $lifetime, '/');
}

/**
 * Clear guest search history
 */
function clearGuestSearchHistory(): void
{
    $config = require BASE_PATH . '/config/app.php';
    $cookieName = $config['cookie']['search_history'];
    setcookie($cookieName, '', time() - 3600, '/');
}
