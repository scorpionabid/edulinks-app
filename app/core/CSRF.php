<?php
/**
 * EduLinks CSRF Protection Class
 * 
 * Handles CSRF token generation and verification
 */

namespace App\Core;

class CSRF
{
    private static string $tokenName;
    
    /**
     * Initialize CSRF protection
     */
    private static function init(): void
    {
        if (!isset(self::$tokenName)) {
            $config = require dirname(__DIR__) . '/config/app.php';
            self::$tokenName = $config['security']['csrf_token_name'];
        }
        
        Session::start();
    }
    
    /**
     * Generate CSRF token
     */
    public static function token(): string
    {
        self::init();
        
        if (!Session::has(self::$tokenName)) {
            $token = bin2hex(random_bytes(32));
            Session::set(self::$tokenName, $token);
        }
        
        return Session::get(self::$tokenName);
    }
    
    /**
     * Verify CSRF token
     */
    public static function verify(string $token = null): bool
    {
        self::init();
        
        $sessionToken = Session::get(self::$tokenName);
        
        if ($token === null) {
            $token = filter_input(INPUT_POST, self::$tokenName) ?? 
                    filter_input(INPUT_GET, self::$tokenName) ?? 
                    ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
        }
        
        return !empty($sessionToken) && hash_equals($sessionToken, $token);
    }
    
    
    /**
     * Get token from request
     */
    private static function getTokenFromRequest(): ?string
    {
        // Check POST data
        if (isset($_POST[self::$tokenName])) {
            return $_POST[self::$tokenName];
        }
        
        // Check GET data (for AJAX requests)
        if (isset($_GET[self::$tokenName])) {
            return $_GET[self::$tokenName];
        }
        
        // Check headers (for API requests)
        $headers = getallheaders() ?: [];
        
        if (isset($headers['X-CSRF-Token'])) {
            return $headers['X-CSRF-Token'];
        }
        
        if (isset($headers['X-Csrf-Token'])) {
            return $headers['X-Csrf-Token'];
        }
        
        return null;
    }
    
    /**
     * Generate CSRF field for forms
     */
    public static function field(): string
    {
        $token = self::token();
        return '<input type="hidden" name="' . self::$tokenName . '" value="' . $token . '">';
    }
    
    /**
     * Generate CSRF meta tag
     */
    public static function metaTag(): string
    {
        $token = self::token();
        return '<meta name="csrf-token" content="' . $token . '">';
    }
    
    /**
     * Regenerate CSRF token
     */
    public static function regenerate(): string
    {
        self::init();
        
        $token = bin2hex(random_bytes(32));
        Session::set(self::$tokenName, $token);
        
        return $token;
    }
    
    /**
     * Clear CSRF token
     */
    public static function clear(): void
    {
        self::init();
        Session::remove(self::$tokenName);
    }
    
    /**
     * Get token name
     */
    public static function getTokenName(): string
    {
        self::init();
        return self::$tokenName;
    }
}