<?php
/**
 * DomainHub - Main Entry Point
 * PHP Native MVC Router
 */

// Error reporting (disable in production)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set timezone
date_default_timezone_set('Asia/Tehran');

// Define base path
define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', __DIR__);

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = BASE_PATH . '/app/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Load helpers
require_once BASE_PATH . '/app/Helpers/functions.php';

// Start session
session_start();

// Simple router
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME']));
$requestUri = str_replace($basePath, '', $requestUri);
$requestUri = trim($requestUri, '/');

if (empty($requestUri)) {
    $requestUri = 'home';
}

// Route mapping
$routes = [
    '' => 'HomeController@index',
    'home' => 'HomeController@index',
    'search' => 'SearchController@index',
    'api/search' => 'SearchController@apiSearch',
    'domain/check' => 'DomainController@check',
    'domain/whois' => 'DomainController@whois',
    'order/create' => 'OrderController@create',
    'order/submit' => 'OrderController@submit',
    'dashboard' => 'DashboardController@index',
    'orders' => 'OrderController@index',
    'login' => 'AuthController@login',
    'logout' => 'AuthController@logout',
    'register' => 'AuthController@register',
    'admin' => 'Admin\DashboardController@index',
    'admin/orders' => 'Admin\OrderController@index',
    'admin/orders/verify' => 'Admin\OrderController@verify',
    'admin/users' => 'Admin\UserController@index',
    'admin/settings' => 'Admin\SettingController@index',
    'admin/logs' => 'Admin\LogController@index',
    'admin/cards' => 'Admin\CardController@index',
];

// Match route
$controllerMethod = $routes[$requestUri] ?? null;

if ($controllerMethod === null) {
    // Try to match with parameters
    $parts = explode('/', $requestUri);
    $controllerName = ucfirst($parts[0]) . 'Controller';
    $method = $parts[1] ?? 'index';
    
    if (count($parts) >= 2 && $parts[0] === 'admin') {
        $controllerName = 'App\\Controllers\\Admin\\' . ucfirst($parts[1]) . 'Controller';
        $method = $parts[2] ?? 'index';
    } else {
        $controllerName = 'App\\Controllers\\' . $controllerName;
    }
    
    if (class_exists($controllerName) && method_exists($controllerName, $method)) {
        $controllerMethod = [$controllerName, $method];
    }
}

if ($controllerMethod) {
    if (is_array($controllerMethod)) {
        $controller = new $controllerMethod[0]();
        $method = $controllerMethod[1];
        $controller->$method();
    } else {
        list($controllerName, $methodName) = explode('@', $controllerMethod);
        
        // Handle admin routes
        if (strpos($requestUri, 'admin/') === 0) {
            $className = 'App\\Controllers\\Admin\\' . $controllerName;
        } else {
            $className = 'App\\Controllers\\' . $controllerName;
        }
        
        if (class_exists($className)) {
            $controller = new $className();
            if (method_exists($controller, $methodName)) {
                $controller->$methodName();
            } else {
                http_response_code(404);
                echo "Method not found: {$methodName}";
            }
        } else {
            http_response_code(404);
            echo "Controller not found: {$controllerName}";
        }
    }
} else {
    http_response_code(404);
    echo "404 - Page not found";
}
