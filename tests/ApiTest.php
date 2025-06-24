<?php
/**
 * EduLinks API Tests
 * 
 * Basic API endpoint tests
 */

require_once '../app/includes/bootstrap.php';

use App\Core\Database;
use App\Core\Auth;
use App\Models\User;
use App\Models\Page;
use App\Models\Link;

class ApiTest
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
        echo "🧪 EduLinks API Tests\n";
        echo "==================\n\n";
        
        $this->setupTestData();
        
        $tests = [
            'testUserPagesEndpoint',
            'testUserStatsEndpoint',
            'testSearchEndpoint',
            'testLinkDetailsEndpoint',
            'testSystemHealthEndpoint',
            'testFileUploadValidation'
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
        
        $this->cleanupTestData();
    }
    
    /**
     * Setup test data
     */
    private function setupTestData(): void
    {
        // Create test user
        $testUser = $this->userModel->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@edulinks.test',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true
        ]);
        $this->testData['user_id'] = $testUser;
        
        // Create test page
        $testPage = $this->pageModel->create([
            'title' => 'Test Page',
            'slug' => 'test-page-' . time(),
            'description' => 'Test page description',
            'color' => '#007bff',
            'icon' => 'fas fa-test',
            'is_active' => true,
            'created_by' => 1
        ]);
        $this->testData['page_id'] = $testPage;
        
        // Create test link
        $testLink = $this->linkModel->create([
            'title' => 'Test Link',
            'description' => 'Test link description',
            'url' => 'https://example.com',
            'type' => 'url',
            'page_id' => $testPage,
            'sort_order' => 1,
            'is_active' => true,
            'is_featured' => false,
            'created_by' => 1
        ]);
        $this->testData['link_id'] = $testLink;
        
        // Give user permission to page
        $this->db->execute(
            "INSERT INTO page_permissions (page_id, user_id, permission_type) VALUES (?, ?, ?)",
            [$testPage, $testUser, 'read']
        );
    }
    
    /**
     * Cleanup test data
     */
    private function cleanupTestData(): void
    {
        if (isset($this->testData['link_id'])) {
            $this->linkModel->delete($this->testData['link_id']);
        }
        
        if (isset($this->testData['page_id'])) {
            $this->db->execute(
                "DELETE FROM page_permissions WHERE page_id = ?",
                [$this->testData['page_id']]
            );
            $this->pageModel->delete($this->testData['page_id']);
        }
        
        if (isset($this->testData['user_id'])) {
            $this->userModel->delete($this->testData['user_id']);
        }
    }
    
    /**
     * Test user pages endpoint
     */
    private function testUserPagesEndpoint(): void
    {
        // Simulate authenticated user
        $_SESSION['user_id'] = $this->testData['user_id'];
        
        $controller = new \App\Controllers\ApiController();
        
        ob_start();
        $controller->getUserPages();
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        if (!$response['success']) {
            throw new Exception('API returned error: ' . $response['error']);
        }
        
        if (empty($response['data']['pages'])) {
            throw new Exception('No pages returned');
        }
        
        $page = $response['data']['pages'][0];
        if ($page['id'] !== $this->testData['page_id']) {
            throw new Exception('Wrong page returned');
        }
        
        unset($_SESSION['user_id']);
    }
    
    /**
     * Test user stats endpoint
     */
    private function testUserStatsEndpoint(): void
    {
        $_SESSION['user_id'] = $this->testData['user_id'];
        
        $controller = new \App\Controllers\ApiController();
        
        ob_start();
        $controller->getUserStats();
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        if (!$response['success']) {
            throw new Exception('API returned error: ' . $response['error']);
        }
        
        $data = $response['data'];
        if (!isset($data['accessible_pages']) || !isset($data['total_links'])) {
            throw new Exception('Missing required stats fields');
        }
        
        if ($data['accessible_pages'] < 1) {
            throw new Exception('No accessible pages found');
        }
        
        unset($_SESSION['user_id']);
    }
    
    /**
     * Test search endpoint
     */
    private function testSearchEndpoint(): void
    {
        $_SESSION['user_id'] = $this->testData['user_id'];
        $_GET['q'] = 'Test';
        
        $controller = new \App\Controllers\ApiController();
        
        ob_start();
        $controller->searchLinks();
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        if (!$response['success']) {
            throw new Exception('API returned error: ' . $response['error']);
        }
        
        $data = $response['data'];
        if ($data['query'] !== 'Test') {
            throw new Exception('Wrong query in response');
        }
        
        unset($_SESSION['user_id']);
        unset($_GET['q']);
    }
    
    /**
     * Test link details endpoint
     */
    private function testLinkDetailsEndpoint(): void
    {
        $_SESSION['user_id'] = $this->testData['user_id'];
        
        $controller = new \App\Controllers\ApiController();
        
        ob_start();
        $controller->getLinkDetails((string)$this->testData['link_id']);
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        if (!$response['success']) {
            throw new Exception('API returned error: ' . $response['error']);
        }
        
        $link = $response['data']['link'];
        if ($link['id'] !== $this->testData['link_id']) {
            throw new Exception('Wrong link returned');
        }
        
        if ($link['title'] !== 'Test Link') {
            throw new Exception('Wrong link title');
        }
        
        unset($_SESSION['user_id']);
    }
    
    /**
     * Test system health endpoint (admin required)
     */
    private function testSystemHealthEndpoint(): void
    {
        // Create admin user for this test
        $adminUser = $this->userModel->create([
            'first_name' => 'Admin',
            'last_name' => 'Test',
            'email' => 'admin@edulinks.test',
            'password' => 'password123',
            'role' => 'admin',
            'is_active' => true
        ]);
        
        $_SESSION['user_id'] = $adminUser;
        
        $controller = new \App\Controllers\ApiController();
        
        ob_start();
        $controller->getSystemHealth();
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        if (!$response['success']) {
            throw new Exception('API returned error: ' . $response['error']);
        }
        
        $data = $response['data'];
        if (!isset($data['status']) || !isset($data['database']) || !isset($data['storage'])) {
            throw new Exception('Missing health check fields');
        }
        
        if ($data['database']['status'] !== 'ok') {
            throw new Exception('Database health check failed');
        }
        
        unset($_SESSION['user_id']);
        $this->userModel->delete($adminUser);
    }
    
    /**
     * Test file upload validation
     */
    private function testFileUploadValidation(): void
    {
        $_SESSION['user_id'] = $this->testData['user_id'];
        
        $controller = new \App\Controllers\FileController();
        
        // Test without file
        ob_start();
        $controller->upload();
        $output = ob_get_clean();
        
        $response = json_decode($output, true);
        
        if ($response['success'] !== false) {
            throw new Exception('Upload should fail without file');
        }
        
        if (strpos($response['error'], 'seçilmədi') === false) {
            throw new Exception('Wrong error message for missing file');
        }
        
        unset($_SESSION['user_id']);
    }
    
    /**
     * Simulate HTTP request
     */
    private function makeApiRequest(string $endpoint, string $method = 'GET', array $data = []): array
    {
        $url = 'http://localhost' . $endpoint;
        
        $options = [
            'http' => [
                'method' => $method,
                'header' => 'Content-Type: application/json',
                'content' => json_encode($data)
            ]
        ];
        
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        
        return json_decode($result, true);
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
    $test = new ApiTest();
    $test->runTests();
}