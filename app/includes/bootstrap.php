<?php
/**
 * EduLinks Application Bootstrap
 * 
 * Initialize the application
 */

// Start error reporting based on environment
$config = require __DIR__ . '/../config/app.php';

if ($config['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Set timezone
date_default_timezone_set($config['timezone']);

// Load environment variables from .env file
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value, " \t\n\r\0\x0B\"'");
        
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key] = $value;
        }
    }
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../';
    
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

// Initialize core classes
use App\Core\Session;
use App\Core\Database;
use App\Core\Logger;

// Start session
Session::start();

// Set error and exception handlers
set_error_handler(function ($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false;
    }
    
    Logger::error("PHP Error: {$message}", [
        'severity' => $severity,
        'file' => $file,
        'line' => $line
    ]);
    
    if ($severity === E_ERROR || $severity === E_CORE_ERROR || $severity === E_COMPILE_ERROR) {
        http_response_code(500);
        if (!headers_sent()) {
            include __DIR__ . '/../views/errors/500.php';
        }
        exit;
    }
    
    return true;
});

set_exception_handler(function ($exception) {
    Logger::critical("Uncaught Exception: " . $exception->getMessage(), [
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTraceAsString()
    ]);
    
    http_response_code(500);
    if (!headers_sent()) {
        include __DIR__ . '/../views/errors/500.php';
    }
    exit;
});

// Register shutdown function for fatal errors
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        Logger::emergency("Fatal Error: {$error['message']}", [
            'file' => $error['file'],
            'line' => $error['line']
        ]);
        
        http_response_code(500);
        if (!headers_sent()) {
            include __DIR__ . '/../views/errors/500.php';
        }
    }
});

// Initialize database connection
try {
    Database::getInstance();
} catch (Exception $e) {
    Logger::critical("Database connection failed: " . $e->getMessage());
    
    http_response_code(500);
    if (!headers_sent()) {
        include __DIR__ . '/../views/errors/database.php';
    }
    exit;
}