<?php
/**
 * EduLinks Authentication Tests
 * 
 * Test authentication and authorization functionality
 */

require_once '../app/includes/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;
use App\Models\User;
use App\Models\Page;

class AuthTest
{
    private Database $db;
    private User $userModel;
    private Page $pageModel;
    private array $testData = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->userModel = new User();
        $this->pageModel = new Page();
    }
    
    /**
     * Run all tests
     */
    public function runTests(): void
    {
        echo "🔐 EduLinks Authentication Tests\n";
        echo "==============================\n\n";
        
        $tests = [
            'testPasswordHashing',
            'testUserLogin',
            'testUserLogout',
            'testAdminCheck',
            'testPagePermissions',
            'testSessionManagement',
            'testRememberMe',
            'testCSRFProtection'
        ];
        
        $passed = 0;
        $failed = 0;
        
        foreach ($tests as $test) {
            try {
                echo "Running: $test... ";
                $this->$test();
                echo "✅ PASSED\n";
                $passed++;
            } catch (Exception $e) {
                echo "❌ FAILED: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
        
        echo "\n📊 Test Results:\n";
        echo "✅ Passed: $passed\n";
        echo "❌ Failed: $failed\n";
        echo "📈 Success Rate: " . round(($passed / ($passed + $failed)) * 100, 2) . "%\n";
        
        $this->cleanup();
    }
    
    /**
     * Test password hashing
     */
    private function testPasswordHashing(): void
    {
        $password = 'testpassword123';
        $hashedPassword = Auth::hashPassword($password);
        
        $this->assert(!empty($hashedPassword), 'Password hash should not be empty');
        $this->assert($hashedPassword !== $password, 'Password should be hashed');
        $this->assert(Auth::verifyPassword($password, $hashedPassword), 'Password verification should work');
        $this->assert(!Auth::verifyPassword('wrongpassword', $hashedPassword), 'Wrong password should not verify');
    }
    
    /**
     * Test user login
     */
    private function testUserLogin(): void
    {
        // Create test user
        $userId = $this->userModel->create([
            'first_name' => 'Auth',
            'last_name' => 'Test',
            'email' => 'auth@test.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true
        ]);
        $this->testData['users'][] = $userId;
        
        // Clear any existing session
        Auth::logout();
        
        // Test successful login
        $loginResult = Auth::login('auth@test.com', 'password123');
        $this->assert($loginResult, 'Login should succeed');
        $this->assert(Auth::check(), 'User should be authenticated after login');
        $this->assert(Auth::id() === $userId, 'Logged in user ID should match');
        
        // Test user data
        $user = Auth::user();
        $this->assert($user['email'] === 'auth@test.com', 'User email should match');
        $this->assert($user['first_name'] === 'Auth', 'User first name should match');
        
        // Test failed login
        Auth::logout();
        $failedLogin = Auth::login('auth@test.com', 'wrongpassword');
        $this->assert(!$failedLogin, 'Login should fail with wrong password');
        $this->assert(!Auth::check(), 'User should not be authenticated after failed login');
        
        // Test login with inactive user
        $this->userModel->update($userId, ['is_active' => false]);
        $inactiveLogin = Auth::login('auth@test.com', 'password123');
        $this->assert(!$inactiveLogin, 'Login should fail for inactive user');
        
        // Reactivate user for cleanup
        $this->userModel->update($userId, ['is_active' => true]);
    }
    
    /**
     * Test user logout
     */
    private function testUserLogout(): void
    {
        // Login first
        $userId = $this->userModel->create([
            'first_name' => 'Logout',
            'last_name' => 'Test',
            'email' => 'logout@test.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true
        ]);
        $this->testData['users'][] = $userId;
        
        Auth::login('logout@test.com', 'password123');
        $this->assert(Auth::check(), 'User should be logged in');
        
        // Test logout
        Auth::logout();
        $this->assert(!Auth::check(), 'User should not be authenticated after logout');
        $this->assert(Auth::id() === null, 'User ID should be null after logout');
        $this->assert(Auth::user() === null, 'User data should be null after logout');
    }
    
    /**
     * Test admin check
     */
    private function testAdminCheck(): void
    {
        // Create admin user
        $adminId = $this->userModel->create([
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin@test.com',
            'password' => 'password123',
            'role' => 'admin',
            'is_active' => true
        ]);
        $this->testData['users'][] = $adminId;
        
        // Create regular user
        $userId = $this->userModel->create([
            'first_name' => 'Regular',
            'last_name' => 'User',
            'email' => 'user@test.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true
        ]);
        $this->testData['users'][] = $userId;
        
        // Test admin user
        Auth::login('admin@test.com', 'password123');
        $this->assert(Auth::isAdmin(), 'Admin user should be identified as admin');
        
        // Test regular user
        Auth::login('user@test.com', 'password123');
        $this->assert(!Auth::isAdmin(), 'Regular user should not be identified as admin');
        
        // Test when not logged in
        Auth::logout();
        $this->assert(!Auth::isAdmin(), 'Unauthenticated user should not be admin');
    }
    
    /**
     * Test page permissions
     */
    private function testPagePermissions(): void
    {
        // Create test user and page
        $userId = $this->userModel->create([
            'first_name' => 'Permission',
            'last_name' => 'Test',
            'email' => 'permission@test.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true
        ]);
        $this->testData['users'][] = $userId;
        
        $pageId = $this->pageModel->create([
            'title' => 'Permission Test Page',
            'slug' => 'permission-test-' . time(),
            'description' => 'For testing permissions',
            'is_active' => true,
            'created_by' => 1
        ]);
        $this->testData['pages'][] = $pageId;
        
        // Login user
        Auth::login('permission@test.com', 'password123');
        
        // Test without permission
        $this->assert(!Auth::hasPagePermission($pageId), 'User should not have permission initially');
        
        // Add permission
        $this->db->execute(
            "INSERT INTO page_permissions (page_id, user_id, permission_type) VALUES (?, ?, ?)",
            [$pageId, $userId, 'read']
        );
        
        // Clear cached permissions
        unset($_SESSION['user_permissions']);
        
        // Test with permission
        $this->assert(Auth::hasPagePermission($pageId), 'User should have permission after adding');
        
        // Test accessible pages
        $accessiblePages = Auth::getAccessiblePages();
        $this->assert(count($accessiblePages) >= 1, 'User should have at least one accessible page');
        
        $found = false;
        foreach ($accessiblePages as $page) {
            if ($page['id'] === $pageId) {
                $found = true;
                break;
            }
        }
        $this->assert($found, 'Page should be in accessible pages list');
        
        Auth::logout();
    }
    
    /**
     * Test session management
     */
    private function testSessionManagement(): void
    {
        // Create test user
        $userId = $this->userModel->create([
            'first_name' => 'Session',
            'last_name' => 'Test',
            'email' => 'session@test.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true
        ]);
        $this->testData['users'][] = $userId;
        
        // Test session creation
        Auth::login('session@test.com', 'password123');
        $this->assert(isset($_SESSION['user_id']), 'Session should contain user_id');
        $this->assert($_SESSION['user_id'] === $userId, 'Session user_id should match');
        
        // Test session persistence
        $sessionUserId = $_SESSION['user_id'];
        $this->assert(Auth::id() === $sessionUserId, 'Auth::id() should return session user_id');
        
        // Test session cleanup on logout
        Auth::logout();
        $this->assert(!isset($_SESSION['user_id']), 'Session should not contain user_id after logout');
    }
    
    /**
     * Test remember me functionality
     */
    private function testRememberMe(): void
    {
        // Create test user
        $userId = $this->userModel->create([
            'first_name' => 'Remember',
            'last_name' => 'Test',
            'email' => 'remember@test.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true
        ]);
        $this->testData['users'][] = $userId;
        
        // Test login with remember me
        $loginResult = Auth::login('remember@test.com', 'password123', true);
        $this->assert($loginResult, 'Login with remember me should succeed');
        
        // Check remember token
        $user = $this->userModel->find($userId);
        $this->assert(!empty($user['remember_token']), 'Remember token should be set');
        
        // Logout and clear session
        Auth::logout();
        session_destroy();
        session_start();
        
        // Simulate returning with remember token cookie
        $_COOKIE['remember_token'] = $user['remember_token'];
        
        // Check if user is automatically logged in
        $rememberResult = Auth::checkRememberToken();
        $this->assert($rememberResult, 'Remember token should log user in');
        $this->assert(Auth::check(), 'User should be authenticated via remember token');
        
        // Clean up cookie
        unset($_COOKIE['remember_token']);
        Auth::logout();
    }
    
    /**
     * Test CSRF protection
     */
    private function testCSRFProtection(): void
    {
        // Test CSRF token generation
        $token1 = \App\Core\CSRF::generate();
        $this->assert(!empty($token1), 'CSRF token should not be empty');
        $this->assert(strlen($token1) >= 32, 'CSRF token should be at least 32 characters');
        
        // Test token persistence
        $token2 = \App\Core\CSRF::generate();
        $this->assert($token1 === $token2, 'CSRF token should persist across calls');
        
        // Test token validation
        $this->assert(\App\Core\CSRF::verify($token1), 'Valid CSRF token should verify');
        $this->assert(!\App\Core\CSRF::verify('invalid_token'), 'Invalid CSRF token should not verify');
        $this->assert(!\App\Core\CSRF::verify(''), 'Empty CSRF token should not verify');
        
        // Test field generation
        $field = \App\Core\CSRF::field();
        $this->assert(strpos($field, 'csrf_token') !== false, 'CSRF field should contain token name');
        $this->assert(strpos($field, $token1) !== false, 'CSRF field should contain token value');
    }
    
    /**
     * Clean up test data
     */
    private function cleanup(): void
    {
        // Clean up pages and permissions
        if (isset($this->testData['pages'])) {
            foreach ($this->testData['pages'] as $pageId) {
                try {
                    $this->db->execute("DELETE FROM page_permissions WHERE page_id = ?", [$pageId]);
                    $this->pageModel->delete($pageId);
                } catch (Exception $e) {
                    // Ignore cleanup errors
                }
            }
        }
        
        // Clean up users
        if (isset($this->testData['users'])) {
            foreach ($this->testData['users'] as $userId) {
                try {
                    $this->db->execute("DELETE FROM page_permissions WHERE user_id = ?", [$userId]);
                    $this->userModel->delete($userId);
                } catch (Exception $e) {
                    // Ignore cleanup errors
                }
            }
        }
        
        // Clear session
        Auth::logout();
    }
    
    /**
     * Assert condition
     */
    private function assert(bool $condition, string $message): void
    {
        if (!$condition) {
            throw new Exception($message);
        }
    }
}

// Run tests if called directly
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $test = new AuthTest();
    $test->runTests();
}