<?php
/**
 * EduLinks Logging Class
 * 
 * Handles application logging and error tracking
 */

namespace App\Core;

class Logger
{
    private static string $logPath;
    private static string $logLevel;
    
    /**
     * Initialize logger
     */
    private static function init(): void
    {
        if (!isset(self::$logPath)) {
            $config = require dirname(__DIR__) . '/config/app.php';
            self::$logPath = $config['paths']['logs'];
            self::$logLevel = $config['log_level'];
            
            // Create logs directory if not exists
            if (!is_dir(self::$logPath)) {
                mkdir(self::$logPath, 0755, true);
            }
        }
    }
    
    /**
     * Log emergency message
     */
    public static function emergency(string $message, array $context = []): void
    {
        self::log('emergency', $message, $context);
    }
    
    /**
     * Log alert message
     */
    public static function alert(string $message, array $context = []): void
    {
        self::log('alert', $message, $context);
    }
    
    /**
     * Log critical message
     */
    public static function critical(string $message, array $context = []): void
    {
        self::log('critical', $message, $context);
    }
    
    /**
     * Log error message
     */
    public static function error(string $message, array $context = []): void
    {
        self::log('error', $message, $context);
    }
    
    /**
     * Log warning message
     */
    public static function warning(string $message, array $context = []): void
    {
        self::log('warning', $message, $context);
    }
    
    /**
     * Log notice message
     */
    public static function notice(string $message, array $context = []): void
    {
        self::log('notice', $message, $context);
    }
    
    /**
     * Log info message
     */
    public static function info(string $message, array $context = []): void
    {
        self::log('info', $message, $context);
    }
    
    /**
     * Log debug message
     */
    public static function debug(string $message, array $context = []): void
    {
        self::log('debug', $message, $context);
    }
    
    /**
     * Log message with level
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        self::init();
        
        // Check if level should be logged
        if (!self::shouldLog($level)) {
            return;
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = self::formatMessage($timestamp, $level, $message, $context);
        
        // Write to file
        $logFile = self::getLogFile($level);
        file_put_contents($logFile, $formattedMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
        
        // Also write to application log
        $appLogFile = self::$logPath . '/application.log';
        file_put_contents($appLogFile, $formattedMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Check if level should be logged
     */
    private static function shouldLog(string $level): bool
    {
        $levels = [
            'emergency' => 0,
            'alert'     => 1,
            'critical'  => 2,
            'error'     => 3,
            'warning'   => 4,
            'notice'    => 5,
            'info'      => 6,
            'debug'     => 7,
        ];
        
        $currentLevel = $levels[self::$logLevel] ?? 3;
        $messageLevel = $levels[$level] ?? 7;
        
        return $messageLevel <= $currentLevel;
    }
    
    /**
     * Format log message
     */
    private static function formatMessage(string $timestamp, string $level, string $message, array $context): string
    {
        $levelUpper = strtoupper($level);
        $formatted = "[{$timestamp}] {$levelUpper}: {$message}";
        
        // Add context if provided
        if (!empty($context)) {
            $formatted .= ' | Context: ' . json_encode($context, JSON_UNESCAPED_UNICODE);
        }
        
        // Add user info if available
        if (Auth::check()) {
            $user = Auth::user();
            $formatted .= " | User: {$user['email']} (ID: {$user['id']})";
        }
        
        // Add request info
        $formatted .= " | IP: " . self::getClientIp();
        $formatted .= " | URI: " . ($_SERVER['REQUEST_URI'] ?? 'N/A');
        $formatted .= " | Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'N/A');
        
        return $formatted;
    }
    
    /**
     * Get log file path for level
     */
    private static function getLogFile(string $level): string
    {
        $date = date('Y-m-d');
        return self::$logPath . "/{$level}-{$date}.log";
    }
    
    /**
     * Get client IP address
     */
    private static function getClientIp(): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                return $ip;
            }
        }
        
        return 'unknown';
    }
    
    /**
     * Log authentication events
     */
    public static function logAuth(string $event, string $email, bool $success = true): void
    {
        $message = "Authentication {$event} for {$email}";
        $context = [
            'event' => $event,
            'email' => $email,
            'success' => $success,
            'ip' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        if ($success) {
            self::info($message, $context);
        } else {
            self::warning($message, $context);
        }
    }
    
    /**
     * Log file operations
     */
    public static function logFile(string $operation, string $filename, int $userId = null): void
    {
        $message = "File {$operation}: {$filename}";
        $context = [
            'operation' => $operation,
            'filename' => $filename,
            'user_id' => $userId,
            'ip' => self::getClientIp()
        ];
        
        self::info($message, $context);
    }
    
    /**
     * Log database operations
     */
    public static function logDatabase(string $query, array $params = [], float $executionTime = null): void
    {
        $message = "Database query executed";
        $context = [
            'query' => $query,
            'params' => $params,
            'execution_time' => $executionTime
        ];
        
        self::debug($message, $context);
    }
    
    /**
     * Log security events
     */
    public static function logSecurity(string $event, string $details = ''): void
    {
        $message = "Security event: {$event}";
        $context = [
            'event' => $event,
            'details' => $details,
            'ip' => self::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'user_id' => Auth::id()
        ];
        
        self::warning($message, $context);
    }
    
    /**
     * Clean old log files
     */
    public static function cleanup(int $daysToKeep = 30): int
    {
        self::init();
        
        $deleted = 0;
        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        
        $files = glob(self::$logPath . '/*.log');
        
        foreach ($files as $file) {
            if (filemtime($file) < $cutoffTime) {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }
        
        self::info("Log cleanup completed", ['deleted_files' => $deleted, 'days_kept' => $daysToKeep]);
        
        return $deleted;
    }
}