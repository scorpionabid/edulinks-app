<?php
/**
 * EduLinks Main Entry Point
 * 
 * Routes all requests through the application
 */

require_once '../app/includes/bootstrap.php';

use App\Core\Router;
use App\Core\Auth;
use App\Core\View;

// Initialize router
$router = new Router();

// Set base path if needed (for subdirectory installations)
// $router->setBasePath('/edulinks');

// Authentication routes
$router->get('/login', function() {
    if (Auth::check()) {
        if (Auth::isAdmin()) {
            header('Location: /admin');
        } else {
            header('Location: /user');
        }
        exit;
    }
    
    $view = new View();
    $view->render('auth/login', [
        'title' => 'Giriş',
        '_no_layout' => true
    ]);
});

$router->post('/login', 'AuthController@login');
$router->post('/logout', 'AuthController@logout');

// Admin routes
$router->get('/admin', 'AdminController@dashboard');
$router->get('/admin/dashboard', 'AdminController@dashboard');

// User management routes
$router->get('/admin/users', 'AdminController@users');
$router->get('/admin/users/create', 'AdminController@createUser');
$router->post('/admin/users/store', 'AdminController@storeUser');
$router->get('/admin/users/{id}/edit', 'AdminController@editUser');
$router->post('/admin/users/{id}/update', 'AdminController@updateUser');
$router->post('/admin/users/{id}/delete', 'AdminController@deleteUser');

// Page management routes
$router->get('/admin/pages', 'AdminController@pages');
$router->get('/admin/pages/create', 'AdminController@createPage');
$router->post('/admin/pages/store', 'AdminController@storePage');
$router->get('/admin/pages/{id}/edit', 'AdminController@editPage');
$router->post('/admin/pages/{id}/update', 'AdminController@updatePage');
$router->post('/admin/pages/{id}/delete', 'AdminController@deletePage');

// Link management routes
$router->get('/admin/links', 'AdminController@links');
$router->get('/admin/links/create', 'AdminController@createLink');
$router->post('/admin/links/store', 'AdminController@storeLink');
$router->get('/admin/links/{id}/edit', 'AdminController@editLink');
$router->post('/admin/links/{id}/update', 'AdminController@updateLink');
$router->post('/admin/links/{id}/delete', 'AdminController@deleteLink');

// User interface routes
$router->get('/', 'UserController@dashboard');
$router->get('/user', 'UserController@dashboard');
$router->get('/user/dashboard', 'UserController@dashboard');
$router->get('/user/page/{slug}', 'UserController@viewPage');
$router->get('/user/profile', 'UserController@profile');
$router->post('/user/profile/update', 'UserController@updateProfile');

// File routes
$router->get('/download/{id}', 'FileController@download');
$router->post('/upload', 'FileController@upload');

// Include API routes
require_once '../routes/api.php';

// Handle 404 for assets (let web server handle them)
if (strpos($_SERVER['REQUEST_URI'], '/assets/') === 0) {
    http_response_code(404);
    exit;
}

// Dispatch request
$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);