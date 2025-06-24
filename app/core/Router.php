<?php
/**
 * EduLinks Router Class
 * 
 * Handles URL routing and request dispatching
 */

namespace App\Core;

class Router
{
    private array $routes = [];
    private string $basePath = '';
    
    /**
     * Add GET route
     */
    public function get(string $pattern, $handler): void
    {
        $this->addRoute('GET', $pattern, $handler);
    }
    
    /**
     * Add POST route
     */
    public function post(string $pattern, $handler): void
    {
        $this->addRoute('POST', $pattern, $handler);
    }
    
    /**
     * Add PUT route
     */
    public function put(string $pattern, $handler): void
    {
        $this->addRoute('PUT', $pattern, $handler);
    }
    
    /**
     * Add DELETE route
     */
    public function delete(string $pattern, $handler): void
    {
        $this->addRoute('DELETE', $pattern, $handler);
    }
    
    /**
     * Add route for multiple methods
     */
    public function match(array $methods, string $pattern, $handler): void
    {
        foreach ($methods as $method) {
            $this->addRoute($method, $pattern, $handler);
        }
    }
    
    /**
     * Set base path
     */
    public function setBasePath(string $basePath): void
    {
        $this->basePath = rtrim($basePath, '/');
    }
    
    /**
     * Add route to collection
     */
    private function addRoute(string $method, string $pattern, $handler): void
    {
        $pattern = $this->basePath . '/' . ltrim($pattern, '/');
        $pattern = rtrim($pattern, '/') ?: '/';
        
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler
        ];
    }
    
    /**
     * Dispatch request
     */
    public function dispatch(string $uri, string $method): void
    {
        // Remove query string from URI
        $uri = strtok($uri, '?');
        $uri = rtrim($uri, '/') ?: '/';
        
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            
            $params = $this->matchRoute($route['pattern'], $uri);
            
            if ($params !== false) {
                $this->executeHandler($route['handler'], $params);
                return;
            }
        }
        
        // No route found
        $this->handleNotFound();
    }
    
    /**
     * Match route pattern against URI
     */
    private function matchRoute(string $pattern, string $uri): array|false
    {
        // Convert route pattern to regex
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
        $pattern = '#^' . $pattern . '$#';
        
        if (preg_match($pattern, $uri, $matches)) {
            array_shift($matches); // Remove full match
            return $matches;
        }
        
        return false;
    }
    
    /**
     * Execute route handler
     */
    private function executeHandler($handler, array $params): void
    {
        try {
            if (is_string($handler)) {
                // Handle "Controller@method" format
                if (strpos($handler, '@') !== false) {
                    [$controller, $method] = explode('@', $handler);
                    $controllerClass = "App\\Controllers\\{$controller}";
                    
                    if (class_exists($controllerClass)) {
                        $controllerInstance = new $controllerClass();
                        
                        if (method_exists($controllerInstance, $method)) {
                            call_user_func_array([$controllerInstance, $method], $params);
                            return;
                        }
                    }
                }
                
                // Handle function name
                if (function_exists($handler)) {
                    call_user_func_array($handler, $params);
                    return;
                }
            }
            
            if (is_callable($handler)) {
                call_user_func_array($handler, $params);
                return;
            }
            
            throw new \RuntimeException("Invalid route handler");
            
        } catch (\Throwable $e) {
            $this->handleError($e);
        }
    }
    
    /**
     * Handle 404 Not Found
     */
    private function handleNotFound(): void
    {
        http_response_code(404);
        
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Route not found',
                'code' => 404
            ]);
        } else {
            $view = new View();
            $view->render('errors/404', [
                'title' => 'Səhifə Tapılmadı',
                'message' => 'Axtardığınız səhifə mövcud deyil.'
            ]);
        }
    }
    
    /**
     * Handle errors
     */
    private function handleError(\Throwable $e): void
    {
        http_response_code(500);
        
        // Log error
        error_log("Router Error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
        
        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'code' => 500
            ]);
        } else {
            $view = new View();
            $view->render('errors/500', [
                'title' => 'Server Xətası',
                'message' => 'Daxili server xətası baş verdi.'
            ]);
        }
    }
    
    /**
     * Check if request is API call
     */
    private function isApiRequest(): bool
    {
        return strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') === 0;
    }
    
    /**
     * Generate URL for named route
     */
    public function url(string $name, array $params = []): string
    {
        // This would need route naming implementation
        // For now, return a simple URL
        return $this->basePath . '/' . ltrim($name, '/');
    }
}