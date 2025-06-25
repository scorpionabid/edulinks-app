<?php
echo "<h1>Environment Variables Test</h1>";
echo "<h2>getenv() Function</h2>";
echo "DB_HOST (getenv): " . (getenv('DB_HOST') ?: 'Not Set') . "<br>";
echo "DB_PORT (getenv): " . (getenv('DB_PORT') ?: 'Not Set') . "<br>";
echo "DB_NAME (getenv): " . (getenv('DB_NAME') ?: 'Not Set') . "<br>";

echo "<h2>\$_ENV Global Variable</h2>";
echo "DB_HOST (\$_ENV): " . ($_ENV['DB_HOST'] ?? 'Not Set') . "<br>";
echo "DB_PORT (\$_ENV): " . ($_ENV['DB_PORT'] ?? 'Not Set') . "<br>";
echo "DB_NAME (\$_ENV): " . ($_ENV['DB_NAME'] ?? 'Not Set') . "<br>";

echo "<h2>\$_SERVER Global Variable</h2>";
echo "DB_HOST (\$_SERVER): " . ($_SERVER['DB_HOST'] ?? 'Not Set') . "<br>";
echo "DB_PORT (\$_SERVER): " . ($_SERVER['DB_PORT'] ?? 'Not Set') . "<br>";
echo "DB_NAME (\$_SERVER): " . ($_SERVER['DB_NAME'] ?? 'Not Set') . "<br>";

echo "<h2>Database Config</h2>";
$config = require dirname(__DIR__) . '/app/config/database.php';
echo "Config Host: " . $config['host'] . "<br>";
echo "Config Port: " . $config['port'] . "<br>";
echo "Config Database: " . $config['database'] . "<br>";

// Test loading function from bootstrap
echo "<h2>Direct Function Test</h2>";
if (function_exists('getEnvironmentVariable')) {
    echo "DB_HOST (func): " . getEnvironmentVariable('DB_HOST', 'Not Set') . "<br>";
} else {
    echo "getEnvironmentVariable function not available<br>";
}

// Print all environment variables
echo "<h2>All Environment Variables</h2>";
echo "<pre>";
print_r($_ENV);
echo "</pre>";
?>
