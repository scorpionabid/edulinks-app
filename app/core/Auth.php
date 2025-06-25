<?php
/**
 * EduLinks Authentication Class
 * 
 * Handles user authentication and authorization
 */

namespace App\Core;

use App\Models\User;

class Auth
{
    private static ?array $user = null;
    private static ?User $userModel = null;
    
    /**
     * Initialize auth system
     */
    private static function init(): void
    {
        if (self::$userModel === null) {
            self::$userModel = new User();
        }
        
        Session::start();
    }
    
    /**
     * Login user with email and password
     */
    public static function login(string $email, string $password, bool $remember = false): bool
    {
        self::init();
        
        $user = self::$userModel->findByEmailForAuth($email);
        
        if (!$user || !$user['is_active'] || !password_verify($password, $user['password'])) {
            return false;
        }
        
        // Update last login
        self::$userModel->update($user['id'], ['last_login' => date('Y-m-d H:i:s')]);
        
        // Set session
        Session::set('user_id', $user['id']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role']);
        Session::regenerate();
        
        // Handle remember me
        if ($remember) {
            self::setRememberToken($user['id']);
        }
        
        self::$user = $user;
        return true;
    }
    
    /**
     * Attempt to authenticate user (alias for login)
     */
    public static function attempt(string $email, string $password, bool $remember = false): bool
    {
        return self::login($email, $password, $remember);
    }
    
    /**
     * Login user by ID
     */
    public static function loginById(int $userId): bool
    {
        self::init();
        
        $user = self::$userModel->find($userId);
        
        if (!$user || !$user['is_active']) {
            return false;
        }
        
        // Set session
        Session::set('user_id', $user['id']);
        Session::set('user_email', $user['email']);
        Session::set('user_role', $user['role']);
        Session::regenerate();
        
        self::$user = $user;
        return true;
    }
    
    /**
     * Check if user is authenticated
     */
    public static function check(): bool
    {
        self::init();
        
        if (Session::has('user_id')) {
            return true;
        }
        
        // Check remember token
        return self::checkRememberToken();
    }
    
    /**
     * Get current authenticated user
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        
        if (self::$user === null) {
            $userId = Session::get('user_id');
            if ($userId) {
                self::$user = self::$userModel->find($userId);
            }
        }
        
        return self::$user;
    }
    
    /**
     * Get current user ID
     */
    public static function id(): ?int
    {
        return Session::get('user_id');
    }
    
    /**
     * Check if current user is admin
     */
    public static function isAdmin(): bool
    {
        $user = self::user();
        return $user && $user['role'] === 'admin';
    }
    
    /**
     * Check if current user is regular user
     */
    public static function isUser(): bool
    {
        $user = self::user();
        return $user && $user['role'] === 'user';
    }
    
    /**
     * Logout current user
     */
    public static function logout(): void
    {
        self::init();
        
        // Clear remember token
        if (Session::has('user_id')) {
            $userId = Session::get('user_id');
            self::clearRememberToken($userId);
        }
        
        // Clear remember cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }
        
        // Clear session
        Session::destroy();
        self::$user = null;
    }
    
    /**
     * Set remember token
     */
    private static function setRememberToken(int $userId): void
    {
        $token = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $token);
        
        // Save hashed token to database
        self::$userModel->update($userId, ['remember_token' => $hashedToken]);
        
        // Set cookie with plain token
        $config = require dirname(__DIR__) . '/config/app.php';
        $lifetime = $config['security']['remember_token_lifetime'];
        
        setcookie(
            'remember_token',
            $token,
            time() + $lifetime,
            '/',
            '',
            true, // secure
            true  // httponly
        );
    }
    
    /**
     * Check remember token
     */
    private static function checkRememberToken(): bool
    {
        if (!isset($_COOKIE['remember_token'])) {
            return false;
        }
        
        $token = $_COOKIE['remember_token'];
        $hashedToken = hash('sha256', $token);
        
        $user = self::$userModel->findWhere([
            'remember_token' => $hashedToken,
            'is_active' => true
        ]);
        
        if ($user) {
            // Auto login
            Session::set('user_id', $user['id']);
            Session::set('user_email', $user['email']);
            Session::set('user_role', $user['role']);
            
            self::$user = $user;
            return true;
        }
        
        // Invalid token, clear cookie
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        return false;
    }
    
    /**
     * Clear remember token
     */
    private static function clearRememberToken(int $userId): void
    {
        self::$userModel->update($userId, ['remember_token' => null]);
    }
    
    /**
     * Hash password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
    /**
     * Generate secure token
     */
    public static function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }
    
    /**
     * Check if user has permission for page
     */
    public static function hasPagePermission(int $pageId, string $permission = 'read'): bool
    {
        if (!self::check()) {
            return false;
        }
        
        // Admin has all permissions
        if (self::isAdmin()) {
            return true;
        }
        
        // Check user permissions
        $db = Database::getInstance();
        $result = $db->query(
            "SELECT id FROM page_permissions 
             WHERE user_id = ? AND page_id = ? AND permission_type = ?",
            [self::id(), $pageId, $permission]
        )->fetch();
        
        return $result !== null;
    }
    
    /**
     * Get user's accessible pages
     */
    public static function getAccessiblePages(): array
    {
        if (!self::check()) {
            return [];
        }
        
        $db = Database::getInstance();
        
        if (self::isAdmin()) {
            // Admin can access all active pages
            return $db->query(
                "SELECT * FROM pages WHERE is_active = true ORDER BY sort_order"
            )->fetchAll();
        }
        
        // Get user's permitted pages
        return $db->query(
            "SELECT p.*, pp.permission_type 
             FROM pages p 
             JOIN page_permissions pp ON p.id = pp.page_id 
             WHERE pp.user_id = ? AND p.is_active = true 
             ORDER BY p.sort_order",
            [self::id()]
        )->fetchAll();
    }
}