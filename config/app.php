<?php
/**
 * Application Configuration
 * General settings and constants - Update via admin panel
 */
return [
    'app' => [
        'name' => 'DomainHub',
        'version' => '1.0.0',
        'url' => getenv('APP_URL') ?: 'http://localhost',
        'timezone' => 'Asia/Tehran',
        'locale' => 'fa_IR',
        'debug' => getenv('APP_DEBUG') ?: false,
    ],
    
    'admin' => [
        'username' => getenv('ADMIN_USERNAME') ?: 'admin',
        'password' => getenv('ADMIN_PASSWORD') ?: 'admin123', // Will be hashed on first login
        'email' => getenv('ADMIN_EMAIL') ?: 'admin@example.com',
    ],
    
    'session' => [
        'lifetime' => 7200, // 2 hours
        'name' => 'domainhub_session',
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
    ],
    
    'cookie' => [
        'search_history' => 'dh_search_history',
        'history_lifetime' => 30, // days
        'max_items' => 50, // maximum search history items per user
    ],
    
    'upload' => [
        'max_size' => 2 * 1024 * 1024, // 2MB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'pdf'],
        'receipts_path' => __DIR__ . '/../public/uploads/receipts/',
    ],
    
    'pagination' => [
        'per_page' => 20,
        'max_pages' => 100,
    ],
    
    'security' => [
        'csrf_token_name' => 'csrf_token',
        'password_min_length' => 8,
        'max_login_attempts' => 5,
        'lockout_time' => 900, // 15 minutes
    ],
    
    'api' => [
        'namecheap' => [
            'api_key' => getenv('NAMECHEAP_API_KEY') ?: '',
            'username' => getenv('NAMECHEAP_USERNAME') ?: '',
            'client_ip' => getenv('NAMECHEAP_CLIENT_IP') ?: '',
            'sandbox' => true,
        ],
        'currency_sources' => explode(',', getenv('CURRENCY_SOURCES') ?: 'bonbast,tgju'),
        'currency_cache_duration' => 3600, // 1 hour
        'default_currency_rate' => 50000, // Fallback rate
    ],
    
    'payment' => [
        'manual_transfer_enabled' => true,
        'cards_display_mode' => 'priority', // priority, random, single
        'require_receipt' => true,
    ],
];
