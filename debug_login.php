<?php
require_once '/var/www/html/app/includes/bootstrap.php';

use App\Core\Auth;
use App\Models\User;

echo "Testing login functionality:\n";

$userModel = new User();

echo "Testing findByEmailForAuth method:\n";
try {
    $user = $userModel->findByEmailForAuth('admin@edulinks.az');
    echo "Method executed successfully\n";
} catch (Exception $e) {
    echo "Method failed: " . $e->getMessage() . "\n";
    $user = null;
}

if ($user) {
    echo "User found: " . $user['email'] . "\n";
    echo "Password field exists: " . (isset($user['password']) ? 'YES' : 'NO') . "\n";
    echo "User is active: " . ($user['is_active'] ? 'YES' : 'NO') . "\n";
    
    $verifyResult = Auth::verifyPassword('password', $user['password']);
    echo "Password verify: " . ($verifyResult ? 'SUCCESS' : 'FAILED') . "\n";
    
    $loginResult = Auth::login('admin@edulinks.az', 'password');
    echo "Login result: " . ($loginResult ? 'SUCCESS' : 'FAILED') . "\n";
    
    if ($loginResult) {
        echo "Auth check: " . (Auth::check() ? 'AUTHENTICATED' : 'NOT AUTHENTICATED') . "\n";
        echo "User ID: " . Auth::id() . "\n";
        echo "Is Admin: " . (Auth::isAdmin() ? 'YES' : 'NO') . "\n";
    }
} else {
    echo "User not found!\n";
}

echo "\nTesting user account:\n";
$regularUser = $userModel->findByEmail('user@edulinks.az');
if ($regularUser) {
    echo "Regular user found: " . $regularUser['email'] . "\n";
    $userLoginResult = Auth::login('user@edulinks.az', 'password');
    echo "User login result: " . ($userLoginResult ? 'SUCCESS' : 'FAILED') . "\n";
} else {
    echo "Regular user not found!\n";
}
?>