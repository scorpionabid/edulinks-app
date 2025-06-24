<?php
/**
 * EduLinks Test Runner
 * 
 * Runs all tests for the EduLinks system
 */

require_once '../app/includes/bootstrap.php';

class TestRunner
{
    private array $testResults = [];
    private int $totalTests = 0;
    private int $totalPassed = 0;
    private int $totalFailed = 0;
    
    public function __construct()
    {
        echo "🧪 EduLinks Test Suite\n";
        echo "=====================\n\n";
    }
    
    /**
     * Run all test suites
     */
    public function runAllTests(): void
    {
        $testSuites = [
            'DatabaseTest' => 'Database & Models',
            'AuthTest' => 'Authentication & Authorization',
            'ApiTest' => 'API Endpoints'
        ];
        
        foreach ($testSuites as $className => $description) {
            $this->runTestSuite($className, $description);
        }
        
        $this->printSummary();
    }
    
    /**
     * Run individual test suite
     */
    private function runTestSuite(string $className, string $description): void
    {
        echo "📋 Running $description Tests\n";
        echo str_repeat('-', 50) . "\n";
        
        try {
            // Capture output
            ob_start();
            
            require_once __DIR__ . "/{$className}.php";
            $testClass = new $className();
            $testClass->runTests();
            
            $output = ob_get_clean();
            
            // Parse results from output
            $this->parseTestResults($output, $className);
            
            echo $output . "\n";
            
        } catch (Throwable $e) {
            ob_end_clean();
            echo "❌ FATAL ERROR in $className: " . $e->getMessage() . "\n\n";
            $this->testResults[$className] = [
                'passed' => 0,
                'failed' => 1,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Parse test results from output
     */
    private function parseTestResults(string $output, string $className): void
    {
        $passed = 0;
        $failed = 0;
        
        // Count passed tests
        preg_match('/✅ Passed: (\d+)/', $output, $passedMatches);
        if (isset($passedMatches[1])) {
            $passed = (int)$passedMatches[1];
        }
        
        // Count failed tests
        preg_match('/❌ Failed: (\d+)/', $output, $failedMatches);
        if (isset($failedMatches[1])) {
            $failed = (int)$failedMatches[1];
        }
        
        $this->testResults[$className] = [
            'passed' => $passed,
            'failed' => $failed
        ];
        
        $this->totalPassed += $passed;
        $this->totalFailed += $failed;
        $this->totalTests += ($passed + $failed);
    }
    
    /**
     * Print test summary
     */
    private function printSummary(): void
    {
        echo "📊 OVERALL TEST SUMMARY\n";
        echo "======================\n\n";
        
        foreach ($this->testResults as $className => $results) {
            $total = $results['passed'] + $results['failed'];
            $successRate = $total > 0 ? round(($results['passed'] / $total) * 100, 1) : 0;
            
            echo sprintf(
                "%-20s | %2d passed | %2d failed | %5.1f%% success\n",
                $className,
                $results['passed'],
                $results['failed'],
                $successRate
            );
            
            if (isset($results['error'])) {
                echo "                     | ERROR: " . $results['error'] . "\n";
            }
        }
        
        echo "\n" . str_repeat('-', 60) . "\n";
        
        $overallSuccess = $this->totalTests > 0 ? 
            round(($this->totalPassed / $this->totalTests) * 100, 1) : 0;
        
        echo sprintf(
            "%-20s | %2d passed | %2d failed | %5.1f%% success\n",
            "TOTAL",
            $this->totalPassed,
            $this->totalFailed,
            $overallSuccess
        );
        
        echo "\n";
        
        if ($this->totalFailed === 0) {
            echo "🎉 ALL TESTS PASSED! The system is ready for deployment.\n";
        } elseif ($overallSuccess >= 80) {
            echo "⚠️  Most tests passed, but some issues need attention.\n";
        } else {
            echo "❌ Many tests failed. Please fix issues before deployment.\n";
        }
        
        echo "\n📈 Coverage Areas Tested:\n";
        echo "- Database connectivity and CRUD operations\n";
        echo "- User authentication and authorization\n";
        echo "- Permission system\n";
        echo "- Session management\n";
        echo "- CSRF protection\n";
        echo "- API endpoints\n";
        echo "- Data validation\n";
        echo "- Search functionality\n";
        echo "- File operations\n";
        
        echo "\n🔍 To run individual test suites:\n";
        echo "php tests/DatabaseTest.php\n";
        echo "php tests/AuthTest.php\n";
        echo "php tests/ApiTest.php\n";
    }
    
    /**
     * Check system requirements
     */
    public function checkRequirements(): void
    {
        echo "🔧 Checking System Requirements\n";
        echo "==============================\n\n";
        
        $requirements = [
            'PHP Version >= 8.1' => version_compare(PHP_VERSION, '8.1.0', '>='),
            'PDO Extension' => extension_loaded('pdo'),
            'PDO PostgreSQL' => extension_loaded('pdo_pgsql'),
            'Session Support' => function_exists('session_start'),
            'JSON Support' => function_exists('json_encode'),
            'Multibyte String' => extension_loaded('mbstring'),
            'File Info' => extension_loaded('fileinfo'),
            'GD Library' => extension_loaded('gd'),
            'OpenSSL' => extension_loaded('openssl')
        ];
        
        $allMet = true;
        
        foreach ($requirements as $requirement => $met) {
            $status = $met ? '✅ OK' : '❌ MISSING';
            echo sprintf("%-25s: %s\n", $requirement, $status);
            
            if (!$met) {
                $allMet = false;
            }
        }
        
        echo "\n";
        
        if ($allMet) {
            echo "✅ All system requirements are met!\n";
        } else {
            echo "❌ Some requirements are missing. Please install missing extensions.\n";
        }
        
        // Check directory permissions
        echo "\n📁 Checking Directory Permissions\n";
        echo "---------------------------------\n";
        
        $directories = [
            '../storage/logs' => 'Logs directory',
            '../public/uploads' => 'Uploads directory',
            '../storage/cache' => 'Cache directory',
            '../storage/sessions' => 'Sessions directory'
        ];
        
        foreach ($directories as $dir => $description) {
            if (!is_dir($dir)) {
                echo sprintf("%-20s: ❌ MISSING (needs to be created)\n", $description);
            } elseif (!is_writable($dir)) {
                echo sprintf("%-20s: ❌ NOT WRITABLE\n", $description);
            } else {
                echo sprintf("%-20s: ✅ OK\n", $description);
            }
        }
        
        echo "\n";
    }
    
    /**
     * Check database connection
     */
    public function checkDatabase(): void
    {
        echo "🗃️  Checking Database Connection\n";
        echo "===============================\n\n";
        
        try {
            $db = \App\Core\Database::getInstance();
            $result = $db->query("SELECT version()")->fetch();
            
            echo "✅ Database connection successful\n";
            echo "📊 PostgreSQL Version: " . $result['version'] . "\n";
            
            // Check if tables exist
            $tables = [
                'users' => 'Users table',
                'pages' => 'Pages table', 
                'links' => 'Links table',
                'page_permissions' => 'Page permissions table'
            ];
            
            echo "\n📋 Checking Tables:\n";
            foreach ($tables as $table => $description) {
                $exists = $db->query(
                    "SELECT EXISTS (SELECT 1 FROM information_schema.tables WHERE table_name = ?)",
                    [$table]
                )->fetch()['exists'];
                
                $status = $exists ? '✅ EXISTS' : '❌ MISSING';
                echo sprintf("%-25s: %s\n", $description, $status);
            }
            
        } catch (Exception $e) {
            echo "❌ Database connection failed: " . $e->getMessage() . "\n";
            echo "\n💡 To fix this:\n";
            echo "1. Make sure PostgreSQL is running\n";
            echo "2. Check database credentials in .env file\n";
            echo "3. Run database installation: psql -d your_db -f database/install.sql\n";
        }
        
        echo "\n";
    }
}

// Main execution
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $runner = new TestRunner();
    
    // Check if --requirements flag is passed
    if (in_array('--requirements', $argv ?? [])) {
        $runner->checkRequirements();
        $runner->checkDatabase();
        exit(0);
    }
    
    // Check if --db flag is passed
    if (in_array('--db', $argv ?? [])) {
        $runner->checkDatabase();
        exit(0);
    }
    
    // Run requirements check first
    $runner->checkRequirements();
    $runner->checkDatabase();
    
    echo "\n";
    
    // Run all tests
    $runner->runAllTests();
}