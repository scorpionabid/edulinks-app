<?php
/**
 * EduLinks Session Management Class
 * 
 * Handles session operations and flash messages
 */

namespace App\Core;

class Session
{
    private static bool $started = false;
    
    /**
     * Start session if not already started
     */
    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        
        $config = require dirname(__DIR__) . '/config/app.php';
        $sessionConfig = $config['session'];
        
        // Set session configuration
        ini_set('session.cookie_lifetime', $sessionConfig['lifetime']);
        ini_set('session.cookie_secure', $sessionConfig['secure'] ? '1' : '0');
        ini_set('session.cookie_httponly', $sessionConfig['httponly'] ? '1' : '0');
        ini_set('session.cookie_samesite', $sessionConfig['same_site']);
        ini_set('session.use_strict_mode', '1');
        ini_set('session.sid_length', '48');
        ini_set('session.sid_bits_per_character', '6');
        
        // Set session name
        session_name($config['security']['session_name']);
        
        // Start session
        session_start();
        self::$started = true;
        
        // Regenerate session ID periodically for security
        if (!self::has('_session_started')) {
            session_regenerate_id(true);
            self::set('_session_started', time());
        } elseif (time() - self::get('_session_started') > 3600) {
            session_regenerate_id(true);
            self::set('_session_started', time());
        }
    }
    
    /**
     * Set session value
     */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     */
    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }
    
    /**
     * Check if session key exists
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }
    
    /**
     * Remove session value
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }
    
    /**
     * Clear all session data
     */
    public static function clear(): void
    {
        self::start();
        $_SESSION = [];
    }
    
    /**
     * Destroy session
     */
    public static function destroy(): void
    {
        self::start();
        
        // Clear session data
        $_SESSION = [];
        
        // Delete session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Destroy session
        session_destroy();
        self::$started = false;
    }
    
    /**
     * Regenerate session ID
     */
    public static function regenerate(bool $deleteOld = true): void
    {
        self::start();
        session_regenerate_id($deleteOld);
        self::set('_session_started', time());
    }
    
    /**
     * Set flash message
     */
    public static function flash(string $type, string $message): void
    {
        self::start();
        $_SESSION['_flash'][$type] = $message;
    }
    
    /**
     * Get and remove flash message
     */
    public static function getFlash(string $type = null): ?string
    {
        self::start();
        
        if ($type === null) {
            $flash = $_SESSION['_flash'] ?? [];
            unset($_SESSION['_flash']);
            return $flash;
        }
        
        $message = $_SESSION['_flash'][$type] ?? null;
        unset($_SESSION['_flash'][$type]);
        
        return $message;
    }
    
    /**
     * Check if flash message exists
     */
    public static function hasFlash(string $type): bool
    {
        self::start();
        return isset($_SESSION['_flash'][$type]);
    }
    
    /**
     * Set error flash message
     */
    public static function error(string $message): void
    {
        self::flash('error', $message);
    }
    
    /**
     * Set success flash message
     */
    public static function success(string $message): void
    {
        self::flash('success', $message);
    }
    
    /**
     * Set warning flash message
     */
    public static function warning(string $message): void
    {
        self::flash('warning', $message);
    }
    
    /**
     * Set info flash message
     */
    public static function info(string $message): void
    {
        self::flash('info', $message);
    }
    
    /**
     * Get session ID
     */
    public static function getId(): string
    {
        self::start();
        return session_id();
    }
    
    /**
     * Get all session data
     */
    public static function all(): array
    {
        self::start();
        return $_SESSION;
    }
    
    /**
     * Check if session is active
     */
    public static function isActive(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }
}