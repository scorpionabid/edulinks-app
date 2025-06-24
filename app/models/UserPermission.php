<?php
/**
 * EduLinks User Permission Model
 * 
 * Handles user permissions for pages
 */

namespace App\Models;

use App\Core\Model;

class UserPermission extends Model
{
    protected $table = 'user_permissions';
    protected $fillable = [
        'user_id',
        'page_id',
        'permission_type'
    ];
    
    /**
     * Get user permissions with page details
     */
    public function getUserPermissions(int $userId): array
    {
        $sql = "SELECT up.*, p.title as page_title, p.slug as page_slug, p.icon, p.color
                FROM user_permissions up
                JOIN pages p ON up.page_id = p.id
                WHERE up.user_id = ? AND p.is_active = true
                ORDER BY p.sort_order";
        
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    /**
     * Get page permissions with user details
     */
    public function getPagePermissions(int $pageId): array
    {
        $sql = "SELECT up.*, u.first_name, u.last_name, u.email, u.role
                FROM user_permissions up
                JOIN users u ON up.user_id = u.id
                WHERE up.page_id = ? AND u.is_active = true
                ORDER BY u.first_name, u.last_name";
        
        return $this->db->fetchAll($sql, [$pageId]);
    }
    
    /**
     * Set user permissions for a page
     */
    public function setUserPagePermission(int $userId, int $pageId, string $permissionType): bool
    {
        // Check if permission already exists
        $existing = $this->findWhere([
            'user_id' => $userId,
            'page_id' => $pageId
        ]);
        
        if ($existing) {
            // Update existing permission
            return $this->update($existing['id'], ['permission_type' => $permissionType]);
        } else {
            // Create new permission
            $this->create([
                'user_id' => $userId,
                'page_id' => $pageId,
                'permission_type' => $permissionType
            ]);
            return true;
        }
    }
    
    /**
     * Remove user permission for a page
     */
    public function removeUserPagePermission(int $userId, int $pageId): bool
    {
        $permission = $this->findWhere([
            'user_id' => $userId,
            'page_id' => $pageId
        ]);
        
        if ($permission) {
            return $this->delete($permission['id']);
        }
        
        return true; // Already doesn't exist
    }
    
    /**
     * Set multiple permissions for a user
     */
    public function setUserPermissions(int $userId, array $permissions): bool
    {
        $this->db->beginTransaction();
        
        try {
            // Remove all existing permissions for user
            $this->db->execute("DELETE FROM user_permissions WHERE user_id = ?", [$userId]);
            
            // Add new permissions
            foreach ($permissions as $pageId => $permissionType) {
                if (!empty($permissionType)) {
                    $this->create([
                        'user_id' => $userId,
                        'page_id' => $pageId,
                        'permission_type' => $permissionType
                    ]);
                }
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Check if user has permission for page
     */
    public function hasPermission(int $userId, int $pageId, string $permissionType = 'read'): bool
    {
        $permission = $this->findWhere([
            'user_id' => $userId,
            'page_id' => $pageId
        ]);
        
        if (!$permission) {
            return false;
        }
        
        // If user has edit permission, they also have read permission
        if ($permissionType === 'read' && $permission['permission_type'] === 'edit') {
            return true;
        }
        
        return $permission['permission_type'] === $permissionType;
    }
    
    /**
     * Get users without permission for a page
     */
    public function getUsersWithoutPagePermission(int $pageId): array
    {
        $sql = "SELECT u.id, u.first_name, u.last_name, u.email, u.role
                FROM users u
                WHERE u.is_active = true 
                  AND u.role = 'user'
                  AND u.id NOT IN (
                      SELECT user_id FROM user_permissions WHERE page_id = ?
                  )
                ORDER BY u.first_name, u.last_name";
        
        return $this->db->fetchAll($sql, [$pageId]);
    }
    
    /**
     * Get pages without permission for a user
     */
    public function getPagesWithoutUserPermission(int $userId): array
    {
        $sql = "SELECT p.id, p.title, p.slug, p.icon, p.color
                FROM pages p
                WHERE p.is_active = true 
                  AND p.id NOT IN (
                      SELECT page_id FROM user_permissions WHERE user_id = ?
                  )
                ORDER BY p.sort_order";
        
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    /**
     * Copy permissions from one user to another
     */
    public function copyUserPermissions(int $fromUserId, int $toUserId): bool
    {
        $this->db->beginTransaction();
        
        try {
            // Get source user permissions
            $sourcePermissions = $this->where(['user_id' => $fromUserId]);
            
            // Remove existing permissions for target user
            $this->db->execute("DELETE FROM user_permissions WHERE user_id = ?", [$toUserId]);
            
            // Copy permissions
            foreach ($sourcePermissions as $permission) {
                $this->create([
                    'user_id' => $toUserId,
                    'page_id' => $permission['page_id'],
                    'permission_type' => $permission['permission_type']
                ]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Get permission statistics
     */
    public function getStatistics(): array
    {
        $totalPermissions = $this->count();
        
        $readPermissions = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM user_permissions WHERE permission_type = 'read'"
        )['count'];
        
        $editPermissions = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM user_permissions WHERE permission_type = 'edit'"
        )['count'];
        
        $usersWithPermissions = $this->db->fetchOne(
            "SELECT COUNT(DISTINCT user_id) as count FROM user_permissions"
        )['count'];
        
        $pagesWithPermissions = $this->db->fetchOne(
            "SELECT COUNT(DISTINCT page_id) as count FROM user_permissions"
        )['count'];
        
        return [
            'total_permissions' => (int)$totalPermissions,
            'read_permissions' => (int)$readPermissions,
            'edit_permissions' => (int)$editPermissions,
            'users_with_permissions' => (int)$usersWithPermissions,
            'pages_with_permissions' => (int)$pagesWithPermissions
        ];
    }
    
    /**
     * Get permission matrix (users vs pages)
     */
    public function getPermissionMatrix(): array
    {
        $sql = "SELECT u.id as user_id, u.first_name, u.last_name, u.email,
                       p.id as page_id, p.title as page_title,
                       up.permission_type
                FROM users u
                CROSS JOIN pages p
                LEFT JOIN user_permissions up ON u.id = up.user_id AND p.id = up.page_id
                WHERE u.is_active = true AND u.role = 'user' AND p.is_active = true
                ORDER BY u.first_name, u.last_name, p.sort_order";
        
        $results = $this->db->fetchAll($sql);
        
        // Group by user
        $matrix = [];
        foreach ($results as $row) {
            $userId = $row['user_id'];
            if (!isset($matrix[$userId])) {
                $matrix[$userId] = [
                    'user' => [
                        'id' => $userId,
                        'name' => $row['first_name'] . ' ' . $row['last_name'],
                        'email' => $row['email']
                    ],
                    'permissions' => []
                ];
            }
            
            $matrix[$userId]['permissions'][$row['page_id']] = [
                'page_title' => $row['page_title'],
                'permission_type' => $row['permission_type']
            ];
        }
        
        return array_values($matrix);
    }
}