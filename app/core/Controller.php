<?php
/**
 * EduLinks Base Controller Class
 * 
 * Abstract base class for all controllers
 */

namespace App\Core;

abstract class Controller
{
    protected $config;
    
    public function __construct()
    {
        $this->config = require dirname(__DIR__) . '/config/app.php';
    }
    
    /**
     * Render view template
     */
    protected function view(string $template, array $data = []): void
    {
        $view = new View();
        $view->render($template, $data);
    }
    
    /**
     * Return JSON response
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Return JSON error response
     */
    protected function jsonError(string $message, int $status = 400, array $errors = []): void
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        $this->json($response, $status);
    }
    
    /**
     * Return JSON success response
     */
    protected function jsonSuccess(string $message = 'Success', array $data = []): void
    {
        $response = [
            'success' => true,
            'message' => $message,
        ];
        
        if (!empty($data)) {
            $response['data'] = $data;
        }
        
        $this->json($response, 200);
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect(string $url, int $status = 302): void
    {
        header("Location: {$url}", true, $status);
        exit;
    }
    
    /**
     * Redirect back with message
     */
    protected function redirectBack(string $message = null, string $type = 'info'): void
    {
        if ($message) {
            Session::flash($type, $message);
        }
        
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }
    
    /**
     * Validate input data
     */
    protected function validate(array $data, array $rules): array
    {
        $validator = new Validator();
        return $validator->validate($data, $rules);
    }
    
    /**
     * Check if user is authenticated
     */
    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            if ($this->isApiRequest()) {
                $this->jsonError('Authentication required', 401);
            } else {
                $this->redirect('/user/login');
            }
        }
    }
    
    /**
     * Check if user has admin role
     */
    protected function requireAdmin(): void
    {
        $this->requireAuth();
        
        if (!Auth::isAdmin()) {
            if ($this->isApiRequest()) {
                $this->jsonError('Admin access required', 403);
            } else {
                $this->redirect('/');
            }
        }
    }
    
    /**
     * Check CSRF token
     */
    protected function requireCSRF(): void
    {
        if (!CSRF::verify()) {
            if ($this->isApiRequest()) {
                $this->jsonError('Invalid CSRF token', 403);
            } else {
                $this->redirectBack('Invalid form token. Please try again.', 'error');
            }
        }
    }
    
    /**
     * Get current user
     */
    protected function user(): ?array
    {
        return Auth::user();
    }
    
    /**
     * Check if request is API call
     */
    protected function isApiRequest(): bool
    {
        return strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0;
    }
    
    /**
     * Get request method
     */
    protected function getMethod(): string
    {
        return $_SERVER['REQUEST_METHOD'] ?? 'GET';
    }
    
    /**
     * Get request input
     */
    protected function input(string $key = null, $default = null)
    {
        $input = array_merge($_GET, $_POST);
        
        if ($key === null) {
            return $input;
        }
        
        return $input[$key] ?? $default;
    }
    
    /**
     * Get file input
     */
    protected function file(string $key): ?array
    {
        return $_FILES[$key] ?? null;
    }
    
    /**
     * Log activity
     */
    protected function log(string $message, string $level = 'info', array $context = []): void
    {
        Logger::log($level, $message, $context);
    }
}