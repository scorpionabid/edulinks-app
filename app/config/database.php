<?php
/**
 * EduLinks Database Configuration
 * 
 * Database connection parameters and settings
 */

// Helper function to get environment variables from multiple sources
function getEnvVar($name, $default = null) {
    // Check getenv first (works with Docker environment variables)
    if (($value = getenv($name)) !== false) {
        return $value;
    }
    
    // Then check $_SERVER
    if (isset($_SERVER[$name])) {
        return $_SERVER[$name];
    }
    
    // Then check $_ENV
    if (isset($_ENV[$name])) {
        return $_ENV[$name];
    }
    
    // Return default if not found
    return $default;
}

return [
    'host' => getEnvVar('DB_HOST', 'localhost'),
    'port' => getEnvVar('DB_PORT', 5432),
    'database' => getEnvVar('DB_NAME', 'edulinks_db'),
    'username' => getEnvVar('DB_USER', 'edulinks_user'),
    'password' => getEnvVar('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false,
    ]
];