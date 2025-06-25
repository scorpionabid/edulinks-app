<?php
/**
 * EduLinks User Model
 * 
 * Handles user data operations
 */

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = [
        'email',
        'password',
        'first_name',
        'last_name',
        'role',
        'is_active',
        'last_login',
        'remember_token'
    ];
    protected $hidden = ['password', 'remember_token'];
    
    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        return $this->findWhere(['email' => $email]);
    }
    
    /**
     * Find active user by email
     */
    public function findActiveByEmail(string $email): ?array
    {
        return $this->findWhere(['email' => $email, 'is_active' => true]);
    }
    
    /**
     * Get all active users
     */
    public function getActiveUsers(string $orderBy = 'first_name, last_name'): array
    {
        return $this->where(['is_active' => true], $orderBy);
    }
    
    /**
     * Get users by role
     */
    public function getUsersByRole(string $role): array
    {
        return $this->where(['role' => $role, 'is_active' => true], 'first_name, last_name');
    }
    
    /**
     * Get admin users
     */
    public function getAdmins(): array
    {
        return $this->getUsersByRole('admin');
    }
    
    /**
     * Get regular users
     */
    public function getRegularUsers(): array
    {
        return $this->getUsersByRole('user');
    }
    
    /**
     * Create new user with hashed password
     */
    public function create(array $data): int
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return parent::create($data);
    }
    
    /**
     * Update user password
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userId, ['password' => $hashedPassword]);
    }
    
    /**
     * Activate user
     */
    public function activateUser(int $userId): bool
    {
        return $this->update($userId, ['is_active' => true]);
    }
    
    /**
     * Deactivate user
     */
    public function deactivateUser(int $userId): bool
    {
        return $this->update($userId, ['is_active' => false]);
    }
    
    /**
     * Update last login time
     */
    public function updateLastLogin(int $userId): bool
    {
        return $this->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
    }
    
    /**
     * Get user statistics
     */
    public function getStatistics(): array
    {
        $db = $this->db;
        
        $totalUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'];
        $activeUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE is_active = true")['count'];
        $adminUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND is_active = true")['count'];
        $regularUsers = $db->fetchOne("SELECT COUNT(*) as count FROM users WHERE role = 'user' AND is_active = true")['count'];
        
        return [
            'total' => (int)$totalUsers,
            'active' => (int)$activeUsers,
            'admins' => (int)$adminUsers,
            'users' => (int)$regularUsers,
            'inactive' => (int)$totalUsers - (int)$activeUsers
        ];
    }
    
    /**
     * Get users with pagination
     */
    public function getPaginated(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Build WHERE clause
        $whereConditions = [];
        $params = [];
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(first_name ILIKE ? OR last_name ILIKE ? OR email ILIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (isset($filters['role']) && $filters['role'] !== '') {
            $whereConditions[] = "role = ?";
            $params[] = $filters['role'];
        }
        
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $whereConditions[] = "is_active = ?";
            $params[] = (bool)$filters['is_active'];
        }
        
        $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM users {$whereClause}";
        $totalCount = $this->db->fetchOne($countSql, $params)['count'];
        
        // Get users
        $sql = "SELECT id, email, first_name, last_name, role, is_active, last_login, created_at 
                FROM users {$whereClause} 
                ORDER BY created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";
        
        $users = $this->db->fetchAll($sql, $params);
        
        return [
            'data' => $users,
            'total' => (int)$totalCount,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalCount / $perPage)
        ];
    }
    
    /**
     * Get user's accessible pages
     */
    public function getUserPages(int $userId): array
    {
        $sql = "SELECT p.*, up.permission_type 
                FROM pages p 
                JOIN user_permissions up ON p.id = up.page_id 
                WHERE up.user_id = ? AND p.is_active = true 
                ORDER BY p.sort_order";
        
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    /**
     * Check if email is unique
     */
    public function isEmailUnique(string $email, int $excludeUserId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeUserId) {
            $sql .= " AND id != ?";
            $params[] = $excludeUserId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] == 0;
    }
    
    /**
     * Get user's full name
     */
    public function getFullName(array $user): string
    {
        return trim($user['first_name'] . ' ' . $user['last_name']);
    }
    
    /**
     * Format user data for display
     */
    public function formatForDisplay(array $user): array
    {
        $user['full_name'] = $this->getFullName($user);
        $user['role_label'] = $user['role'] === 'admin' ? 'Administrator' : 'İstifadəçi';
        $user['status_label'] = $user['is_active'] ? 'Aktiv' : 'Deaktiv';
        
        if ($user['last_login']) {
            $user['last_login_formatted'] = date('d.m.Y H:i', strtotime($user['last_login']));
        } else {
            $user['last_login_formatted'] = 'Heç vaxt';
        }
        
        return $user;
    }
}