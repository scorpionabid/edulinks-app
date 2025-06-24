<?php
/**
 * EduLinks API Controller
 * 
 * Handles REST API endpoints for the EduLinks system
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Logger;
use App\Models\User;
use App\Models\Page;
use App\Models\Link;

class ApiController extends Controller
{
    private User $userModel;
    private Page $pageModel;
    private Link $linkModel;
    
    public function __construct()
    {
        parent::__construct();
        
        // Set JSON content type
        header('Content-Type: application/json');
        
        $this->userModel = new User();
        $this->pageModel = new Page();
        $this->linkModel = new Link();
    }
    
    /**
     * Record link click
     * POST /api/links/{id}/click
     */
    public function recordLinkClick(string $id): void
    {
        $this->requireAuth();
        
        if ($this->getMethod() !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }
        
        $linkId = (int)$id;
        if (!$linkId) {
            $this->jsonError('Invalid link ID', 400);
            return;
        }
        
        $link = $this->linkModel->getLinkWithDetails($linkId);
        
        if (!$link || !$link['is_active']) {
            $this->jsonError('Link not found', 404);
            return;
        }
        
        // Check permission
        if (!Auth::isAdmin() && !Auth::hasPagePermission($link['page_id'])) {
            $this->jsonError('Access denied', 403);
            return;
        }
        
        // Increment click count
        $this->linkModel->incrementClicks($linkId);
        
        // Log click
        Logger::info("Link clicked via API", [
            'link_id' => $linkId,
            'link_title' => $link['title'],
            'user_id' => Auth::id(),
            'page_id' => $link['page_id']
        ]);
        
        $this->jsonSuccess('Click recorded', [
            'click_count' => $link['click_count'] + 1
        ]);
    }
    
    /**
     * Get link details
     * GET /api/links/{id}
     */
    public function getLinkDetails(string $id): void
    {
        $this->requireAuth();
        
        $linkId = (int)$id;
        $link = $this->linkModel->getLinkWithDetails($linkId);
        
        if (!$link) {
            $this->jsonError('Link not found', 404);
            return;
        }
        
        // Check permission
        if (!Auth::isAdmin() && !Auth::hasPagePermission($link['page_id'])) {
            $this->jsonError('Access denied', 403);
            return;
        }
        
        $this->jsonSuccess('Link details', [
            'link' => $this->linkModel->formatForDisplay($link)
        ]);
    }
    
    /**
     * Get user's accessible pages
     * GET /api/user/pages
     */
    public function getUserPages(): void
    {
        $this->requireAuth();
        
        $accessiblePages = Auth::getAccessiblePages();
        
        $this->jsonSuccess('User pages', [
            'pages' => $accessiblePages,
            'total' => count($accessiblePages)
        ]);
    }
    
    /**
     * Get page links
     * GET /api/pages/{id}/links
     */
    public function getPageLinks(string $id): void
    {
        $this->requireAuth();
        
        $pageId = (int)$id;
        $page = $this->pageModel->find($pageId);
        
        if (!$page) {
            $this->jsonError('Page not found', 404);
            return;
        }
        
        // Check permission
        if (!Auth::isAdmin() && !Auth::hasPagePermission($pageId)) {
            $this->jsonError('Access denied', 403);
            return;
        }
        
        $links = $this->linkModel->getPageLinks($pageId);
        $formattedLinks = array_map([$this->linkModel, 'formatForDisplay'], $links);
        
        $this->jsonSuccess('Page links', [
            'page' => $page,
            'links' => $formattedLinks,
            'total' => count($formattedLinks)
        ]);
    }
    
    /**
     * Search links
     * GET /api/search?q={query}
     */
    public function searchLinks(): void
    {
        $this->requireAuth();
        
        $query = $this->input('q', '');
        $limit = min((int)$this->input('limit', 20), 100);
        
        if (strlen($query) < 2) {
            $this->jsonError('Query must be at least 2 characters', 400);
            return;
        }
        
        // Get all search results
        $allLinks = $this->linkModel->searchLinks($query, $limit * 2);
        
        // Filter by user permissions
        $links = [];
        foreach ($allLinks as $link) {
            if (Auth::isAdmin() || Auth::hasPagePermission($link['page_id'])) {
                $links[] = $this->linkModel->formatForDisplay($link);
                if (count($links) >= $limit) {
                    break;
                }
            }
        }
        
        $this->jsonSuccess('Search results', [
            'query' => $query,
            'links' => $links,
            'total' => count($links)
        ]);
    }
    
    /**
     * Get user statistics
     * GET /api/user/stats
     */
    public function getUserStats(): void
    {
        $this->requireAuth();
        
        $userId = Auth::id();
        
        // Get user's accessible pages count
        $accessiblePagesCount = count(Auth::getAccessiblePages());
        
        // Get total links in accessible pages
        $totalLinksCount = 0;
        if (Auth::isAdmin()) {
            $totalLinksCount = $this->linkModel->count(['is_active' => true]);
        } else {
            $accessiblePages = Auth::getAccessiblePages();
            foreach ($accessiblePages as $page) {
                $totalLinksCount += $this->linkModel->count([
                    'page_id' => $page['id'],
                    'is_active' => true
                ]);
            }
        }
        
        // Get recent activity
        $recentLinks = $this->linkModel->getRecentLinks(5);
        $formattedRecentLinks = array_map([$this->linkModel, 'formatForDisplay'], $recentLinks);
        
        $this->jsonSuccess('User statistics', [
            'accessible_pages' => $accessiblePagesCount,
            'total_links' => $totalLinksCount,
            'recent_links' => $formattedRecentLinks
        ]);
    }
    
    /**
     * Get system health status (admin only)
     * GET /api/system/health
     */
    public function getSystemHealth(): void
    {
        $this->requireAdmin();
        
        $health = [
            'status' => 'healthy',
            'timestamp' => date('Y-m-d H:i:s'),
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'uploads' => $this->checkUploadsHealth()
        ];
        
        // Overall status
        $allHealthy = $health['database']['status'] === 'ok' && 
                     $health['storage']['status'] === 'ok' && 
                     $health['uploads']['status'] === 'ok';
        
        if (!$allHealthy) {
            $health['status'] = 'warning';
        }
        
        $this->jsonSuccess('System health', $health);
    }
    
    /**
     * Get system statistics (admin only)
     * GET /api/system/stats
     */
    public function getSystemStats(): void
    {
        $this->requireAdmin();
        
        $stats = [
            'users' => $this->userModel->getStatistics(),
            'pages' => $this->pageModel->getStatistics(),
            'links' => $this->linkModel->getStatistics(),
            'storage' => $this->getStorageStats()
        ];
        
        $this->jsonSuccess('System statistics', $stats);
    }
    
    /**
     * Update link order in page
     * PUT /api/pages/{id}/links/reorder
     */
    public function reorderPageLinks(string $id): void
    {
        $this->requireAdmin();
        
        if ($this->getMethod() !== 'PUT') {
            $this->jsonError('Method not allowed', 405);
            return;
        }
        
        $pageId = (int)$id;
        $page = $this->pageModel->find($pageId);
        
        if (!$page) {
            $this->jsonError('Page not found', 404);
            return;
        }
        
        $linkIds = $this->getJsonInput()['link_ids'] ?? [];
        
        if (!is_array($linkIds)) {
            $this->jsonError('Invalid link_ids format', 400);
            return;
        }
        
        try {
            $this->linkModel->updateLinkOrder($pageId, $linkIds);
            
            Logger::info("Page links reordered", [
                'page_id' => $pageId,
                'link_count' => count($linkIds),
                'admin_id' => Auth::id()
            ]);
            
            $this->jsonSuccess('Link order updated');
            
        } catch (\Exception $e) {
            Logger::error("Failed to reorder links: " . $e->getMessage());
            $this->jsonError('Failed to update link order', 500);
        }
    }
    
    /**
     * Toggle link featured status
     * PUT /api/links/{id}/featured
     */
    public function toggleLinkFeatured(string $id): void
    {
        $this->requireAdmin();
        
        if ($this->getMethod() !== 'PUT') {
            $this->jsonError('Method not allowed', 405);
            return;
        }
        
        $linkId = (int)$id;
        $link = $this->linkModel->find($linkId);
        
        if (!$link) {
            $this->jsonError('Link not found', 404);
            return;
        }
        
        $newFeaturedStatus = !$link['is_featured'];
        
        try {
            $this->linkModel->update($linkId, [
                'is_featured' => $newFeaturedStatus
            ]);
            
            Logger::info("Link featured status toggled", [
                'link_id' => $linkId,
                'is_featured' => $newFeaturedStatus,
                'admin_id' => Auth::id()
            ]);
            
            $this->jsonSuccess('Featured status updated', [
                'is_featured' => $newFeaturedStatus
            ]);
            
        } catch (\Exception $e) {
            Logger::error("Failed to toggle featured status: " . $e->getMessage());
            $this->jsonError('Failed to update featured status', 500);
        }
    }
    
    /**
     * Check database health
     */
    private function checkDatabaseHealth(): array
    {
        try {
            $db = \App\Core\Database::getInstance();
            $stmt = $db->query("SELECT 1");
            
            return [
                'status' => 'ok',
                'message' => 'Database connection is healthy'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed'
            ];
        }
    }
    
    /**
     * Check storage health
     */
    private function checkStorageHealth(): array
    {
        $uploadDir = $_ENV['UPLOAD_DIR'] ?? '/uploads';
        $basePath = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
        
        if (!is_dir($basePath)) {
            return [
                'status' => 'error',
                'message' => 'Upload directory does not exist'
            ];
        }
        
        if (!is_writable($basePath)) {
            return [
                'status' => 'error',
                'message' => 'Upload directory is not writable'
            ];
        }
        
        $freeSpace = disk_free_space($basePath);
        $totalSpace = disk_total_space($basePath);
        $usedPercent = round((($totalSpace - $freeSpace) / $totalSpace) * 100, 2);
        
        return [
            'status' => $usedPercent > 90 ? 'warning' : 'ok',
            'free_space' => $this->formatBytes($freeSpace),
            'total_space' => $this->formatBytes($totalSpace),
            'used_percent' => $usedPercent
        ];
    }
    
    /**
     * Check uploads health
     */
    private function checkUploadsHealth(): array
    {
        $uploadStats = \App\Core\FileUpload::getUploadStats();
        
        return [
            'status' => 'ok',
            'total_files' => $uploadStats['total_files'],
            'total_size' => $this->formatBytes($uploadStats['total_size']),
            'average_size' => $this->formatBytes($uploadStats['average_size'])
        ];
    }
    
    /**
     * Get storage statistics
     */
    private function getStorageStats(): array
    {
        $uploadDir = $_ENV['UPLOAD_DIR'] ?? '/uploads';
        $basePath = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
        
        $freeSpace = disk_free_space($basePath);
        $totalSpace = disk_total_space($basePath);
        $usedSpace = $totalSpace - $freeSpace;
        
        return [
            'free_space' => $freeSpace,
            'used_space' => $usedSpace,
            'total_space' => $totalSpace,
            'free_space_formatted' => $this->formatBytes($freeSpace),
            'used_space_formatted' => $this->formatBytes($usedSpace),
            'total_space_formatted' => $this->formatBytes($totalSpace),
            'used_percent' => round(($usedSpace / $totalSpace) * 100, 2)
        ];
    }
    
    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }
    
    /**
     * Get JSON input data
     */
    private function getJsonInput(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
}