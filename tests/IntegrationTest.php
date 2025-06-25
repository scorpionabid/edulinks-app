<?php
/**
 * EduLinks Integration Tests
 * 
 * Test complete user workflows and system integration
 */

require_once dirname(__DIR__) . '/app/includes/bootstrap.php';

use App\Core\Database;
use App\Core\Auth;
use App\Models\User;
use App\Models\Page;
use App\Models\Link;

class IntegrationTest
{
    private Database $db;
    private User $userModel;
    private Page $pageModel;
    private Link $linkModel;
    private array $testData = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->userModel = new User();
        $this->pageModel = new Page();
        $this->linkModel = new Link();
    }
    
    /**
     * Run all integration tests
     */
    public function runTests(): void
    {
        echo "🔄 EduLinks Integration Tests\n";
        echo "============================\n\n";
        
        $tests = [
            'testCompleteUserWorkflow',
            'testAdminWorkflow',
            'testPermissionsWorkflow',
            'testFileUploadWorkflow',
            'testSearchWorkflow',
            'testStatisticsWorkflow',
            'testErrorHandling',
            'testSecurityFeatures'
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
        
        echo "\n📊 Integration Test Results:\n";
        echo "✅ Passed: $passed\n";
        echo "❌ Failed: $failed\n";
        echo "📈 Success Rate: " . round(($passed / ($passed + $failed)) * 100, 2) . "%\n";
        
        $this->cleanup();
    }
    
    /**
     * Test complete user workflow
     */
    private function testCompleteUserWorkflow(): void
    {
        // 1. User registration simulation
        $userId = $this->userModel->create([
            'first_name' => 'Integration',
            'last_name' => 'Test',
            'email' => 'integration@test.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true
        ]);
        $this->testData['users'][] = $userId;
        
        // 2. User login
        $loginSuccess = Auth::login('integration@test.com', 'password123');
        $this->assert($loginSuccess, 'User login should succeed');
        $this->assert(Auth::check(), 'User should be authenticated');
        
        // 3. Check accessible pages (should be empty initially)
        $accessiblePages = Auth::getAccessiblePages();
        $this->assert(is_array($accessiblePages), 'Accessible pages should return array');
        
        // 4. Create a test page for permissions
        $pageId = $this->pageModel->create([
            'title' => 'Test Integration Page',
            'slug' => 'test-integration-' . time(),
            'description' => 'For integration testing',
            'is_active' => true,
            'created_by' => 1
        ]);
        $this->testData['pages'][] = $pageId;
        
        // 5. Grant user permission to page
        $this->db->execute(
            "INSERT INTO page_permissions (page_id, user_id, permission_type) VALUES (?, ?, ?)",
            [$pageId, $userId, 'read']
        );
        
        // 6. Check accessible pages again
        unset($_SESSION['user_permissions']); // Clear cache
        $accessiblePages = Auth::getAccessiblePages();
        $this->assert(count($accessiblePages) >= 1, 'User should now have accessible pages');
        
        // 7. Test page access
        $hasAccess = Auth::hasPagePermission($pageId);
        $this->assert($hasAccess, 'User should have access to granted page');
        
        // 8. User logout
        Auth::logout();
        $this->assert(!Auth::check(), 'User should be logged out');
    }
    
    /**
     * Test admin workflow
     */
    private function testAdminWorkflow(): void
    {
        // 1. Admin login
        $loginSuccess = Auth::login('admin@edulinks.az', 'password');
        $this->assert($loginSuccess, 'Admin login should succeed');
        $this->assert(Auth::isAdmin(), 'User should be identified as admin');
        
        // 2. Create new user (admin function)
        $newUserId = $this->userModel->create([
            'first_name' => 'Admin',
            'last_name' => 'Created',
            'email' => 'admin-created@test.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true
        ]);
        $this->testData['users'][] = $newUserId;
        
        // 3. Create new page
        $pageId = $this->pageModel->create([
            'title' => 'Admin Test Page',
            'slug' => 'admin-test-' . time(),
            'description' => 'Created by admin',
            'color' => '#28a745',
            'icon' => 'fas fa-test',
            'is_active' => true,
            'created_by' => Auth::id()
        ]);
        $this->testData['pages'][] = $pageId;
        
        // 4. Create links for the page
        $linkId = $this->linkModel->create([
            'title' => 'Admin Test Link',
            'description' => 'Test link created by admin',
            'url' => 'https://example.com/test',
            'type' => 'url',
            'page_id' => $pageId,
            'sort_order' => 1,
            'is_active' => true,
            'is_featured' => true,
            'created_by' => Auth::id()
        ]);
        $this->testData['links'][] = $linkId;
        
        // 5. Grant permissions to new user
        $this->db->execute(
            "INSERT INTO page_permissions (page_id, user_id, permission_type) VALUES (?, ?, ?)",
            [$pageId, $newUserId, 'read']
        );
        
        // 6. Test admin has access to all pages
        $allPages = $this->pageModel->all();
        foreach ($allPages as $page) {
            $this->assert(Auth::hasPagePermission($page['id']), 'Admin should have access to all pages');
        }
        
        Auth::logout();
    }
    
    /**
     * Test permissions workflow
     */
    private function testPermissionsWorkflow(): void
    {
        // Create two users
        $user1Id = $this->userModel->create([
            'first_name' => 'User',
            'last_name' => 'One',
            'email' => 'user1@test.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true
        ]);
        $this->testData['users'][] = $user1Id;
        
        $user2Id = $this->userModel->create([
            'first_name' => 'User',
            'last_name' => 'Two',
            'email' => 'user2@test.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true
        ]);
        $this->testData['users'][] = $user2Id;
        
        // Create page
        $pageId = $this->pageModel->create([
            'title' => 'Permission Test Page',
            'slug' => 'permission-test-' . time(),
            'description' => 'For testing permissions',
            'is_active' => true,
            'created_by' => 1
        ]);
        $this->testData['pages'][] = $pageId;
        
        // Grant permission only to user1
        $this->db->execute(
            "INSERT INTO page_permissions (page_id, user_id, permission_type) VALUES (?, ?, ?)",
            [$pageId, $user1Id, 'read']
        );
        
        // Test user1 access
        Auth::login('user1@test.com', 'password123');
        $this->assert(Auth::hasPagePermission($pageId), 'User1 should have access');
        Auth::logout();
        
        // Test user2 no access
        Auth::login('user2@test.com', 'password123');
        $this->assert(!Auth::hasPagePermission($pageId), 'User2 should not have access');
        Auth::logout();
    }
    
    /**
     * Test file upload workflow (simulated)
     */
    private function testFileUploadWorkflow(): void
    {
        // Login as admin
        Auth::login('admin@edulinks.az', 'password');
        
        // Create page for file upload
        $pageId = $this->pageModel->create([
            'title' => 'File Upload Test',
            'slug' => 'file-upload-test-' . time(),
            'description' => 'For testing file uploads',
            'is_active' => true,
            'created_by' => Auth::id()
        ]);
        $this->testData['pages'][] = $pageId;
        
        // Simulate file link creation
        $linkId = $this->linkModel->create([
            'title' => 'Test Document',
            'description' => 'A test document file',
            'type' => 'file',
            'file_name' => 'test-document.pdf',
            'file_path' => '/uploads/test/test-document.pdf',
            'file_size' => 1024000,
            'file_type' => 'application/pdf',
            'page_id' => $pageId,
            'sort_order' => 1,
            'is_active' => true,
            'created_by' => Auth::id()
        ]);
        $this->testData['links'][] = $linkId;
        
        // Test link formatting
        $link = $this->linkModel->find($linkId);
        $formattedLink = $this->linkModel->formatForDisplay($link);
        
        $this->assert($formattedLink['type'] === 'file', 'Link should be file type');
        $this->assert(isset($formattedLink['file_size_formatted']), 'File size should be formatted');
        $this->assert($formattedLink['file_extension'] === 'PDF', 'File extension should be detected');
        
        Auth::logout();
    }
    
    /**
     * Test search workflow
     */
    private function testSearchWorkflow(): void
    {
        // Create test data for search
        $pageId = $this->pageModel->create([
            'title' => 'Search Test Page',
            'slug' => 'search-test-' . time(),
            'description' => 'For testing search functionality',
            'is_active' => true,
            'created_by' => 1
        ]);
        $this->testData['pages'][] = $pageId;
        
        // Create searchable links
        $searchTerms = ['mathematics', 'physics', 'chemistry'];
        foreach ($searchTerms as $i => $term) {
            $linkId = $this->linkModel->create([
                'title' => ucfirst($term) . ' Course Materials',
                'description' => 'Educational materials for ' . $term,
                'url' => 'https://example.com/' . $term,
                'type' => 'url',
                'page_id' => $pageId,
                'sort_order' => $i + 1,
                'is_active' => true,
                'created_by' => 1
            ]);
            $this->testData['links'][] = $linkId;
        }
        
        // Test search functionality
        foreach ($searchTerms as $term) {
            $results = $this->linkModel->searchLinks($term, 10);
            $this->assert(count($results) >= 1, "Search for '$term' should return results");
            
            $found = false;
            foreach ($results as $result) {
                if (stripos($result['title'], $term) !== false || stripos($result['description'], $term) !== false) {
                    $found = true;
                    break;
                }
            }
            $this->assert($found, "Search results should contain '$term'");
        }
    }
    
    /**
     * Test statistics workflow
     */
    private function testStatisticsWorkflow(): void
    {
        Auth::login('admin@edulinks.az', 'password');
        
        // Get initial statistics
        $userStats = $this->userModel->getStatistics();
        $pageStats = $this->pageModel->getStatistics();
        $linkStats = $this->linkModel->getStatistics();
        
        $this->assert(is_array($userStats), 'User statistics should be array');
        $this->assert(isset($userStats['total']), 'User stats should have total');
        $this->assert($userStats['total'] >= 2, 'Should have at least 2 users');
        
        $this->assert(is_array($pageStats), 'Page statistics should be array');
        $this->assert(isset($pageStats['total']), 'Page stats should have total');
        
        $this->assert(is_array($linkStats), 'Link statistics should be array');
        $this->assert(isset($linkStats['total']), 'Link stats should have total');
        
        // Test click increment
        if (!empty($linkStats['total'])) {
            $firstLink = $this->linkModel->all()[0];
            $originalClicks = $firstLink['click_count'];
            
            $this->linkModel->incrementClicks($firstLink['id']);
            
            $updatedLink = $this->linkModel->find($firstLink['id']);
            $this->assert($updatedLink['click_count'] === $originalClicks + 1, 'Click count should increment');
        }
        
        Auth::logout();
    }
    
    /**
     * Test error handling
     */
    private function testErrorHandling(): void
    {
        // Test invalid login
        $loginResult = Auth::login('invalid@email.com', 'wrongpassword');
        $this->assert(!$loginResult, 'Invalid login should fail');
        
        // Test accessing non-existent page
        $nonExistentPage = $this->pageModel->find(99999);
        $this->assert(!$nonExistentPage, 'Non-existent page should return false');
        
        // Test accessing non-existent link
        $nonExistentLink = $this->linkModel->find(99999);
        $this->assert(!$nonExistentLink, 'Non-existent link should return false');
        
        // Test database constraint violations
        try {
            // Try to create user with duplicate email
            $this->userModel->create([
                'first_name' => 'Duplicate',
                'last_name' => 'Email',
                'email' => 'admin@edulinks.az', // Existing email
                'password' => 'password123',
                'role' => 'user',
                'is_active' => true
            ]);
            throw new Exception('Should have failed due to duplicate email');
        } catch (PDOException $e) {
            // Expected - unique constraint violation
            $this->assert(true, 'Duplicate email constraint working');
        }
    }
    
    /**
     * Test security features
     */
    private function testSecurityFeatures(): void
    {
        // Test CSRF token generation and validation
        $token1 = \App\Core\CSRF::generate();
        $token2 = \App\Core\CSRF::generate();
        $this->assert($token1 === $token2, 'CSRF tokens should be consistent in same session');
        $this->assert(\App\Core\CSRF::verify($token1), 'Valid CSRF token should verify');
        $this->assert(!\App\Core\CSRF::verify('invalid'), 'Invalid CSRF token should not verify');
        
        // Test password hashing
        $plainPassword = 'test123';
        $hashedPassword = Auth::hashPassword($plainPassword);
        $this->assert($hashedPassword !== $plainPassword, 'Password should be hashed');
        $this->assert(Auth::verifyPassword($plainPassword, $hashedPassword), 'Password verification should work');
        $this->assert(!Auth::verifyPassword('wrong', $hashedPassword), 'Wrong password should not verify');
        
        // Test session security
        Auth::login('admin@edulinks.az', 'password');
        $this->assert(isset($_SESSION['user_id']), 'Session should store user ID');
        $this->assert(!isset($_SESSION['password']), 'Session should not store password');
        
        Auth::logout();
        $this->assert(!isset($_SESSION['user_id']), 'Session should be cleared on logout');
    }
    
    /**
     * Clean up test data
     */
    private function cleanup(): void
    {
        // Clean up in reverse order due to foreign key constraints
        if (isset($this->testData['links'])) {
            foreach ($this->testData['links'] as $linkId) {
                try {
                    $this->linkModel->delete($linkId);
                } catch (Exception $e) {
                    // Ignore cleanup errors
                }
            }
        }
        
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
        
        // Clear any remaining session
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
    $test = new IntegrationTest();
    $test->runTests();
}