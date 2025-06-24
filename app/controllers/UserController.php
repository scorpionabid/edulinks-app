<?php
/**
 * EduLinks User Controller
 * 
 * Handles user interface operations
 */

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\CSRF;
use App\Core\Session;
use App\Core\Logger;
use App\Models\User;
use App\Models\Page;
use App\Models\Link;

class UserController extends Controller
{
    private User $userModel;
    private Page $pageModel;
    private Link $linkModel;
    
    public function __construct()
    {
        parent::__construct();
        
        // Require user authentication
        $this->requireAuth();
        
        $this->userModel = new User();
        $this->pageModel = new Page();
        $this->linkModel = new Link();
    }
    
    /**
     * User dashboard
     */
    public function dashboard(): void
    {
        $user = Auth::user();
        
        // Get user's accessible pages
        $accessiblePages = Auth::getAccessiblePages();
        
        // Get recent links
        $recentLinks = $this->linkModel->getRecentLinks(10);
        
        // Get popular links
        $popularLinks = $this->linkModel->getPopularLinks(5);
        
        // Get featured links
        $featuredLinks = $this->linkModel->getFeaturedLinks(8);
        
        // Format links for display
        $recentLinks = array_map([$this->linkModel, 'formatForDisplay'], $recentLinks);
        $popularLinks = array_map([$this->linkModel, 'formatForDisplay'], $popularLinks);
        $featuredLinks = array_map([$this->linkModel, 'formatForDisplay'], $featuredLinks);
        
        $this->view('user/dashboard/index', [
            'title' => 'Dashboard',
            'user' => $user,
            'accessible_pages' => $accessiblePages,
            'recent_links' => $recentLinks,
            'popular_links' => $popularLinks,
            'featured_links' => $featuredLinks
        ]);
    }
    
    /**
     * View specific page with links
     */
    public function viewPage(string $slug): void
    {
        $page = $this->pageModel->findBySlug($slug);
        
        if (!$page) {
            http_response_code(404);
            $this->view('errors/404', [
                'title' => 'Səhifə Tapılmadı',
                'message' => 'Axtardığınız səhifə mövcud deyil.'
            ]);
            return;
        }
        
        // Check permission (unless admin)
        if (!Auth::isAdmin() && !Auth::hasPagePermission($page['id'])) {
            http_response_code(403);
            $this->view('errors/403', [
                'title' => 'Giriş Qadağandır',
                'message' => 'Bu səhifəyə giriş icazəniz yoxdur.'
            ]);
            return;
        }
        
        // Get page links
        $links = $this->linkModel->getPageLinks($page['id']);
        
        // Format links for display
        $links = array_map([$this->linkModel, 'formatForDisplay'], $links);
        
        // Get user's accessible pages for navigation
        $accessiblePages = Auth::getAccessiblePages();
        
        $this->view('user/page/view', [
            'title' => $page['title'],
            'page' => $page,
            'links' => $links,
            'accessible_pages' => $accessiblePages
        ]);
    }
    
    /**
     * User profile
     */
    public function profile(): void
    {
        $user = Auth::user();
        
        // Get user's accessible pages with permission details
        $userPermissions = [];
        if (!Auth::isAdmin()) {
            $accessiblePages = Auth::getAccessiblePages();
            foreach ($accessiblePages as $page) {
                $userPermissions[] = [
                    'page' => $page,
                    'permission_type' => $page['permission_type'] ?? 'read'
                ];
            }
        }
        
        $this->view('user/profile/index', [
            'title' => 'Profil',
            'user' => $user,
            'user_permissions' => $userPermissions
        ]);
    }
    
    /**
     * Update user profile
     */
    public function updateProfile(): void
    {
        $this->requireCSRF();
        
        $userId = Auth::id();
        $data = [
            'first_name' => $this->input('first_name'),
            'last_name' => $this->input('last_name'),
            'email' => $this->input('email')
        ];
        
        // Add password if provided
        $newPassword = $this->input('password');
        if (!empty($newPassword)) {
            $data['password'] = $newPassword;
            $data['password_confirmation'] = $this->input('password_confirmation');
        }
        
        // Validation rules
        $rules = [
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'email' => "required|email|unique:users,email,{$userId}"
        ];
        
        if (!empty($newPassword)) {
            $rules['password'] = 'required|min:8|confirmed';
        }
        
        $validation = $this->validate($data, $rules);
        
        if (!$validation['valid']) {
            Session::error('Formdakı xətaları düzəldin.');
            Session::set('validation_errors', $validation['errors']);
            Session::set('old_input', $data);
            $this->redirect('/user/profile');
            return;
        }
        
        try {
            // Update password separately if provided
            if (!empty($newPassword)) {
                $this->userModel->updatePassword($userId, $newPassword);
                unset($validation['data']['password']);
                unset($validation['data']['password_confirmation']);
            }
            
            $this->userModel->update($userId, $validation['data']);
            
            Logger::info("User profile updated", [
                'user_id' => $userId
            ]);
            
            Session::success('Profil məlumatları yeniləndi.');
            
        } catch (\Exception $e) {
            Logger::error("Failed to update user profile: " . $e->getMessage());
            Session::error('Profil yenilənərkən xəta baş verdi.');
        }
        
        $this->redirect('/user/profile');
    }
    
    /**
     * Search links
     */
    public function search(): void
    {
        $query = $this->input('q', '');
        $links = [];
        $totalResults = 0;
        
        if (!empty($query) && strlen($query) >= 2) {
            // Get all search results
            $allLinks = $this->linkModel->searchLinks($query, 100);
            
            // Filter by user permissions
            $links = [];
            foreach ($allLinks as $link) {
                if (Auth::isAdmin() || Auth::hasPagePermission($link['page_id'])) {
                    $links[] = $this->linkModel->formatForDisplay($link);
                }
            }
            
            $totalResults = count($links);
            
            // Limit to first 20 results for display
            $links = array_slice($links, 0, 20);
        }
        
        // Get user's accessible pages for navigation
        $accessiblePages = Auth::getAccessiblePages();
        
        $this->view('user/search/index', [
            'title' => 'Axtarış',
            'query' => $query,
            'links' => $links,
            'total_results' => $totalResults,
            'accessible_pages' => $accessiblePages
        ]);
    }
    
    /**
     * Get user statistics via AJAX
     */
    public function getStats(): void
    {
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
        
        $this->json([
            'success' => true,
            'data' => [
                'accessible_pages' => $accessiblePagesCount,
                'total_links' => $totalLinksCount
            ]
        ]);
    }
    
    /**
     * Record link click (AJAX)
     */
    public function recordClick(): void
    {
        $linkId = (int)$this->input('link_id');
        
        if (!$linkId) {
            $this->jsonError('Link ID tələb olunur', 400);
            return;
        }
        
        $link = $this->linkModel->getLinkWithDetails($linkId);
        
        if (!$link || !$link['is_active']) {
            $this->jsonError('Link tapılmadı', 404);
            return;
        }
        
        // Check permission
        if (!Auth::isAdmin() && !Auth::hasPagePermission($link['page_id'])) {
            $this->jsonError('Bu linkə giriş icazəniz yoxdur', 403);
            return;
        }
        
        // Increment click count
        $this->linkModel->incrementClicks($linkId);
        
        // Log click
        Logger::info("Link clicked", [
            'link_id' => $linkId,
            'link_title' => $link['title'],
            'user_id' => Auth::id(),
            'page_id' => $link['page_id']
        ]);
        
        $this->jsonSuccess('Klik qeydə alındı');
    }
}