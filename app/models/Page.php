<?php
/**
 * EduLinks Page Model
 * 
 * Handles page data operations
 */

namespace App\Models;

use App\Core\Model;

class Page extends Model
{
    protected $table = 'pages';
    protected $fillable = [
        'title',
        'description',
        'slug',
        'is_active',
        'sort_order',
        'icon',
        'color',
        'created_by'
    ];
    
    /**
     * Get all active pages ordered by sort_order
     */
    public function getActivePages(): array
    {
        return $this->where(['is_active' => true], 'sort_order ASC');
    }
    
    /**
     * Find page by slug
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->findWhere(['slug' => $slug, 'is_active' => true]);
    }
    
    /**
     * Get pages with pagination
     */
    public function getPaginated(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Build WHERE clause
        $whereConditions = [];
        $params = [];
        
        if (!empty($filters['search'])) {
            $whereConditions[] = "(title ILIKE ? OR description ILIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $whereConditions[] = "is_active = ?";
            $params[] = (bool)$filters['is_active'];
        }
        
        $whereClause = empty($whereConditions) ? '' : 'WHERE ' . implode(' AND ', $whereConditions);
        
        // Get total count
        $countSql = "SELECT COUNT(*) as count FROM pages {$whereClause}";
        $totalCount = $this->db->fetchOne($countSql, $params)['count'];
        
        // Get pages with creator information
        $sql = "SELECT p.*, 
                       u.first_name || ' ' || u.last_name as created_by_name,
                       (SELECT COUNT(*) FROM links l WHERE l.page_id = p.id AND l.is_active = true) as links_count
                FROM pages p
                LEFT JOIN users u ON p.created_by = u.id 
                {$whereClause} 
                ORDER BY p.sort_order ASC, p.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        $pages = $this->db->fetchAll($sql, $params);
        
        return [
            'data' => $pages,
            'total' => (int)$totalCount,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($totalCount / $perPage)
        ];
    }
    
    /**
     * Create page with auto-generated slug
     */
    public function createPage(array $data): int
    {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }
        
        // Set sort order if not provided
        if (!isset($data['sort_order'])) {
            $maxOrder = $this->db->fetchOne("SELECT MAX(sort_order) as max_order FROM pages")['max_order'];
            $data['sort_order'] = ($maxOrder ?? 0) + 1;
        }
        
        return $this->create($data);
    }
    
    /**
     * Update page with slug regeneration if title changed
     */
    public function updatePage(int $id, array $data): bool
    {
        $currentPage = $this->find($id);
        if (!$currentPage) {
            return false;
        }
        
        // Regenerate slug if title changed and slug not manually set
        if (isset($data['title']) && $data['title'] !== $currentPage['title'] && empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }
        
        return $this->update($id, $data);
    }
    
    /**
     * Generate unique slug from title
     */
    private function generateSlug(string $title): string
    {
        // Convert to lowercase and remove special characters
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Azerbaijani character replacements
        $replacements = [
            'ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u',
            'Ç' => 'c', 'Ğ' => 'g', 'İ' => 'i', 'Ö' => 'o', 'Ş' => 's', 'Ü' => 'u'
        ];
        
        foreach ($replacements as $from => $to) {
            $slug = str_replace($from, $to, $slug);
        }
        
        // Ensure uniqueness
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Check if slug exists
     */
    private function slugExists(string $slug, int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM pages WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetchOne($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Get page statistics
     */
    public function getStatistics(): array
    {
        $totalPages = $this->db->fetchOne("SELECT COUNT(*) as count FROM pages")['count'];
        $activePages = $this->db->fetchOne("SELECT COUNT(*) as count FROM pages WHERE is_active = true")['count'];
        
        // Get page with most links
        $popularPage = $this->db->fetchOne(
            "SELECT p.title, COUNT(l.id) as links_count 
             FROM pages p 
             LEFT JOIN links l ON p.id = l.page_id AND l.is_active = true 
             WHERE p.is_active = true 
             GROUP BY p.id, p.title 
             ORDER BY links_count DESC 
             LIMIT 1"
        );
        
        return [
            'total' => (int)$totalPages,
            'active' => (int)$activePages,
            'inactive' => (int)$totalPages - (int)$activePages,
            'most_popular' => $popularPage ? [
                'title' => $popularPage['title'],
                'links_count' => (int)$popularPage['links_count']
            ] : null
        ];
    }
    
    /**
     * Update sort orders
     */
    public function updateSortOrders(array $pageOrders): bool
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($pageOrders as $pageId => $sortOrder) {
                $this->update($pageId, ['sort_order' => $sortOrder]);
            }
            
            $this->db->commit();
            return true;
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
    
    /**
     * Check if page can be deleted
     */
    public function canDelete(int $pageId): array
    {
        $linksCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM links WHERE page_id = ?",
            [$pageId]
        )['count'];
        
        $permissionsCount = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM user_permissions WHERE page_id = ?",
            [$pageId]
        )['count'];
        
        $canDelete = $linksCount == 0 && $permissionsCount == 0;
        
        return [
            'can_delete' => $canDelete,
            'links_count' => (int)$linksCount,
            'permissions_count' => (int)$permissionsCount,
            'reasons' => $canDelete ? [] : [
                $linksCount > 0 ? "Səhifədə {$linksCount} link mövcuddur" : null,
                $permissionsCount > 0 ? "Səhifə üçün {$permissionsCount} istifadəçi icazəsi mövcuddur" : null
            ]
        ];
    }
    
    /**
     * Get pages accessible by user
     */
    public function getUserAccessiblePages(int $userId): array
    {
        $sql = "SELECT DISTINCT p.*, up.permission_type 
                FROM pages p 
                JOIN user_permissions up ON p.id = up.page_id 
                WHERE up.user_id = ? AND p.is_active = true 
                ORDER BY p.sort_order";
        
        return $this->db->fetchAll($sql, [$userId]);
    }
    
    /**
     * Get page with links count
     */
    public function getPageWithLinksCount(int $pageId): ?array
    {
        $sql = "SELECT p.*, 
                       u.first_name || ' ' || u.last_name as created_by_name,
                       (SELECT COUNT(*) FROM links l WHERE l.page_id = p.id AND l.is_active = true) as active_links_count,
                       (SELECT COUNT(*) FROM links l WHERE l.page_id = p.id) as total_links_count
                FROM pages p
                LEFT JOIN users u ON p.created_by = u.id 
                WHERE p.id = ?";
        
        return $this->db->fetchOne($sql, [$pageId]);
    }
    
    /**
     * Format page data for display
     */
    public function formatForDisplay(array $page): array
    {
        $page['status_label'] = $page['is_active'] ? 'Aktiv' : 'Deaktiv';
        $page['status_class'] = $page['is_active'] ? 'success' : 'secondary';
        
        if (!empty($page['icon'])) {
            $page['icon_html'] = '<i class="' . htmlspecialchars($page['icon']) . '"></i>';
        } else {
            $page['icon_html'] = '<i class="fas fa-folder"></i>';
        }
        
        if (!empty($page['color'])) {
            $page['color_style'] = 'background-color: ' . htmlspecialchars($page['color']);
        } else {
            $page['color_style'] = 'background-color: #007bff';
        }
        
        return $page;
    }
}