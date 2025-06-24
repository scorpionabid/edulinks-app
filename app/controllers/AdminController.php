<?php
/**
 * EduLinks Admin Controller
 * 
 * Handles admin panel operations
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

class AdminController extends Controller
{
    private User $userModel;
    
    public function __construct()
    {
        parent::__construct();
        
        // Require admin authentication
        $this->requireAdmin();
        
        $this->userModel = new User();
    }
    
    /**
     * Admin dashboard
     */
    public function dashboard(): void
    {
        $userStats = $this->userModel->getStatistics();
        
        // Get recent activity (this would be implemented with analytics)
        $recentActivity = [];
        
        $this->view('admin/dashboard/index', [
            'title' => 'Admin Panel',
            'user_stats' => $userStats,
            'recent_activity' => $recentActivity
        ]);
    }
    
    /**
     * User management - list users
     */
    public function users(): void
    {
        $page = (int)($this->input('page') ?? 1);
        $search = $this->input('search');
        $role = $this->input('role');
        $status = $this->input('status');
        
        $filters = [
            'search' => $search,
            'role' => $role,
            'is_active' => $status
        ];
        
        $paginatedUsers = $this->userModel->getPaginated($page, 20, $filters);
        
        // Format users for display
        $paginatedUsers['data'] = array_map(
            [$this->userModel, 'formatForDisplay'],
            $paginatedUsers['data']
        );
        
        $this->view('admin/users/index', [
            'title' => 'İstifadəçi İdarəetməsi',
            'users' => $paginatedUsers,
            'filters' => $filters
        ]);
    }
    
    /**
     * Show create user form
     */
    public function createUser(): void
    {
        $this->view('admin/users/create', [
            'title' => 'Yeni İstifadəçi'
        ]);
    }
    
    /**
     * Store new user
     */
    public function storeUser(): void
    {
        $this->requireCSRF();
        
        $data = [
            'email' => $this->input('email'),
            'password' => $this->input('password'),
            'password_confirmation' => $this->input('password_confirmation'),
            'first_name' => $this->input('first_name'),
            'last_name' => $this->input('last_name'),
            'role' => $this->input('role'),
            'is_active' => (bool)$this->input('is_active')
        ];
        
        // Validate input
        $validation = $this->validate($data, [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'role' => 'required|in:admin,user'
        ]);
        
        if (!$validation['valid']) {
            Session::error('Formdakı xətaları düzəldin.');
            // Store errors and old input in session for display
            Session::set('validation_errors', $validation['errors']);
            Session::set('old_input', $data);
            $this->redirect('/admin/users/create');
            return;
        }
        
        try {
            $userId = $this->userModel->createUser($validation['data']);
            
            Logger::info("New user created", [
                'user_id' => $userId,
                'email' => $data['email'],
                'created_by' => Auth::id()
            ]);
            
            Session::success('İstifadəçi uğurla yaradıldı.');
            $this->redirect('/admin/users');
            
        } catch (\Exception $e) {
            Logger::error("Failed to create user: " . $e->getMessage());
            Session::error('İstifadəçi yaradılarkən xəta baş verdi.');
            $this->redirect('/admin/users/create');
        }
    }
    
    /**
     * Show edit user form
     */
    public function editUser(string $id): void
    {
        $user = $this->userModel->find((int)$id);
        
        if (!$user) {
            Session::error('İstifadəçi tapılmadı.');
            $this->redirect('/admin/users');
            return;
        }
        
        $this->view('admin/users/edit', [
            'title' => 'İstifadəçini Redaktə Et',
            'user' => $user
        ]);
    }
    
    /**
     * Update user
     */
    public function updateUser(string $id): void
    {
        $this->requireCSRF();
        
        $userId = (int)$id;
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            Session::error('İstifadəçi tapılmadı.');
            $this->redirect('/admin/users');
            return;
        }
        
        $data = [
            'email' => $this->input('email'),
            'first_name' => $this->input('first_name'),
            'last_name' => $this->input('last_name'),
            'role' => $this->input('role'),
            'is_active' => (bool)$this->input('is_active')
        ];
        
        // Add password if provided
        $newPassword = $this->input('password');
        if (!empty($newPassword)) {
            $data['password'] = $newPassword;
            $data['password_confirmation'] = $this->input('password_confirmation');
        }
        
        // Validation rules
        $rules = [
            'email' => "required|email|unique:users,email,{$userId}",
            'first_name' => 'required|min:2|max:100',
            'last_name' => 'required|min:2|max:100',
            'role' => 'required|in:admin,user'
        ];
        
        if (!empty($newPassword)) {
            $rules['password'] = 'required|min:8|confirmed';
        }
        
        $validation = $this->validate($data, $rules);
        
        if (!$validation['valid']) {
            Session::error('Formdakı xətaları düzəldin.');
            Session::set('validation_errors', $validation['errors']);
            Session::set('old_input', $data);
            $this->redirect("/admin/users/{$userId}/edit");
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
            
            Logger::info("User updated", [
                'user_id' => $userId,
                'updated_by' => Auth::id()
            ]);
            
            Session::success('İstifadəçi məlumatları yeniləndi.');
            $this->redirect('/admin/users');
            
        } catch (\Exception $e) {
            Logger::error("Failed to update user: " . $e->getMessage());
            Session::error('İstifadəçi yenilənərkən xəta baş verdi.');
            $this->redirect("/admin/users/{$userId}/edit");
        }
    }
    
    /**
     * Delete user
     */
    public function deleteUser(string $id): void
    {
        $this->requireCSRF();
        
        $userId = (int)$id;
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            Session::error('İstifadəçi tapılmadı.');
            $this->redirect('/admin/users');
            return;
        }
        
        // Prevent admin from deleting themselves
        if ($userId === Auth::id()) {
            Session::error('Özünüzü silə bilməzsiniz.');
            $this->redirect('/admin/users');
            return;
        }
        
        try {
            $this->userModel->delete($userId);
            
            Logger::info("User deleted", [
                'user_id' => $userId,
                'email' => $user['email'],
                'deleted_by' => Auth::id()
            ]);
            
            Session::success('İstifadəçi silindi.');
            
        } catch (\Exception $e) {
            Logger::error("Failed to delete user: " . $e->getMessage());
            Session::error('İstifadəçi silinərkən xəta baş verdi.');
        }
        
        $this->redirect('/admin/users');
    }
    
    /**
     * Page management methods will be implemented here
     */
    public function pages(): void
    {
        // TODO: Implement page management
        $this->view('admin/pages/index', [
            'title' => 'Səhifə İdarəetməsi'
        ]);
    }
    
    public function links(): void
    {
        // TODO: Implement link management
        $this->view('admin/links/index', [
            'title' => 'Link İdarəetməsi'
        ]);
    }
}