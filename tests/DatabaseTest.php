<?php
/**
 * EduLinks Database Tests
 * 
 * Test database operations and models
 */

require_once '../app/includes/bootstrap.php';

use App\Core\Database;
use App\Models\User;
use App\Models\Page;
use App\Models\Link;

class DatabaseTest
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
     * Run all tests
     */
    public function runTests(): void
    {
        echo "🗃️  EduLinks Database Tests\n";
        echo "========================\n\n";
        
        $tests = [
            'testDatabaseConnection',
            'testUserCRUD',
            'testPageCRUD',
            'testLinkCRUD',
            'testPermissions',
            'testDataIntegrity',
            'testModelValidation',
            'testSearchFunctionality'
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
     * Test database connection
     */
    private function testDatabaseConnection(): void
    {
        $result = $this->db->query("SELECT 1 as test")->fetch();
        $this->assert($result['test'] === '1', 'Database connection failed');
    }
    
    /**
     * Test User CRUD operations
     */
    private function testUserCRUD(): void
    {
        // Create user
        $userId = $this->userModel->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true
        ]);
        
        $this->assert($userId > 0, 'User creation failed');
        $this->testData['users'][] = $userId;
        
        // Read user
        $user = $this->userModel->find($userId);
        $this->assert($user['first_name'] === 'Test', 'User read failed');
        $this->assert(!empty($user['password']), 'Password should be hashed');
        
        // Update user
        $this->userModel->update($userId, [
            'first_name' => 'Updated',
            'last_name' => 'Name'
        ]);
        
        $updatedUser = $this->userModel->find($userId);
        $this->assert($updatedUser['first_name'] === 'Updated', 'User update failed');
        
        // Test pagination
        $paginatedUsers = $this->userModel->paginate(1, 10);
        $this->assert(isset($paginatedUsers['data']), 'Pagination failed');
        $this->assert(isset($paginatedUsers['total']), 'Pagination total missing');
    }
    
    /**
     * Test Page CRUD operations
     */
    private function testPageCRUD(): void
    {
        // Create page
        $pageId = $this->pageModel->create([
            'title' => 'Test Page',
            'slug' => 'test-page-' . time(),
            'description' => 'Test description',
            'color' => '#007bff',
            'icon' => 'fas fa-test',
            'is_active' => true,
            'created_by' => 1
        ]);
        
        $this->assert($pageId > 0, 'Page creation failed');
        $this->testData['pages'][] = $pageId;
        
        // Read page
        $page = $this->pageModel->find($pageId);
        $this->assert($page['title'] === 'Test Page', 'Page read failed');
        
        // Test findBySlug
        $pageBySlug = $this->pageModel->findBySlug($page['slug']);
        $this->assert($pageBySlug['id'] === $pageId, 'Find by slug failed');
        
        // Update page
        $this->pageModel->update($pageId, [
            'title' => 'Updated Page'
        ]);
        
        $updatedPage = $this->pageModel->find($pageId);
        $this->assert($updatedPage['title'] === 'Updated Page', 'Page update failed');
    }
    
    /**
     * Test Link CRUD operations
     */
    private function testLinkCRUD(): void
    {
        // Need a page first
        $pageId = $this->pageModel->create([
            'title' => 'Link Test Page',
            'slug' => 'link-test-' . time(),
            'description' => 'For testing links',
            'is_active' => true,
            'created_by' => 1
        ]);
        $this->testData['pages'][] = $pageId;
        
        // Create URL link
        $linkId = $this->linkModel->create([
            'title' => 'Test Link',
            'description' => 'Test description',
            'url' => 'https://example.com',
            'type' => 'url',
            'page_id' => $pageId,
            'sort_order' => 1,
            'is_active' => true,
            'is_featured' => false,
            'created_by' => 1
        ]);
        
        $this->assert($linkId > 0, 'Link creation failed');
        $this->testData['links'][] = $linkId;
        
        // Read link
        $link = $this->linkModel->find($linkId);
        $this->assert($link['title'] === 'Test Link', 'Link read failed');
        
        // Test getLinkWithDetails
        $linkWithDetails = $this->linkModel->getLinkWithDetails($linkId);
        $this->assert(isset($linkWithDetails['page_title']), 'Link details missing page info');
        
        // Test getPageLinks
        $pageLinks = $this->linkModel->getPageLinks($pageId);
        $this->assert(count($pageLinks) === 1, 'Page links count incorrect');
        
        // Test incrementClicks
        $this->linkModel->incrementClicks($linkId);
        $updatedLink = $this->linkModel->find($linkId);
        $this->assert($updatedLink['click_count'] === 1, 'Click increment failed');
        
        // Test formatForDisplay
        $formatted = $this->linkModel->formatForDisplay($link);
        $this->assert(isset($formatted['status_class']), 'Format for display missing status class');
    }
    
    /**
     * Test permissions system
     */
    private function testPermissions(): void
    {
        // Create test user and page
        $userId = $this->userModel->create([
            'first_name' => 'Permission',
            'last_name' => 'Test',
            'email' => 'permission@example.com',
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
        
        // Add permission
        $this->db->execute(
            "INSERT INTO page_permissions (page_id, user_id, permission_type) VALUES (?, ?, ?)",
            [$pageId, $userId, 'read']
        );
        
        // Test permission exists
        $permission = $this->db->query(
            "SELECT * FROM page_permissions WHERE page_id = ? AND user_id = ?",
            [$pageId, $userId]
        )->fetch();
        
        $this->assert($permission !== false, 'Permission creation failed');
        $this->assert($permission['permission_type'] === 'read', 'Permission type incorrect');
    }
    
    /**
     * Test data integrity
     */
    private function testDataIntegrity(): void
    {
        // Test foreign key constraints
        try {
            // Try to create link with non-existent page
            $this->linkModel->create([
                'title' => 'Invalid Link',
                'url' => 'https://example.com',
                'type' => 'url',
                'page_id' => 99999, // Non-existent page
                'sort_order' => 1,
                'is_active' => true,
                'created_by' => 1
            ]);
            
            throw new Exception('Should have failed due to foreign key constraint');
        } catch (PDOException $e) {
            // This is expected - foreign key constraint should prevent this
            $this->assert(true, 'Foreign key constraint working');
        }
        
        // Test unique constraints
        try {
            // Try to create two users with same email
            $this->userModel->create([
                'first_name' => 'Test1',
                'last_name' => 'User1',
                'email' => 'duplicate@example.com',
                'password' => 'password123',
                'role' => 'user',
                'is_active' => true
            ]);
            
            $this->userModel->create([
                'first_name' => 'Test2',
                'last_name' => 'User2',
                'email' => 'duplicate@example.com', // Same email
                'password' => 'password123',
                'role' => 'user',
                'is_active' => true
            ]);
            
            throw new Exception('Should have failed due to unique constraint');
        } catch (PDOException $e) {
            // This is expected - unique constraint should prevent this
            $this->assert(true, 'Unique constraint working');
        }
    }
    
    /**
     * Test model validation
     */
    private function testModelValidation(): void
    {
        // Test User model validation
        $userValidation = $this->userModel->validate([
            'first_name' => '',
            'last_name' => 'Test',
            'email' => 'invalid-email',
            'password' => '123' // Too short
        ]);
        
        $this->assert(!$userValidation['valid'], 'User validation should fail');
        $this->assert(isset($userValidation['errors']['first_name']), 'First name error missing');
        $this->assert(isset($userValidation['errors']['email']), 'Email error missing');
        $this->assert(isset($userValidation['errors']['password']), 'Password error missing');
        
        // Test valid user data
        $validUserValidation = $this->userModel->validate([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123'
        ]);
        
        $this->assert($validUserValidation['valid'], 'Valid user data should pass validation');
    }
    
    /**
     * Test search functionality
     */
    private function testSearchFunctionality(): void
    {
        // Create test data for searching
        $pageId = $this->pageModel->create([
            'title' => 'Search Test Page',
            'slug' => 'search-test-' . time(),
            'description' => 'For testing search',
            'is_active' => true,
            'created_by' => 1
        ]);
        $this->testData['pages'][] = $pageId;
        
        $linkId1 = $this->linkModel->create([
            'title' => 'Mathematics Document',
            'description' => 'Advanced calculus materials',
            'url' => 'https://example.com/math',
            'type' => 'url',
            'page_id' => $pageId,
            'sort_order' => 1,
            'is_active' => true,
            'created_by' => 1
        ]);
        $this->testData['links'][] = $linkId1;
        
        $linkId2 = $this->linkModel->create([
            'title' => 'Physics Notes',
            'description' => 'Quantum physics fundamentals',
            'url' => 'https://example.com/physics',
            'type' => 'url',
            'page_id' => $pageId,
            'sort_order' => 2,
            'is_active' => true,
            'created_by' => 1
        ]);
        $this->testData['links'][] = $linkId2;
        
        // Test search
        $searchResults = $this->linkModel->searchLinks('mathematics', 10);
        $this->assert(count($searchResults) >= 1, 'Search should find mathematics link');
        
        $foundMath = false;
        foreach ($searchResults as $result) {
            if ($result['id'] === $linkId1) {
                $foundMath = true;
                break;
            }
        }
        $this->assert($foundMath, 'Mathematics link should be in search results');
        
        // Test search with no results
        $noResults = $this->linkModel->searchLinks('nonexistent', 10);
        $this->assert(count($noResults) === 0, 'Search should return no results for nonexistent term');
    }
    
    /**
     * Clean up test data
     */
    private function cleanup(): void
    {
        // Delete links
        if (isset($this->testData['links'])) {
            foreach ($this->testData['links'] as $linkId) {
                try {
                    $this->linkModel->delete($linkId);
                } catch (Exception $e) {
                    // Ignore errors during cleanup
                }
            }
        }
        
        // Delete pages
        if (isset($this->testData['pages'])) {
            foreach ($this->testData['pages'] as $pageId) {
                try {
                    $this->db->execute("DELETE FROM page_permissions WHERE page_id = ?", [$pageId]);
                    $this->pageModel->delete($pageId);
                } catch (Exception $e) {
                    // Ignore errors during cleanup
                }
            }
        }
        
        // Delete users
        if (isset($this->testData['users'])) {
            foreach ($this->testData['users'] as $userId) {
                try {
                    $this->db->execute("DELETE FROM page_permissions WHERE user_id = ?", [$userId]);
                    $this->userModel->delete($userId);
                } catch (Exception $e) {
                    // Ignore errors during cleanup
                }
            }
        }
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
    $test = new DatabaseTest();
    $test->runTests();
}