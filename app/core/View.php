<?php
/**
 * EduLinks View Renderer Class
 * 
 * Handles template rendering and view management
 */

namespace App\Core;

class View
{
    private string $viewsPath;
    private array $data = [];
    private string $layout = 'master';
    
    public function __construct()
    {
        $this->viewsPath = dirname(__DIR__) . '/views/';
    }
    
    /**
     * Render view template
     */
    public function render(string $template, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        
        $templatePath = $this->viewsPath . str_replace('.', '/', $template) . '.php';
        
        if (!file_exists($templatePath)) {
            throw new \RuntimeException("View template not found: {$template}");
        }
        
        // Extract data to variables
        extract($this->data);
        
        // Start output buffering
        ob_start();
        
        try {
            include $templatePath;
            $content = ob_get_clean();
            
            // If layout is set, wrap content in layout
            if ($this->layout && !isset($this->data['_no_layout'])) {
                $this->renderLayout($content);
            } else {
                echo $content;
            }
            
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }
    
    /**
     * Render layout with content
     */
    private function renderLayout(string $content): void
    {
        $layoutPath = $this->viewsPath . 'layout/' . $this->layout . '.php';
        
        if (!file_exists($layoutPath)) {
            echo $content;
            return;
        }
        
        // Add content to data
        $this->data['content'] = $content;
        
        // Extract data to variables
        extract($this->data);
        
        include $layoutPath;
    }
    
    /**
     * Set layout template
     */
    public function setLayout(string $layout = null): void
    {
        $this->layout = $layout;
    }
    
    /**
     * Add data to view
     */
    public function with(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }
    
    /**
     * Add multiple data to view
     */
    public function withData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }
    
    /**
     * Check if view exists
     */
    public function exists(string $template): bool
    {
        $templatePath = $this->viewsPath . str_replace('.', '/', $template) . '.php';
        return file_exists($templatePath);
    }
    
    /**
     * Include partial view
     */
    public function include(string $partial, array $data = []): void
    {
        $partialPath = $this->viewsPath . str_replace('.', '/', $partial) . '.php';
        
        if (!file_exists($partialPath)) {
            return;
        }
        
        // Merge data with current view data
        $partialData = array_merge($this->data, $data);
        extract($partialData);
        
        include $partialPath;
    }
    
    /**
     * Escape HTML output
     */
    public function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Format date
     */
    public function formatDate(string $date, string $format = 'd.m.Y H:i'): string
    {
        try {
            $dateTime = new \DateTime($date);
            return $dateTime->format($format);
        } catch (\Exception $e) {
            return $date;
        }
    }
    
    /**
     * Format file size
     */
    public function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);
        
        return sprintf("%.1f %s", $bytes / pow(1024, $factor), $units[$factor]);
    }
    
    /**
     * Generate URL
     */
    public function url(string $path = ''): string
    {
        $config = require dirname(__DIR__) . '/config/app.php';
        $baseUrl = rtrim($config['url'], '/');
        
        return $baseUrl . '/' . ltrim($path, '/');
    }
    
    /**
     * Generate asset URL
     */
    public function asset(string $path): string
    {
        return $this->url('assets/' . ltrim($path, '/'));
    }
    
    /**
     * Get CSRF token
     */
    public function csrfToken(): string
    {
        return CSRF::token();
    }
    
    /**
     * Get flash message
     */
    public function flash(string $type = null): ?string
    {
        return Session::flash($type);
    }
    
    /**
     * Check if user is authenticated
     */
    public function auth(): bool
    {
        return Auth::check();
    }
    
    /**
     * Get current user
     */
    public function user(): ?array
    {
        return Auth::user();
    }
    
    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return Auth::isAdmin();
    }
}