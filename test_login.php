<?php
/**
 * Test login functionality manually
 */

require_once 'app/includes/bootstrap.php';

use App\Core\Auth;
use App\Models\User;

echo "🧪 Testing Login Functionality\n";
echo "=============================\n\n";

try {
    // Test database connection
    $userModel = new User();
    $users = $userModel->all();
    echo "✅ Database connection: OK\n";
    echo "👥 Users in database: " . count($users) . "\n\n";
    
    // Test password verification
    $testUser = $userModel->findByEmail('admin@edulinks.az');
    if ($testUser) {
        echo "👤 Found admin user: " . $testUser['email'] . "\n";
        
        // Test password verification
        $passwordCheck = Auth::verifyPassword('password', $testUser['password']);
        echo "🔐 Password verification: " . ($passwordCheck ? "✅ OK" : "❌ FAILED") . "\n";
        
        // Test login
        $loginResult = Auth::login('admin@edulinks.az', 'password');
        echo "🚪 Login test: " . ($loginResult ? "✅ SUCCESS" : "❌ FAILED") . "\n";
        
        if ($loginResult) {
            echo "🆔 Logged in user ID: " . Auth::id() . "\n";
            echo "👑 Is admin: " . (Auth::isAdmin() ? "✅ YES" : "❌ NO") . "\n";
        }
        
    } else {
        echo "❌ Admin user not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n";
?>