<?php
/**
 * EduLinks Application Configuration
 * 
 * Main application settings and parameters
 */

return [
    'name' => $_ENV['APP_NAME'] ?? 'EduLinks',
    'version' => '1.0.0',
    'url' => $_ENV['APP_URL'] ?? 'https://edulinks.sim.edu.az',
    'timezone' => 'Asia/Baku',
    'locale' => 'az',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
    'log_level' => $_ENV['LOG_LEVEL'] ?? 'error',
    
    // File upload settings
    'upload' => [
        'max_size' => 52428800, // 50MB
        'allowed_types' => [
            'pdf', 'doc', 'docx', 'xls', 'xlsx', 
            'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif'
        ],
        'path' => __DIR__ . '/../../public/uploads/',
        'url' => '/uploads/',
        'temp_path' => __DIR__ . '/../../public/uploads/temp/'
    ],
    
    // Security settings
    'security' => [
        'csrf_token_name' => 'edulinks_csrf_token',
        'session_name' => 'edulinks_session',
        'password_min_length' => 8,
        'failed_login_attempts' => 5,
        'failed_login_locktime' => 900, // 15 minutes
        'remember_token_lifetime' => 2592000, // 30 days
    ],
    
    // Session settings
    'session' => [
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 28800), // 8 hours
        'secure' => filter_var($_ENV['SESSION_SECURE'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
        'httponly' => filter_var($_ENV['SESSION_HTTPONLY'] ?? 'true', FILTER_VALIDATE_BOOLEAN),
        'same_site' => 'Strict',
    ],
    
    // Pagination settings
    'pagination' => [
        'per_page' => 20,
        'max_per_page' => 100
    ],
    
    // Paths
    'paths' => [
        'root' => dirname(__DIR__, 2),
        'app' => dirname(__DIR__),
        'public' => dirname(__DIR__, 2) . '/public',
        'logs' => dirname(__DIR__, 2) . '/logs',
        'backup' => dirname(__DIR__, 2) . '/backup',
    ]
];