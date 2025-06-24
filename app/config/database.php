<?php
/**
 * EduLinks Database Configuration
 * 
 * Database connection parameters and settings
 */

return [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? 5432,
    'database' => $_ENV['DB_NAME'] ?? 'edulinks_db',
    'username' => $_ENV['DB_USER'] ?? 'edulinks_user',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_PERSISTENT => false,
    ]
];