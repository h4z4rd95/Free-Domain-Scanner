<?php
/**
 * Application Configuration
 * General settings and constants
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
];
